<?php

namespace App\Services;

use App\Models\TokenData;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Google_Client;

class NotificationService
{
    private const IOS_BUNDLE_ID = 'com.kumba.care';
    private const FIREBASE_RTDB_URL = 'https://kumba-care-default-rtdb.firebaseio.com';

    /**
     * Resolve FCM tokens for a user/doctor from MySQL (`savetoken`) and fallback to Firebase RTDB (`TokenList`).
     */
    public function resolveTokensForUser($userId, string $userTypeField): array
    {
        $cleanId = preg_replace('/[^0-9]/', '', (string)$userId);
        if (str_starts_with($cleanId, '117') && strlen($cleanId) > 3 && $userTypeField === 'user_id') {
            $cleanId = substr($cleanId, 3);
        } elseif (str_starts_with($cleanId, '100') && strlen($cleanId) > 3 && $userTypeField === 'doctor_id') {
            $cleanId = substr($cleanId, 3);
        }

        $tokens = TokenData::where($userTypeField, $cleanId)
            ->orWhere($userTypeField, $userId)
            ->pluck('token')
            ->filter(fn($t) => !empty($t) && !in_array($t, ['no_fcm_token', 'no_token_provided', 'null', 'undefined']))
            ->unique()
            ->values()
            ->toArray();

        if (!empty($tokens)) {
            return $tokens;
        }

        // Fallback: Check Firebase Realtime Database TokenList (e.g. 117{userId}/TokenList or 100{doctorId}/TokenList)
        // Must pass ?access_token= because Firebase App Check is enforced on RTDB for unauthenticated requests!
        try {
            $accessToken = $this->generateAccessToken();
            $prefix = $userTypeField === 'user_id' ? '117' : '100';
            $rtdbNode = $prefix . $cleanId;
            $url = self::FIREBASE_RTDB_URL . "/{$rtdbNode}/TokenList.json";
            if ($accessToken) {
                $url .= "?access_token=" . urlencode($accessToken);
            }

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 4);
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($code == 200 && !empty($resp) && $resp !== 'null') {
                $data = json_decode($resp, true);
                if (is_array($data)) {
                    foreach ($data as $k => $tok) {
                        if (is_string($tok) && strlen($tok) > 20 && !in_array($tok, ['no_fcm_token', 'no_token_provided', 'null', 'undefined'])) {
                            $tokens[] = $tok;
                            // Backfill into MySQL savetoken table so future lookups are instant
                            try {
                                TokenData::updateOrCreate(
                                    ['token' => $tok],
                                    [
                                        $userTypeField => $cleanId,
                                        'type' => '1',
                                    ]
                                );
                            } catch (\Exception $e) {}
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning("RTDB TokenList fallback lookup failed: " . $e->getMessage());
        }

        return array_values(array_unique($tokens));
    }

    /**
     * Push FCM token directly to Firebase Realtime Database (`117{userId}/TokenList` or `100{doctorId}/TokenList`)
     * using Server OAuth credentials (bypasses client-side Firebase App Check blocks).
     */
    public function syncTokenToFirebaseRtdb($id, string $userTypeField, string $token): void
    {
        if (empty($token) || in_array($token, ['no_fcm_token', 'no_token_provided', 'null', 'undefined'])) {
            return;
        }
        try {
            $cleanId = preg_replace('/[^0-9]/', '', (string)$id);
            if (empty($cleanId)) return;

            $accessToken = $this->generateAccessToken();
            if (!$accessToken) return;

            $prefix = $userTypeField === 'user_id' ? '117' : '100';
            $rtdbNode = $prefix . $cleanId;
            $url = self::FIREBASE_RTDB_URL . "/{$rtdbNode}/TokenList.json?access_token=" . urlencode($accessToken);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['device' => $token]));
            curl_setopt($ch, CURLOPT_TIMEOUT, 4);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Exception $e) {
            Log::warning("Failed to sync token to Firebase RTDB from backend: " . $e->getMessage());
        }
    }

    /**
     * Resolve the path to the Firebase Service Account JSON file across public/upload/jsonfile and storage/app/jsonfile.
     */
    public function getFirebaseJsonPath(): ?string
    {
        $setting = Setting::find(1);
        if (!$setting || !$setting->not_json_filename) {
            return null;
        }

        $publicPath = public_path('upload/jsonfile/' . $setting->not_json_filename);
        if (File::exists($publicPath)) {
            return $publicPath;
        }

        $storagePath = storage_path('app/jsonfile/' . $setting->not_json_filename);
        if (File::exists($storagePath)) {
            return $storagePath;
        }

        return null;
    }

    /**
     * Send a notification to a user (Patient or Doctor).
     *
     * @param string $message The message body.
     * @param int $userId The ID of the user receiving the notification.
     * @param string $userTypeField The column name to match user ID (e.g., 'user_id' for patient, 'doctor_id' for doctor).
     * @param mixed $relatedId Optional ID for related data (e.g., appointment ID).
     * @param array $extraData Additional key-value pairs for the FCM data payload.
     * @return bool
     */
    public function sendNotification($message, $userId, $userTypeField, $relatedId = null, $extraData = [])
    {
        try {
            $accessToken = $this->generateAccessToken();
            if (!$accessToken) {
                return false;
            }

            $tokens = $this->resolveTokensForUser($userId, $userTypeField);

            if (empty($tokens)) {
                return false;
            }

            $jsonPath = $this->getFirebaseJsonPath();
            if (!$jsonPath) {
                return false;
            }

            $contents = File::get($jsonPath);
            $accountJson = json_decode($contents);
            $apiEndpoint = "https://fcm.googleapis.com/v1/projects/" . $accountJson->project_id . "/messages:send";

            $headers = [
                "Content-Type: application/json",
                "Authorization: Bearer " . $accessToken
            ];

            $successCount = 0;

            $dataPayload = array_merge([
                'message' => (string)$message,
                'body' => (string)$message,
                'title' => (string)config('app.name'),
                'related_id' => (string)$relatedId,
                'order_id' => (string)$relatedId,
                'type' => (string)$userTypeField,
                'receiver_type' => (string)$userTypeField,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ], array_map('strval', $extraData));

            foreach ($tokens as $token) {
                if (empty($token) || in_array($token, ['no_fcm_token', 'no_token_provided', 'null', 'undefined'])) {
                    TokenData::where('token', $token)->delete();
                    continue;
                }

                $payload = [
                    "message" => [
                        "token" => $token,
                        "notification" => [
                            "title" => config('app.name'),
                            "body" => $message
                        ],
                        "data" => $dataPayload,
                        "android" => [
                            "priority" => "HIGH",
                            "notification" => [
                                "channel_id" => "chiremba_notifications",
                                "sound" => "default",
                                "default_sound" => true,
                                "default_vibrate_timings" => true,
                            ]
                        ],
                        "apns" => [
                            "headers" => [
                                "apns-priority" => "10",
                                "apns-push-type" => "alert",
                                "apns-topic" => self::IOS_BUNDLE_ID,
                            ],
                            "payload" => [
                                "aps" => [
                                    "alert" => [
                                        "title" => config('app.name'),
                                        "body" => $message
                                    ],
                                    "sound" => "default",
                                    "badge" => 1,
                                    "content-available" => 1,
                                    "mutable-content" => 1,
                                ]
                            ]
                        ]
                    ]
                ];

                $ch = curl_init($apiEndpoint);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode == 200) {
                    $successCount++;
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasTable('mobile_diagnostic_logs')) {
                            \App\Models\MobileDiagnosticLog::create([
                                'category' => 'fcm',
                                'level' => 'info',
                                'user_role' => $userTypeField === 'user_id' ? 'patient' : 'doctor',
                                'user_id' => $userId,
                                'fcm_token' => $token,
                                'title' => 'FCM Push Delivered (HTTP 200)',
                                'message' => $message,
                                'metadata' => $response,
                            ]);
                        }
                    } catch (\Exception $e) {}
                } else {
                    try {
                        if (\Illuminate\Support\Facades\Schema::hasTable('mobile_diagnostic_logs')) {
                            \App\Models\MobileDiagnosticLog::create([
                                'category' => 'fcm',
                                'level' => 'error',
                                'user_role' => $userTypeField === 'user_id' ? 'patient' : 'doctor',
                                'user_id' => $userId,
                                'fcm_token' => $token,
                                'title' => "FCM Push Failed (HTTP {$httpCode})",
                                'message' => $message,
                                'metadata' => $response,
                            ]);
                        }
                    } catch (\Exception $e) {}
                    if (str_contains($response, 'UNREGISTERED') || str_contains($response, 'NotRegistered')) {
                        TokenData::where('token', $token)->delete();
                        Log::info("Deleted stale FCM token for user ID {$userId}");
                    } else {
                        Log::error("FCM Send Error: " . $response);
                    }
                }
            }

            try {
                \App\Models\Notification::create([
                    'user_id' => $userTypeField === 'user_id' ? $userId : null,
                    'doctor_id' => $userTypeField === 'doctor_id' ? $userId : null,
                    'title' => config('app.name'),
                    'message' => $message,
                    'type' => $extraData['type'] ?? 'general',
                    'related_id' => (string)$relatedId,
                    'is_read' => false,
                ]);
            } catch (\Exception $e) {
                Log::warning("Could not persist notification to DB: " . $e->getMessage());
            }

            return $successCount > 0;

        } catch (\Exception $e) {
            Log::error("NotificationService Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send high priority VoIP call notification with ringing parameters
     */
    public function sendCallPushNotification($receiverId, $receiverTypeField, $channelName, $callerName = 'Doctor', $callType = 'video')
    {
        try {
            $accessToken = $this->generateAccessToken();
            if (!$accessToken) {
                Log::error("Call FCM: Failed to generate access token");
                return false;
            }

            $tokens = $this->resolveTokensForUser($receiverId, $receiverTypeField);
            if (empty($tokens)) {
                Log::warning("Call FCM: No registered tokens found for receiver {$receiverTypeField}={$receiverId}");
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('mobile_diagnostic_logs')) {
                        \App\Models\MobileDiagnosticLog::create([
                            'category' => 'fcm',
                            'level' => 'warning',
                            'user_role' => $receiverTypeField === 'user_id' ? 'patient' : 'doctor',
                            'user_id' => (int)preg_replace('/[^0-9]/', '', (string)$receiverId),
                            'title' => 'Call Push Skipped: No Registered Token',
                            'message' => "No token in savetoken or RTDB TokenList for {$receiverTypeField}={$receiverId} (channel: {$channelName})",
                        ]);
                    }
                } catch (\Exception $e) {}
                return false;
            }

            $jsonPath = $this->getFirebaseJsonPath();
            if (!$jsonPath) {
                return false;
            }

            $contents = File::get($jsonPath);
            $accountJson = json_decode($contents);
            $apiEndpoint = "https://fcm.googleapis.com/v1/projects/" . $accountJson->project_id . "/messages:send";

            $headers = [
                "Content-Type: application/json",
                "Authorization: Bearer " . $accessToken
            ];

            $uuid = (string)\Illuminate\Support\Str::uuid();
            $callTitle = "Incoming " . ucfirst($callType) . " Call";
            $callBody = "Incoming consultation call from {$callerName}";

            $successCount = 0;

            foreach ($tokens as $token) {
                if (empty($token) || in_array($token, ['no_fcm_token', 'no_token_provided', 'null', 'undefined'])) {
                    TokenData::where('token', $token)->delete();
                    continue;
                }

                $payload = [
                    "message" => [
                        "token" => $token,
                        "data" => [
                            'type' => 'call',
                            'action' => 'incoming_call',
                            'notificationType' => 'call',
                            'channel_name' => (string)$channelName,
                            'caller_name' => (string)$callerName,
                            'call_type' => (string)$callType,
                            'receiver_id' => (string)$receiverId,
                            'receiver_type' => (string)$receiverTypeField,
                            'uuid' => $uuid,
                            'title' => $callTitle,
                            'body' => $callBody,
                            'message' => $callBody,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ],
                        "android" => [
                            "priority" => "HIGH",
                            "ttl" => "45s",
                        ],
                        "apns" => [
                            "headers" => [
                                "apns-priority" => "10",
                                "apns-push-type" => "alert",
                                "apns-topic" => self::IOS_BUNDLE_ID,
                                "apns-expiration" => (string)(time() + 45),
                            ],
                            "payload" => [
                                "aps" => [
                                    "alert" => [
                                        "title" => $callTitle,
                                        "body" => $callBody,
                                    ],
                                    "sound" => "default",
                                    "badge" => 1,
                                    "content-available" => 1,
                                    "mutable-content" => 1,
                                    "interruption-level" => "time-sensitive",
                                    "category" => "INCOMING_CALL"
                                ]
                            ]
                        ]
                    ]
                ];

                $ch = curl_init($apiEndpoint);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode == 200) {
                    $successCount++;
                } else {
                    if (str_contains($response, 'UNREGISTERED') || 
                        str_contains($response, 'NotRegistered') || 
                        str_contains($response, 'INVALID_ARGUMENT') ||
                        str_contains($response, 'InvalidArgument')) {
                        TokenData::where('token', $token)->delete();
                        Log::info("Deleted stale or invalid FCM token [{$token}] for {$receiverTypeField}={$receiverId}");
                    }
                    Log::error("Call FCM Send Error: " . $response);
                }
            }

            return $successCount > 0;
        } catch (\Exception $e) {
            Log::error("NotificationService Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send high priority cancellation push notification so CallKit immediately stops ringing
     */
    public function sendCancelCallPushNotification($receiverId, $receiverTypeField, $channelName)
    {
        try {
            $accessToken = $this->generateAccessToken();
            if (!$accessToken) {
                Log::error("Cancel Call FCM: Failed to generate access token");
                return false;
            }

            $tokens = $this->resolveTokensForUser($receiverId, $receiverTypeField);
            if (empty($tokens)) {
                Log::warning("Cancel Call FCM: No registered tokens found for receiver {$receiverTypeField}={$receiverId}");
                return false;
            }

            $jsonPath = $this->getFirebaseJsonPath();
            if (!$jsonPath) {
                return false;
            }

            $contents = File::get($jsonPath);
            $accountJson = json_decode($contents);
            $apiEndpoint = "https://fcm.googleapis.com/v1/projects/" . $accountJson->project_id . "/messages:send";

            $headers = [
                "Content-Type: application/json",
                "Authorization: Bearer " . $accessToken
            ];

            $successCount = 0;

            foreach ($tokens as $token) {
                if (empty($token) || in_array($token, ['no_fcm_token', 'no_token_provided', 'null', 'undefined'])) {
                    TokenData::where('token', $token)->delete();
                    continue;
                }

                $payload = [
                    "message" => [
                        "token" => $token,
                        "data" => [
                            'type' => 'cancel_call',
                            'action' => 'cancel_call',
                            'channel_name' => (string)$channelName,
                        ],
                        "android" => [
                            "priority" => "HIGH",
                            "ttl" => "10s",
                        ],
                        "apns" => [
                            "headers" => [
                                "apns-priority" => "5",
                                "apns-push-type" => "background",
                                "apns-topic" => self::IOS_BUNDLE_ID,
                                "apns-expiration" => (string)(time() + 10),
                            ],
                            "payload" => [
                                "aps" => [
                                    "content-available" => 1,
                                ]
                            ]
                        ]
                    ]
                ];

                $ch = curl_init($apiEndpoint);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode == 200) {
                    $successCount++;
                } else {
                    if (str_contains($response, 'UNREGISTERED') || 
                        str_contains($response, 'NotRegistered') || 
                        str_contains($response, 'INVALID_ARGUMENT') ||
                        str_contains($response, 'InvalidArgument')) {
                        TokenData::where('token', $token)->delete();
                        Log::info("Deleted stale or invalid FCM token [{$token}] during cancel");
                    }
                    Log::error("Cancel Call FCM Send Error: " . $response);
                }
            }

            return $successCount > 0;
        } catch (\Exception $e) {
            Log::error("sendCancelCallPushNotification Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send force logout push notification to previous active devices
     */
    public function sendForceLogoutNotification($tokens)
    {
        if (empty($tokens)) {
            return false;
        }

        try {
            $accessToken = $this->generateAccessToken();
            if (!$accessToken) {
                return false;
            }

            $jsonPath = $this->getFirebaseJsonPath();
            if (!$jsonPath) {
                return false;
            }

            $contents = File::get($jsonPath);
            $accountJson = json_decode($contents);
            $apiEndpoint = "https://fcm.googleapis.com/v1/projects/" . $accountJson->project_id . "/messages:send";

            $headers = [
                "Content-Type: application/json",
                "Authorization: Bearer " . $accessToken
            ];

            foreach ((array)$tokens as $token) {
                if (empty($token) || in_array($token, ['no_fcm_token', 'no_token_provided', 'null', 'undefined'])) {
                    continue;
                }

                $payload = [
                    "message" => [
                        "token" => $token,
                        "data" => [
                            'type' => 'force_logout',
                            'action' => 'force_logout',
                            'message' => 'Your account was logged in on another device.',
                        ],
                        "android" => [
                            "priority" => "HIGH",
                            "ttl" => "10s",
                        ],
                        "apns" => [
                            "headers" => [
                                "apns-priority" => "5",
                                "apns-push-type" => "background",
                                "apns-topic" => self::IOS_BUNDLE_ID,
                                "apns-expiration" => (string)(time() + 10),
                            ],
                            "payload" => [
                                "aps" => [
                                    "content-available" => 1,
                                ]
                            ]
                        ]
                    ]
                ];

                $ch = curl_init($apiEndpoint);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_exec($ch);
                curl_close($ch);
            }
            return true;
        } catch (\Exception $e) {
            Log::error("sendForceLogoutNotification Error: " . $e->getMessage());
            return false;
        }
    }

    public function generateAccessToken()
    {
        try {
            $path = $this->getFirebaseJsonPath();
            if (!$path) {
                Log::error("Firebase JSON file not found in public/upload/jsonfile or storage/app/jsonfile");
                return null;
            }

            $client = new Google_Client();
            $client->setAuthConfig($path);
            $client->setScopes([
                'https://www.googleapis.com/auth/firebase.messaging',
                'https://www.googleapis.com/auth/firebase.database',
                'https://www.googleapis.com/auth/userinfo.email',
            ]);
            
            return $client->fetchAccessTokenWithAssertion()['access_token'];
        } catch (\Exception $e) {
            Log::error("Google Access Token Error: " . $e->getMessage());
            return null;
        }
    }
}
