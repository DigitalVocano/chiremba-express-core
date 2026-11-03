<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeveloperKey;
use App\Models\MobileDiagnosticLog;
use App\Models\Reportspam;
use App\Models\TokenData;
use App\Models\Patient;
use App\Models\Doctors;
use App\Models\Setting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Google_Client;

class DeveloperController extends Controller
{
    /**
     * Ensure mobile_diagnostic_logs table and savetoken columns exist automatically.
     */
    private function ensureDiagnosticSchema(): void
    {
        try {
            if (!Schema::hasTable('mobile_diagnostic_logs')) {
                Schema::create('mobile_diagnostic_logs', function ($table) {
                    $table->id();
                    $table->string('category', 50)->default('general')->index();
                    $table->string('level', 20)->default('info')->index();
                    $table->string('platform', 30)->nullable()->index();
                    $table->string('os_version', 60)->nullable();
                    $table->string('device_model', 100)->nullable();
                    $table->string('app_version', 40)->nullable();
                    $table->string('user_role', 30)->nullable()->index();
                    $table->unsignedBigInteger('user_id')->nullable()->index();
                    $table->string('user_name', 120)->nullable();
                    $table->text('fcm_token')->nullable();
                    $table->text('apns_token')->nullable();
                    $table->string('permission_status', 60)->nullable();
                    $table->string('title', 255)->nullable();
                    $table->text('message')->nullable();
                    $table->longText('stack_trace')->nullable();
                    $table->longText('metadata')->nullable();
                    $table->string('ip_address', 45)->nullable();
                    $table->boolean('is_resolved')->default(false)->index();
                    $table->timestamps();
                });
            }

            if (Schema::hasTable('savetoken') && !Schema::hasColumn('savetoken', 'platform')) {
                Schema::table('savetoken', function ($table) {
                    $table->string('platform', 30)->nullable()->after('type');
                    $table->text('apns_token')->nullable()->after('platform');
                    $table->string('device_model', 100)->nullable()->after('apns_token');
                    $table->string('app_version', 40)->nullable()->after('device_model');
                    $table->string('permission_status', 60)->nullable()->after('app_version');
                });
            }
        } catch (\Exception $e) {
            // Ignore schema auto-creation errors if DB permissions restrict DDL
        }
    }

    /**
     * Auto-heal & backfill savetoken from Firebase Realtime Database (117{userId} / 100{doctorId})
     * and remove duplicate unlinked guest token rows.
     */
    private function syncAndHealTokens(bool $force = false): void
    {
        try {
            $cacheKey = 'dev_console_rtdb_token_sync';
            if (!$force && \Illuminate\Support\Facades\Cache::has($cacheKey)) {
                return;
            }
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, 600); // 10 min cache

            // 1. Sync tokens from Firebase RTDB (117{userId} => patient, 100{doctorId} => doctor)
            $notifService = new \App\Services\NotificationService();
            $accessToken = $notifService->generateAccessToken();
            if ($accessToken) {
                $url = "https://kumba-care-default-rtdb.firebaseio.com/.json?access_token=" . urlencode($accessToken);
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 6);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $raw = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200 && $raw) {
                    $nodes = json_decode($raw, true);
                    if (is_array($nodes)) {
                        foreach ($nodes as $nodeKey => $nodeVal) {
                            if (!is_array($nodeVal) || empty($nodeVal['TokenList'])) {
                                continue;
                            }
                            $nodeKeyStr = (string)$nodeKey;
                            $userId = null;
                            $doctorId = null;
                            $type = '1';

                            if (str_starts_with($nodeKeyStr, '117') && strlen($nodeKeyStr) > 3) {
                                $userId = (int)substr($nodeKeyStr, 3);
                                $type = '1';
                            } elseif (str_starts_with($nodeKeyStr, '100') && strlen($nodeKeyStr) > 3) {
                                $doctorId = (int)substr($nodeKeyStr, 3);
                                $type = '2';
                            } else {
                                continue;
                            }

                            $tList = is_array($nodeVal['TokenList']) ? array_values($nodeVal['TokenList']) : [$nodeVal['TokenList']];
                            foreach ($tList as $tok) {
                                if (!is_string($tok) || strlen(trim($tok)) < 20) {
                                    continue;
                                }
                                $tok = trim($tok);
                                $existing = TokenData::where('token', $tok)->orderBy('id', 'DESC')->first();
                                if ($existing) {
                                    $changed = false;
                                    if ($userId && empty($existing->user_id)) {
                                        $existing->user_id = $userId;
                                        $changed = true;
                                    }
                                    if ($doctorId && empty($existing->doctor_id)) {
                                        $existing->doctor_id = $doctorId;
                                        $existing->type = '2';
                                        $changed = true;
                                    }
                                    if ($changed) {
                                        $existing->save();
                                    }
                                    // Delete older duplicate rows for the same token
                                    TokenData::where('token', $tok)->where('id', '!=', $existing->id)->delete();
                                } else {
                                    TokenData::create([
                                        'token' => $tok,
                                        'type' => $type,
                                        'user_id' => $userId,
                                        'doctor_id' => $doctorId,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Non-blocking
        }
    }

    /**
     * Display Developer Console (Crashlytics, Push/iOS Diagnostics, User Bug Reports, Server Logs, XAPI Keys).
     */
    public function index(Request $request)
    {
        $this->ensureDiagnosticSchema();
        $this->syncAndHealTokens($request->boolean('sync_tokens'));

        $activeTab = $request->get('tab', 'crashlytics');
        if ($activeTab === 'overview') {
            $activeTab = 'crashlytics';
        } elseif ($activeTab === 'keys') {
            $activeTab = 'api_keys';
        }

        // 1. Developer API Keys
        $keyQuery = DeveloperKey::query()->orderBy('id', 'DESC');
        if ($request->filled('search')) {
            $s = $request->search;
            $keyQuery->where(function ($q) use ($s) {
                $q->where('app_name', 'LIKE', "%$s%")
                  ->orWhere('developer_name', 'LIKE', "%$s%")
                  ->orWhere('developer_email', 'LIKE', "%$s%")
                  ->orWhere('api_key', 'LIKE', "%$s%");
            });
        }
        $keys = $keyQuery->paginate(15, ['*'], 'keys_page');

        // 2. Crash & Diagnostic Logs
        $diagLogs = collect();
        $totalLogsCount = 0;
        $crashCount = 0;
        $fcmErrorCount = 0;
        $iosEventCount = 0;
        if (Schema::hasTable('mobile_diagnostic_logs')) {
            $logQuery = MobileDiagnosticLog::query()->orderBy('id', 'DESC');
            if ($request->filled('category')) {
                $logQuery->where('category', $request->category);
            }
            if ($request->filled('platform')) {
                $logQuery->where('platform', $request->platform);
            }
            if ($request->filled('level')) {
                $logQuery->where('level', $request->level);
            }
            if ($request->filled('log_search')) {
                $ls = $request->log_search;
                $logQuery->where(function ($q) use ($ls) {
                    $q->where('title', 'LIKE', "%$ls%")
                      ->orWhere('message', 'LIKE', "%$ls%")
                      ->orWhere('device_model', 'LIKE', "%$ls%")
                      ->orWhere('user_name', 'LIKE', "%$ls%");
                });
            }
            $diagLogs = $logQuery->paginate(25, ['*'], 'logs_page');

            $totalLogsCount = MobileDiagnosticLog::count();
            $crashCount = MobileDiagnosticLog::where('category', 'crash')->where('is_resolved', false)->count();
            $fcmErrorCount = MobileDiagnosticLog::whereIn('category', ['fcm', 'push_notification'])->whereIn('level', ['warning', 'error', 'critical', 'fatal'])->count();
            $iosEventCount = MobileDiagnosticLog::where('platform', 'ios')->count();
        }

        // 3. Registered Push Tokens & iOS/Android Inspector
        $iosTokensCount = 0;
        $androidTokensCount = 0;
        if (Schema::hasTable('savetoken')) {
            if (Schema::hasColumn('savetoken', 'platform')) {
                $iosTokensCount = TokenData::where('platform', 'ios')->count();
                $androidTokensCount = TokenData::where(function ($q) {
                    $q->where('platform', 'android')->orWhereNull('platform')->orWhere('platform', '');
                })->count();
            } else {
                $androidTokensCount = TokenData::count();
            }
        }

        $tokenQuery = TokenData::query()
            ->orderByRaw('CASE WHEN user_id IS NOT NULL OR doctor_id IS NOT NULL THEN 0 ELSE 1 END ASC')
            ->orderBy('id', 'DESC');

        if ($request->filled('token_search')) {
            $ts = trim($request->get('token_search'));
            $matchingPatientIds = Patient::where('name', 'LIKE', "%$ts%")
                ->orWhere('email', 'LIKE', "%$ts%")
                ->orWhere('phone', 'LIKE', "%$ts%")
                ->pluck('id')
                ->toArray();
            $matchingDoctorIds = Doctors::where('name', 'LIKE', "%$ts%")
                ->orWhere('email', 'LIKE', "%$ts%")
                ->orWhere('phoneno', 'LIKE', "%$ts%")
                ->pluck('id')
                ->toArray();

            $tokenQuery->where(function ($q) use ($ts, $matchingPatientIds, $matchingDoctorIds) {
                $q->where('token', 'LIKE', "%$ts%");
                if (!empty($matchingPatientIds)) {
                    $q->orWhereIn('user_id', $matchingPatientIds);
                }
                if (!empty($matchingDoctorIds)) {
                    $q->orWhereIn('doctor_id', $matchingDoctorIds);
                }
            });
        }

        $tokens = $tokenQuery->take(150)->get()->map(function ($tk) {
            $ownerName = 'Guest Device (Not Logged In)';
            $ownerEmail = '';
            $ownerRole = 'Guest';
            if (!empty($tk->doctor_id)) {
                $d = Doctors::find($tk->doctor_id);
                if ($d) {
                    $ownerName = $d->name;
                    $ownerEmail = $d->phoneno ?: $d->email;
                    $ownerRole = ($d->profile_type == 2) ? 'Pharmacy' : 'Doctor';
                } else {
                    $ownerName = 'Doctor #' . $tk->doctor_id;
                    $ownerRole = 'Doctor';
                }
            } elseif (!empty($tk->user_id)) {
                $p = Patient::find($tk->user_id);
                if ($p) {
                    $ownerName = $p->name;
                    $ownerEmail = $p->phone ?: $p->email;
                    $ownerRole = 'Patient';
                } else {
                    $ownerName = 'Patient #' . $tk->user_id;
                    $ownerRole = 'Patient';
                }
            }
            $tk->owner_name = $ownerName;
            $tk->owner_contact = $ownerEmail;
            $tk->owner_role = $ownerRole;
            return $tk;
        });

        // 4. User Bug Reports (from Reportspam + MobileDiagnosticLog bug_report)
        $bugReports = collect();
        try {
            $spamReports = Reportspam::orderBy('id', 'DESC')->take(50)->get()->map(function ($r) {
                $p = $r->user_id ? Patient::find($r->user_id) : null;
                return (object)[
                    'id' => $r->id,
                    'source' => 'reportspam',
                    'user_id' => $r->user_id,
                    'user_name' => $p ? $p->name : ('User #' . $r->user_id),
                    'user_contact' => $p ? ($p->phone ?: $p->email) : '',
                    'title' => $r->title,
                    'description' => $r->description,
                    'platform' => 'mobile',
                    'device_model' => '',
                    'app_version' => '',
                    'created_at' => $r->created_at,
                ];
            });
            $bugReports = $spamReports;
        } catch (\Exception $e) {}

        // 5. Parse Recent Laravel Server Logs (storage/logs/laravel.log)
        $serverLogs = $this->parseRecentLaravelLogs(150);

        // 6. Check Firebase Service Account Status
        $setting = Setting::find(1);
        $fcmProject = null;
        $fcmClientEmail = null;
        if ($setting && $setting->not_json_filename) {
            $pPath = public_path('upload/jsonfile/' . $setting->not_json_filename);
            $sPath = storage_path('app/jsonfile/' . $setting->not_json_filename);
            $resolved = File::exists($pPath) ? $pPath : (File::exists($sPath) ? $sPath : null);
            if ($resolved) {
                $json = json_decode(File::get($resolved), true);
                $fcmProject = $json['project_id'] ?? null;
                $fcmClientEmail = $json['client_email'] ?? null;
            }
        }
        $firebaseConfigured = !empty($fcmClientEmail);
        $firebaseEmail = $fcmClientEmail;

        $stats = [
            'total_logs' => $totalLogsCount,
            'crashes_24h' => $crashCount,
            'push_errors_24h' => $fcmErrorCount,
            'ios_logs' => $iosEventCount,
            'ios_tokens' => $iosTokensCount,
            'android_tokens' => $androidTokensCount,
            'bug_reports' => $bugReports->count(),
        ];

        return view('admin.developers.index', compact(
            'keys',
            'diagLogs',
            'crashCount',
            'fcmErrorCount',
            'iosEventCount',
            'tokens',
            'bugReports',
            'serverLogs',
            'activeTab',
            'fcmProject',
            'fcmClientEmail',
            'firebaseConfigured',
            'firebaseEmail',
            'stats'
        ));
    }

    /**
     * Parse the last N entries of storage/logs/laravel.log
     */
    private function parseRecentLaravelLogs(int $limit = 150): array
    {
        $logFile = storage_path('logs/laravel.log');
        if (!File::exists($logFile)) {
            return [];
        }

        try {
            // Read last ~250KB of the log file for speed
            $size = File::size($logFile);
            if ($size <= 0) {
                return [];
            }
            $readBytes = min($size, 262144);
            $handle = fopen($logFile, 'rb');
            if (!$handle) return [];
            if ($size > $readBytes) {
                fseek($handle, -$readBytes, SEEK_END);
            }
            $content = fread($handle, $readBytes);
            fclose($handle);

            $pattern = '/^\[(\d{4}-\d{2}-\d{2}[T\s]\d{2}:\d{2}:\d{2}[^\]]*)\]\s+(\w+)\.(\w+):\s+(.*?)(?=\n\[\d{4}-\d{2}-\d{2}|\z)/ms';
            preg_match_all($pattern, (string)$content, $matches, PREG_SET_ORDER);

            $entries = [];
            foreach (array_reverse($matches) as $m) {
                $fullText = trim($m[4]);
                $firstLine = strtok($fullText, "\n");
                $stack = trim(substr($fullText, strlen($firstLine)));
                $entries[] = [
                    'timestamp' => $m[1],
                    'env' => $m[2],
                    'level' => strtoupper($m[3]),
                    'message' => $firstLine,
                    'context' => $stack,
                    'trace' => $stack,
                ];
                if (count($entries) >= $limit) {
                    break;
                }
            }
            return $entries;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Live Test Push Notification to any specific device token (returns exact Google FCM v1 / APNs response).
     */
    public function testPushToken(Request $request)
    {
        $token = trim($request->get('token', ''));
        $title = $request->get('title', 'Chiremba iOS/Android Push Diagnostic');
        $body = $request->get('body', 'Diagnostic test push sent from Developer Console at ' . now()->format('H:i:s'));

        if (empty($token)) {
            return redirect()->to(url('/admin/developers?tab=push'))->with('error', 'Please provide a valid FCM token.');
        }

        try {
            $notifService = new \App\Services\NotificationService();
            $accessToken = $notifService->generateAccessToken();
            $jsonPath = $notifService->getFirebaseJsonPath();

            if (!$accessToken || !$jsonPath) {
                return redirect()->to(url('/admin/developers?tab=push'))->with('error', 'Firebase Service Account JSON is missing or invalid.');
            }

            $accountJson = json_decode(File::get($jsonPath));
            $apiEndpoint = "https://fcm.googleapis.com/v1/projects/" . $accountJson->project_id . "/messages:send";

            $payload = [
                "message" => [
                    "token" => $token,
                    "notification" => [
                        "title" => $title,
                        "body" => $body
                    ],
                    "data" => [
                        "message" => $body,
                        "body" => $body,
                        "title" => $title,
                        "type" => "diagnostic_test",
                        "click_action" => "FLUTTER_NOTIFICATION_CLICK"
                    ],
                    "android" => [
                        "priority" => "HIGH",
                        "notification" => [
                            "channel_id" => "chiremba_notifications",
                            "sound" => "default",
                            "default_sound" => true
                        ]
                    ],
                    "apns" => [
                        "headers" => [
                            "apns-priority" => "10",
                            "apns-push-type" => "alert",
                            "apns-topic" => "com.kumba.care"
                        ],
                        "payload" => [
                            "aps" => [
                                "alert" => [
                                    "title" => $title,
                                    "body" => $body
                                ],
                                "sound" => "default",
                                "badge" => 1,
                                "content-available" => 1,
                                "mutable-content" => 1
                            ]
                        ]
                    ]
                ]
            ];

            $ch = curl_init($apiEndpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json",
                "Authorization: Bearer " . $accessToken
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (Schema::hasTable('mobile_diagnostic_logs')) {
                MobileDiagnosticLog::create([
                    'category' => 'fcm',
                    'level' => $httpCode == 200 ? 'info' : 'error',
                    'platform' => 'admin_test',
                    'fcm_token' => $token,
                    'title' => "Admin Diagnostic Push Test (HTTP {$httpCode})",
                    'message' => $body,
                    'metadata' => $response,
                    'ip_address' => $request->ip(),
                ]);
            }

            if ($httpCode == 200) {
                return redirect()->to(url('/admin/developers?tab=push'))
                    ->with('success', "Push accepted by FCM & APNs (HTTP 200)! Response: {$response}");
            } else {
                return redirect()->to(url('/admin/developers?tab=push'))
                    ->with('error', "FCM/APNs Rejected Push (HTTP {$httpCode}): {$response}");
            }
        } catch (\Exception $e) {
            return redirect()->to(url('/admin/developers?tab=push'))
                ->with('error', 'Exception during push test: ' . $e->getMessage());
        }
    }

    /**
     * Clear diagnostic logs or mark resolved.
     */
    public function clearDiagnosticLogs(Request $request)
    {
        if (Schema::hasTable('mobile_diagnostic_logs')) {
            $category = $request->get('category');
            if ($category) {
                MobileDiagnosticLog::where('category', $category)->delete();
            } else {
                MobileDiagnosticLog::truncate();
            }
        }
        return redirect()->back()->with('success', 'Diagnostic logs cleared successfully.');
    }

    /**
     * Clear Laravel server log file and prune bloated Telescope/Session files to free cPanel disk quota.
     */
    public function clearServerLogs()
    {
        $logFile = storage_path('logs/laravel.log');
        if (File::exists($logFile)) {
            File::put($logFile, '');
        }
        try {
            if (Schema::hasTable('telescope_entries_tags')) {
                \Illuminate\Support\Facades\DB::table('telescope_entries_tags')->truncate();
            }
            if (Schema::hasTable('telescope_entries')) {
                \Illuminate\Support\Facades\DB::table('telescope_entries')->delete();
            }
        } catch (\Exception $e) {}

        return redirect()->to(url('/admin/developers?tab=server_logs'))->with('success', 'Laravel server log & Telescope bloat cleared.');
    }

    public function toggleStatus($id)
    {
        $key = DeveloperKey::findOrFail($id);
        $key->is_active = !$key->is_active;
        $key->save();

        return redirect()->to(url('/admin/developers?tab=api_keys'))->with('success', 'Developer API key status updated successfully.');
    }

    public function regenerateSecret($id)
    {
        $key = DeveloperKey::findOrFail($id);
        $key->secret_key = 'ch_sec_' . Str::random(48);
        $key->save();

        return redirect()->to(url('/admin/developers?tab=api_keys'))->with('success', 'New secret key generated for ' . $key->app_name);
    }

    public function destroy($id)
    {
        $key = DeveloperKey::findOrFail($id);
        $key->delete();

        return redirect()->to(url('/admin/developers?tab=api_keys'))->with('success', 'Developer API key revoked and removed.');
    }
}
