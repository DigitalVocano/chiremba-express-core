<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Banner;
use App\Models\About;
use App\Models\PaymentGatewayDetail;

class CommonController extends Controller
{
    public function banner_list()
    {
        $data = Banner::all();
        foreach ($data as $k) {
            $k->image = asset('upload/banner') . '/' . $k->image;
        }
        return response()->json(['success' => "1", 'data' => $data], 200);
    }

    public function about()
    {
        $data = About::find(1);
        if (!$data) {
            $data = [
                'aboutus' => 'About us content is not available at the moment.',
                'termcondition' => 'Terms and conditions are not available at the moment.'
            ];
        }
        return response()->json(['success' => "1", 'data' => $data, 'status' => 1], 200);
    }

    public function get_bankdetails()
    {
        $data = PaymentGatewayDetail::where('gateway_name', 'bank_transfer')->get();
        return response()->json(['success' => "1", 'data' => $data], 200);
    }

    public function privacy_policy()
    {
        $data = About::find(1);
        $content = 'Privacy policy not available.';
        if ($data && $data->privacy) {
            $content = $data->privacy;
        }
        return response()->json(['success' => "1", 'data' => ['privacy_policy_content' => $content]], 200);
    }

    public function get_medical_aid_societies(Request $request)
    {
        $doctorId = $request->get('doctor_id');
        $pharmacyId = $request->get('pharmacy_id');

        if ($doctorId) {
            $doctor = \App\Models\Doctors::find($doctorId);
            $societies = $doctor ? $doctor->medicalAids()->where('is_active', 1)->get() : collect();
            if ($societies->isEmpty()) {
                $societies = \App\Models\MedicalAidSociety::where('is_active', 1)->get();
            }
        } elseif ($pharmacyId) {
            $pharmacy = \App\Models\Doctors::find($pharmacyId);
            $societies = $pharmacy ? $pharmacy->medicalAids()->where('is_active', 1)->get() : collect();
            if ($societies->isEmpty()) {
                $societies = \App\Models\MedicalAidSociety::where('is_active', 1)->get();
            }
        } else {
            $societies = \App\Models\MedicalAidSociety::where('is_active', 1)->orderBy('name', 'ASC')->get();
        }

        return response()->json(['success' => "1", 'data' => $societies], 200);
    }

    public function get_notifications(Request $request)
    {
        $userId = $request->get('user_id');
        $doctorId = $request->get('doctor_id');

        $query = \App\Models\Notification::query();

        if ($doctorId) {
            $query->where(function($q) use ($doctorId) {
                $q->where('doctor_id', $doctorId)
                  ->orWhere(function($sub) {
                      $sub->whereNull('doctor_id')->whereNull('user_id');
                  });
            });
        } elseif ($userId) {
            $query->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere(function($sub) {
                      $sub->whereNull('doctor_id')->whereNull('user_id');
                  });
            });
        }

        $notifications = $query->orderBy('id', 'desc')->take(60)->get()->map(function($item) {
            return [
                'id' => (string)$item->id,
                'title' => $item->title ?: config('app.name', 'Chiremba Express'),
                'message' => $item->message,
                'type' => $item->type ?: 'general',
                'related_id' => $item->related_id ? (string)$item->related_id : '',
                'is_read' => (bool)$item->is_read,
                'created_at' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'),
                'time_ago' => $item->created_at ? $item->created_at->diffForHumans() : 'Just now',
            ];
        });

        $unreadCount = $notifications->where('is_read', false)->count();

        return response()->json([
            'success' => "1",
            'unread_count' => $unreadCount,
            'data' => $notifications->values(),
        ], 200);
    }

    public function mark_notification_read(Request $request)
    {
        $id = $request->get('id');
        $userId = $request->get('user_id');
        $doctorId = $request->get('doctor_id');

        if ($id && $id !== 'all') {
            \App\Models\Notification::where('id', $id)->update(['is_read' => true]);
        } elseif ($userId) {
            \App\Models\Notification::where('user_id', $userId)->update(['is_read' => true]);
        } elseif ($doctorId) {
            \App\Models\Notification::where('doctor_id', $doctorId)->update(['is_read' => true]);
        }

        return response()->json(['success' => "1", 'message' => 'Notifications updated successfully'], 200);
    }

    public function mediaupload(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $destination = public_path('upload/chat');
            if (!file_exists($destination)) {
                mkdir($destination, 0777, true);
            }
            $file->move($destination, $filename);
            return response()->json([
                'status' => true,
                'success' => 1,
                'data' => $filename,
                'url' => asset('public/upload/chat/' . $filename)
            ], 200);
        }
        return response()->json(['status' => false, 'success' => 0, 'msg' => 'No file provided'], 400);
    }

    public function logMobileTelemetry(Request $request)
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('mobile_diagnostic_logs')) {
                return response()->json(['success' => "0", 'message' => 'Telemetry table not migrated yet'], 200);
            }

            $category = $request->get('category', 'general');
            $level = $request->get('level', 'info');
            $title = $request->get('title', 'Mobile Telemetry Event');
            $message = $request->get('message', '');

            $log = \App\Models\MobileDiagnosticLog::create([
                'category' => $category,
                'level' => $level,
                'platform' => $request->get('platform', 'unknown'),
                'os_version' => $request->get('os_version'),
                'device_model' => $request->get('device_model'),
                'app_version' => $request->get('app_version'),
                'user_role' => $request->get('user_role'),
                'user_id' => $request->get('user_id') ? (int)$request->get('user_id') : null,
                'user_name' => $request->get('user_name'),
                'fcm_token' => $request->get('fcm_token'),
                'apns_token' => $request->get('apns_token'),
                'permission_status' => $request->get('permission_status'),
                'title' => substr((string)$title, 0, 250),
                'message' => $message,
                'stack_trace' => $request->get('stack_trace'),
                'metadata' => is_array($request->get('metadata')) ? json_encode($request->get('metadata')) : $request->get('metadata'),
                'ip_address' => $request->ip(),
            ]);

            if (in_array($level, ['error', 'critical', 'fatal']) || $category === 'crash') {
                \Illuminate\Support\Facades\Log::error("[Mobile {$category}] ({$log->platform} {$log->device_model}): {$title} - {$message}");
            }

            return response()->json([
                'success' => "1",
                'log_id' => $log->id,
                'message' => 'Telemetry logged'
            ], 200);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("logMobileTelemetry error: " . $e->getMessage());
            return response()->json(['success' => "0", 'message' => $e->getMessage()], 200);
        }
    }

    public function sendChatPush(Request $request)
    {
        $token = $request->get('token');
        $receiverUid = $request->get('receiver_uid', '');
        $senderUid = $request->get('sender_uid', '');
        $senderName = $request->get('sender_name', 'New Message');
        $messageBody = $request->get('message', 'Sent you a message');
        $channelId = $request->get('channel_id', '');

        $tokens = [];
        if (!empty($token) && !in_array($token, ['no_fcm_token', 'no_token_provided', 'null', 'undefined'])) {
            $tokens[] = $token;
        }

        $notifService = new \App\Services\NotificationService();

        if (!empty($receiverUid)) {
            if (str_starts_with($receiverUid, '100')) {
                $docId = (int)substr($receiverUid, 3);
                if ($docId > 0) {
                    $dbTokens = $notifService->resolveTokensForUser($docId, 'doctor_id');
                    $tokens = array_unique(array_merge($tokens, $dbTokens));
                }
            } elseif (str_starts_with($receiverUid, '117')) {
                $patId = (int)substr($receiverUid, 3);
                if ($patId > 0) {
                    $dbTokens = $notifService->resolveTokensForUser($patId, 'user_id');
                    $tokens = array_unique(array_merge($tokens, $dbTokens));
                }
            }
        }

        if (empty($tokens)) {
            return response()->json(['success' => "0", 'message' => 'No target tokens found'], 200);
        }

        try {
            $accessToken = $notifService->generateAccessToken();
            $jsonPath = $notifService->getFirebaseJsonPath();
            if (!$accessToken || !$jsonPath) {
                return response()->json(['success' => "0", 'message' => 'FCM service account not configured'], 200);
            }

            $accountJson = json_decode(\Illuminate\Support\Facades\File::get($jsonPath));
            $apiEndpoint = "https://fcm.googleapis.com/v1/projects/" . $accountJson->project_id . "/messages:send";
            $headers = [
                "Content-Type: application/json",
                "Authorization: Bearer " . $accessToken
            ];

            $sent = 0;
            foreach ($tokens as $tk) {
                if (empty($tk) || in_array($tk, ['no_fcm_token', 'no_token_provided', 'null', 'undefined'])) continue;

                $payload = [
                    "message" => [
                        "token" => $tk,
                        "notification" => [
                            "title" => (string)$senderName,
                            "body" => (string)$messageBody,
                        ],
                        "data" => [
                            "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                            "body" => (string)$messageBody,
                            "title" => (string)$senderName,
                            "uid" => (string)$senderUid,
                            "channelId" => (string)$channelId,
                            "myUserName" => (string)$senderName,
                            "myid" => (string)$senderUid,
                            "notificationType" => "0",
                        ],
                        "android" => [
                            "priority" => "HIGH",
                            "notification" => [
                                "channel_id" => "chiremba_notifications",
                                "sound" => "default",
                                "default_sound" => true,
                            ]
                        ],
                        "apns" => [
                            "headers" => [
                                "apns-priority" => "10",
                                "apns-push-type" => "alert",
                                "apns-topic" => "com.kumba.care",
                            ],
                            "payload" => [
                                "aps" => [
                                    "alert" => [
                                        "title" => (string)$senderName,
                                        "body" => (string)$messageBody,
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
                $resp = curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($code == 200) {
                    $sent++;
                }
            }

            return response()->json(['success' => "1", 'sent' => $sent], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => "0", 'message' => $e->getMessage()], 200);
        }
    }
}
