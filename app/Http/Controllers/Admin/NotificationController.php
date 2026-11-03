<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use Illuminate\Support\Facades\Session;
use validate;
use Sentinel;
use DB;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\TokenData;
use Yajra\DataTables\DataTables;
use Google_Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class NotificationController extends Controller
{
    private function resolveFirebaseJsonPath(?Setting $setting): ?string
    {
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

    public function showsendnotification()
    {
        $totalTokens = TokenData::count();
        $patientTokens = TokenData::where(function($q) {
            $q->whereIn('type', ['1', 'patient'])->orWhereNotNull('user_id');
        })->count();
        $doctorTokens = TokenData::where(function($q) {
            $q->whereIn('type', ['2', 'doctor'])->orWhereNotNull('doctor_id');
        })->count();
        $pharmacyTokens = TokenData::whereIn('type', ['3', 'pharmacy'])->count();

        $setting = Setting::find(1);
        $hasFcmKey = $this->resolveFirebaseJsonPath($setting) !== null;

        return view("admin.notification", compact('totalTokens', 'patientTokens', 'doctorTokens', 'pharmacyTokens', 'hasFcmKey'));
    }

    public function notificationkey()
    {
        $user = Setting::find(1);
        return view("admin.notificationkey")->with("user", $user);
    }

    public function updatenotificationkey(Request $request)
    {
        $user = Setting::find(1);
        if ($request->hasFile('jsonfile')) {
            $file = $request->file('jsonfile');
            $filename = $file->getClientOriginalName();
            $publicDir = public_path('upload/jsonfile');

            if (!File::isDirectory($publicDir)) {
                File::makeDirectory($publicDir, 0755, true);
            }

            // Delete old file if it exists and has a different name
            if ($user->not_json_filename && $user->not_json_filename !== $filename) {
                $oldPublic = $publicDir . '/' . $user->not_json_filename;
                if (File::exists($oldPublic)) {
                    File::delete($oldPublic);
                }
                if (Storage::disk('local')->exists('jsonfile/' . $user->not_json_filename)) {
                    Storage::disk('local')->delete('jsonfile/' . $user->not_json_filename);
                }
            }

            // Save in public/upload/jsonfile (used by NotificationService) and mirror to storage/app/jsonfile
            $file->move($publicDir, $filename);
            try {
                Storage::disk('local')->put('jsonfile/' . $filename, File::get($publicDir . '/' . $filename));
            } catch (\Exception $e) {}

            $user->not_json_filename = $filename;
            $user->save();
        }

        Session::flash('message', __('Notification Key Update Successfully'));
        Session::flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    public function notificationtable(Request $request)
    {
        $notify = Notification::orderBy('id', 'desc')->get();

        return DataTables::of($notify)
            ->editColumn('id', function ($notify) {
                return $notify->id;
            })
            ->addColumn('target', function ($notify) {
                if (preg_match('/^\[(.*?)\]\s*(.*)$/', $notify->message, $matches)) {
                    $role = strtolower($matches[1]);
                    $badgeClasses = [
                        'all' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 border border-purple-200',
                        'patient' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200',
                        'doctor' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-200',
                        'pharmacy' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200',
                    ];
                    $cls = $badgeClasses[$role] ?? 'bg-gray-100 text-gray-800 border border-gray-200';
                    return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold ' . $cls . '">' . ucfirst($role) . '</span>';
                }
                return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 border border-purple-200">All</span>';
            })
            ->editColumn('message', function ($notify) {
                if (preg_match('/^\[(.*?)\]\s*(.*)$/', $notify->message, $matches)) {
                    return e($matches[2]);
                }
                return e($notify->message);
            })
            ->addColumn('created_at', function ($notify) {
                return $notify->created_at ? $notify->created_at->format('M d, Y h:i A') : 'N/A';
            })
            ->rawColumns(['target'])
            ->make(true);
    }

    public function savenotification()
    {
        return view("admin.addnotification");
    }

    public function sendnotificationtouser(Request $request)
    {
        if (empty($request->get("message"))) {
            Session::flash('message', __('Message Is Required'));
            Session::flash('alert-class', 'alert-danger');
            return redirect()->back();
        }

        $title = $request->get("title", __("Notification"));
        $msg = $request->get("message");
        $targetRole = $request->get("target_role", "all");

        $result = $this->sendNotifications($msg, $targetRole, null, null, $title);
        
        $store = new Notification();
        $store->message = "[$targetRole] " . $msg;
        $store->save();

        if (is_array($result) && isset($result['status']) && $result['status'] === true) {
            Session::flash('message', __('Notification broadcast saved & sent to ') . $result['sent_count'] . __(' of ') . $result['total_target'] . __(' target devices successfully.'));
            Session::flash('alert-class', 'alert-success');
        } elseif (is_numeric($result) && $result == 1) {
            Session::flash('message', __('Notification Broadcast Sent Successfully'));
            Session::flash('alert-class', 'alert-success');
        } else {
            $err = is_array($result) && isset($result['error_message']) ? $result['error_message'] : __('Failed to send notification. Please check Firebase credentials.');
            Session::flash('message', $err);
            Session::flash('alert-class', 'alert-danger');
        }

        return redirect("admin/sendnotification");
    }

    public function generateAccessToken()
    {
        $setting = Setting::find(1);
        if (!$setting || !$setting->not_json_filename) {
            throw new \Exception("Firebase JSON key filename is not configured in settings.");
        }
        $resolvedPath = $this->resolveFirebaseJsonPath($setting);

        if (!$resolvedPath) {
            throw new \Exception("Firebase JSON key file not found at: " . public_path('upload/jsonfile/' . $setting->not_json_filename));
        }
        $contents = File::get($resolvedPath);
        $accountJson = json_decode($contents, true);

        $scopes = array(
            "https://www.googleapis.com/auth/userinfo.email",
            "https://www.googleapis.com/auth/firebase.database",
            "https://www.googleapis.com/auth/firebase.messaging"
        );

        $client = new Google_Client();
        $client->setAuthConfig($accountJson);
        $client->setScopes($scopes);
        $accessToken = $client->fetchAccessTokenWithAssertion()["access_token"];
        return $accessToken;
    }

    public function sendNotifications($msg, $targetRole = 'all', $id = null, $field = null, $title = null)
    {
        $title = $title ?: config('app.name', __('Notification'));

        try {
            $accessToken = $this->generateAccessToken();
        } catch (\Exception $e) {
            \Log::error("Failed to generate notification access token: " . $e->getMessage());
            return [
                'status' => false,
                'sent_count' => 0,
                'total_target' => 0,
                'error_message' => __('FCM Service Account JSON Key missing or invalid: ') . $e->getMessage()
            ];
        }

        $usersQuery = TokenData::query();

        if ($targetRole === 'patient') {
            $usersQuery->where(function($q) {
                $q->whereIn('type', ['1', 'patient'])->orWhereNotNull('user_id');
            });
        } elseif ($targetRole === 'doctor') {
            $usersQuery->where(function($q) {
                $q->whereIn('type', ['2', 'doctor'])->orWhereNotNull('doctor_id');
            });
        } elseif ($targetRole === 'pharmacy') {
            $usersQuery->whereIn('type', ['3', 'pharmacy']);
        }

        if ($id !== null && $field !== null) {
            $usersQuery->where($field, $id);
        }

        $getusers = $usersQuery->get();
        $totalTarget = $getusers->count();

        if ($totalTarget === 0) {
            return [
                'status' => true,
                'sent_count' => 0,
                'total_target' => 0,
                'error_message' => __('No registered device tokens found for selected target role.')
            ];
        }

        $setting = Setting::find(1);
        $resolvedPath = $this->resolveFirebaseJsonPath($setting);
        if (!$resolvedPath) {
            return [
                'status' => false,
                'sent_count' => 0,
                'total_target' => $totalTarget,
                'error_message' => __('Firebase JSON key file missing.')
            ];
        }

        $contents = File::get($resolvedPath);
        $accountJson = json_decode($contents);
        $api = "https://fcm.googleapis.com/v1/projects/" . $accountJson->project_id . "/messages:send";

        $headers = array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $accessToken
        );

        $succ = 0;

        foreach ($getusers as $user) {
            if (empty($user->token) || in_array($user->token, ['no_fcm_token', 'no_token_provided', 'null', 'undefined'])) {
                continue;
            }

            $message = array(
                "message" => array(
                    "token" => $user->token,
                    "notification" => array(
                        "title" => $title,
                        "body" => $msg
                    ),
                    "data" => array(
                        'message' => (string)$msg,
                        'body' => (string)$msg,
                        'title' => (string)$title,
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ),
                    "android" => array(
                        "priority" => "HIGH",
                        "notification" => array(
                            "channel_id" => "chiremba_notifications",
                            "sound" => "default",
                            "default_sound" => true,
                            "default_vibrate_timings" => true,
                        )
                    ),
                    "apns" => array(
                        "headers" => array(
                            "apns-priority" => "10",
                            "apns-push-type" => "alert",
                            "apns-topic" => "com.kumba.care",
                        ),
                        "payload" => array(
                            "aps" => array(
                                "alert" => array(
                                    "title" => $title,
                                    "body" => $msg
                                ),
                                "sound" => "default",
                                "badge" => 1,
                                "content-available" => 1,
                                "mutable-content" => 1,
                            )
                        )
                    )
                )
            );

            $ch = curl_init($api);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

            $response = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($statusCode == 200) {
                $succ++;
            } else {
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('mobile_diagnostic_logs')) {
                        \App\Models\MobileDiagnosticLog::create([
                            'category' => 'fcm',
                            'level' => 'error',
                            'platform' => $user->platform ?? 'unknown',
                            'user_role' => $targetRole,
                            'user_id' => $user->user_id ?: $user->doctor_id,
                            'fcm_token' => $user->token,
                            'apns_token' => $user->apns_token ?? null,
                            'title' => "Broadcast FCM Failed (HTTP {$statusCode})",
                            'message' => $msg,
                            'metadata' => (string)$response,
                        ]);
                    }
                } catch (\Exception $e) {}
                if (str_contains((string)$response, 'UNREGISTERED') || str_contains((string)$response, 'NotRegistered')) {
                    TokenData::where('token', $user->token)->delete();
                }
            }
        }

        return [
            'status' => true,
            'sent_count' => $succ,
            'total_target' => $totalTarget
        ];
    }
}
