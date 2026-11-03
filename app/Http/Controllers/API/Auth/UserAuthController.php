<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Patient;
use App\Models\TokenData;

class UserAuthController extends Controller
{
    public function postregisterpatient(Request $request)
    {
        $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'phone' => 'required',
            'token' => 'required',
            'name' => 'required'
        ];

        $messages = array(
            'phone.required' => "Mobile No is required",
            'token.required' => "token is required",
            'name.required' => 'name is required'
        );

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $message = '';
            $messages_l = json_decode(json_encode($validator->messages()), true);
            foreach ($messages_l as $msg) {
                $message .= $msg[0] . ", ";
            }
            $response['register'] = $message;
        } else {
            $getuser = Patient::whereIn("phone", self::getPhoneVariants($request->get("phone")))->first();
            if (empty($getuser)) { //update token
                $email = $request->get("email") ?: $request->get("phone") . '@placeholder.chiremba.com';
                $password = $request->get("password") ?: \Illuminate\Support\Str::random(12);
                
                $getemail = Patient::where("email", $email)->first();
                if ($getemail) {
                    $response['success'] = "0";
                    $response['register'] = "Email Id Already Register";
                } else {
                    $inset = new Patient();
                    $inset->phone = $request->get("phone");
                    $inset->name = $request->get("name");
                    $inset->password = Hash::make($password);
                    $inset->email = $email;
                    $inset->save();
                    $tokenVal = $request->get("token");
                    if ($tokenVal && !in_array($tokenVal, ['no_fcm_token', 'no_token_provided', 'null', 'undefined'])) {
                        $store = TokenData::where("token", $tokenVal)->first() ?: new TokenData();
                        $store->token = $tokenVal;
                        $store->user_id = $inset->id;
                        $store->type = '1';
                        $store->save();
                        (new \App\Services\NotificationService())->syncTokenToFirebaseRtdb($inset->id, 'user_id', $tokenVal);
                    }
                    $response['success'] = "1";
                    $response['register'] = array("user_id" => $inset->id, "name" => $request->get("name"), "phone" => $inset->phone, "email" => $inset->email);
                }
            } else {
                $response['success'] = "0";
                $response['register'] = "Mobile Number Already Register";
            }
        }
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    public function storetoken(Request $request)
    {
        $response = array("success" => "0", "register" => "Validation error");
        $token = $request->get("token");

        if (empty($token) || in_array($token, ['no_fcm_token', 'no_token_provided', 'null', 'undefined'])) {
            return response()->json(['success' => 0, 'register' => 'Invalid or empty token'], 200);
        }

        $type = $request->get("type", "1");
        $userId = $request->get("user_id");
        $doctorId = $request->get("doctor_id");

        $store = TokenData::where('token', $token)->first();
        if (!$store) {
            $store = new TokenData();
            $store->token = $token;
        }

        $store->type = $type;
        if (!empty($userId)) {
            $oldTokens = TokenData::where('user_id', $userId)->where('token', '!=', $token)->pluck('token')->toArray();
            if (!empty($oldTokens)) {
                $notifService = new \App\Services\NotificationService();
                $notifService->sendForceLogoutNotification($oldTokens);
                TokenData::where('user_id', $userId)->where('token', '!=', $token)->delete();
            }
            $store->user_id = $userId;
        }
        if (!empty($doctorId)) {
            $oldTokens = TokenData::where('doctor_id', $doctorId)->where('token', '!=', $token)->pluck('token')->toArray();
            if (!empty($oldTokens)) {
                $notifService = new \App\Services\NotificationService();
                $notifService->sendForceLogoutNotification($oldTokens);
                TokenData::where('doctor_id', $doctorId)->where('token', '!=', $token)->delete();
            }
            $store->doctor_id = $doctorId;
        }
        $store->save();
        try {
            $notifService = new \App\Services\NotificationService();
            if (!empty($store->user_id)) {
                $notifService->syncTokenToFirebaseRtdb($store->user_id, 'user_id', $store->token);
            } elseif (!empty($store->doctor_id)) {
                $notifService->syncTokenToFirebaseRtdb($store->doctor_id, 'doctor_id', $store->token);
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('savetoken', 'platform')) {
                if ($request->filled('platform')) $store->platform = $request->get('platform');
                if ($request->filled('apns_token')) $store->apns_token = $request->get('apns_token');
                if ($request->filled('device_model')) $store->device_model = $request->get('device_model');
                if ($request->filled('app_version')) $store->app_version = $request->get('app_version');
                if ($request->filled('permission_status')) $store->permission_status = $request->get('permission_status');
                $store->save();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('mobile_diagnostic_logs')) {
                \App\Models\MobileDiagnosticLog::create([
                    'category' => 'fcm',
                    'level' => 'info',
                    'platform' => $request->get('platform', 'unknown'),
                    'os_version' => $request->get('os_version'),
                    'device_model' => $request->get('device_model'),
                    'app_version' => $request->get('app_version'),
                    'user_role' => ($type ?? '1') == '1' ? 'patient' : (($type ?? '1') == '3' ? 'pharmacy' : 'doctor'),
                    'user_id' => $store->user_id ?: $store->doctor_id,
                    'fcm_token' => $store->token,
                    'apns_token' => $request->get('apns_token'),
                    'permission_status' => $request->get('permission_status'),
                    'title' => 'FCM Token Synced Successfully',
                    'message' => 'Device registered FCM token (' . substr((string)$store->token, 0, 18) . '...) ' . ($request->filled('apns_token') ? 'with APNs token (' . substr($request->get('apns_token'), 0, 16) . '...)' : 'without APNs token'),
                    'ip_address' => $request->ip(),
                ]);
            }
        } catch (\Exception $e) {}

        // Prune any legacy dummy tokens
        TokenData::whereIn('token', ['no_fcm_token', 'no_token_provided', 'null', 'undefined'])->delete();

        $response['success'] = "1";
        $response['headers'] = array("Access-Control-Allow-Origin" => "*", "Access-Control-Allow-Credentials" => true, "Access-Control-Allow-Headers" => "Origin,Content-Type,X-Amz-Date,Authorization,X-Api-Key,X-Amz-Security-Token", "Access-Control-Allow-Methods" => "POST, OPTIONS,GET");
        $response['register'] = "Registered";
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    public function showlogin(Request $request)
    {
        $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'email' => 'required', // This can act as phone or email
            'token' => 'required',
            "login_type" => 'required'
        ];
        if ($request->input('login_type') == '1' && $request->input('auth_type') != 'otp') {
            $rules['password'] = 'required';
        }
        if (($request->input('auth_type') == 'otp' || $request->input('login_type') == 'otp') && $request->input('login_type') == '1') {
            $rules['otp'] = 'required';
        }
        if (in_array($request->input('login_type'), ['2', '3', '4'])) {
            $rules['name'] = 'required';
        }
        $messages = array(
            'email.required' => "Email or Phone is required",
            'password.required' => "password is required",
            'token.required' => "token is required",
            'login_type.required' => "login type is required",
            'name.required' => "name is required"
        );
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $message = '';
            $messages_l = json_decode(json_encode($validator->messages()), true);
            foreach ($messages_l as $msg) {
                $message .= $msg[0] . ", ";
            }
            $response['register'] = $message;
        } else {

            if ($request->input('login_type') == '1' || $request->input('login_type') == 'otp') {
                $loginField = $request->get("email");
                
                if (filter_var($loginField, FILTER_VALIDATE_EMAIL)) {
                    $getuser = Patient::where("email", $loginField)->first();
                } else {
                    $getuser = Patient::whereIn("phone", self::getPhoneVariants($loginField))->first();
                }
                
                $isValid = false;
                if ($getuser) {
                    if ($request->input('auth_type') == 'otp' || $request->input('login_type') == 'otp') {
                        $otp = $request->input('otp');
                        $cachedOtp = collect(self::getPhoneVariants($loginField))->map(fn($v) => \Illuminate\Support\Facades\Cache::get('otp_' . $v))->first(fn($val) => !empty($val));
                        $cleanField = preg_replace('/[^0-9]/', '', $loginField);
                        $isDemo = str_ends_with($cleanField, '771234567') || str_ends_with($cleanField, '771234568') || str_ends_with($cleanField, '771234569');

                        if ($otp && (($cachedOtp && $otp == $cachedOtp) || ($isDemo && $otp == '123456'))) {
                            $isValid = true;
                            foreach (self::getPhoneVariants($loginField) as $v) { \Illuminate\Support\Facades\Cache::forget('otp_' . $v); }
                        } else {
                            $isValid = false;
                        }
                    } else {
                        $isValid = Hash::check($request->get("password"), $getuser->password);
                    }
                } else {
                    if ($request->input('auth_type') == 'otp' || $request->input('login_type') == 'otp') {
                        $otp = $request->input('otp');
                        $cachedOtp = collect(self::getPhoneVariants($loginField))->map(fn($v) => \Illuminate\Support\Facades\Cache::get('otp_' . $v))->first(fn($val) => !empty($val));
                        $cleanField = preg_replace('/[^0-9]/', '', $loginField);
                        $isDemo = str_ends_with($cleanField, '771234567') || str_ends_with($cleanField, '771234568') || str_ends_with($cleanField, '771234569');

                        if ($otp && (($cachedOtp && $otp == $cachedOtp) || ($isDemo && $otp == '123456'))) {
                            $getuser = new Patient();
                            $getuser->phone = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? null : $loginField;
                            $getuser->name = 'New User';
                            $getuser->email = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? $loginField : $loginField . '@placeholder.chiremba.com';
                            $getuser->password = Hash::make(\Illuminate\Support\Str::random(12));
                            if ($isDemo) {
                                $getuser->is_demo = 1;
                            }
                            $getuser->save();
                            $isValid = true;
                            foreach (self::getPhoneVariants($loginField) as $v) { \Illuminate\Support\Facades\Cache::forget('otp_' . $v); }
                        } else {
                            $isValid = false;
                        }
                    }
                }
                
                if ($isValid) { //update token
                    $tokenVal = $request->get("token");
                    if ($tokenVal && !in_array($tokenVal, ['no_fcm_token', 'no_token_provided', 'null', 'undefined'])) {
                        $oldTokens = TokenData::where("user_id", $getuser->id)->where('token', '!=', $tokenVal)->pluck('token')->toArray();
                        if (!empty($oldTokens)) {
                            $notifService = new \App\Services\NotificationService();
                            $notifService->sendForceLogoutNotification($oldTokens);
                            TokenData::where("user_id", $getuser->id)->where('token', '!=', $tokenVal)->delete();
                        }
                        $store = TokenData::where('token', $tokenVal)->first() ?: new TokenData();
                        $store->token = $tokenVal;
                        $store->user_id = $getuser->id;
                        $store->type = '1';
                        $store->save();
        try {
            $notifService = new \App\Services\NotificationService();
            if (!empty($store->user_id)) {
                $notifService->syncTokenToFirebaseRtdb($store->user_id, 'user_id', $store->token);
            } elseif (!empty($store->doctor_id)) {
                $notifService->syncTokenToFirebaseRtdb($store->doctor_id, 'doctor_id', $store->token);
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('savetoken', 'platform')) {
                if ($request->filled('platform')) $store->platform = $request->get('platform');
                if ($request->filled('apns_token')) $store->apns_token = $request->get('apns_token');
                if ($request->filled('device_model')) $store->device_model = $request->get('device_model');
                if ($request->filled('app_version')) $store->app_version = $request->get('app_version');
                if ($request->filled('permission_status')) $store->permission_status = $request->get('permission_status');
                $store->save();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('mobile_diagnostic_logs')) {
                \App\Models\MobileDiagnosticLog::create([
                    'category' => 'fcm',
                    'level' => 'info',
                    'platform' => $request->get('platform', 'unknown'),
                    'os_version' => $request->get('os_version'),
                    'device_model' => $request->get('device_model'),
                    'app_version' => $request->get('app_version'),
                    'user_role' => ($type ?? '1') == '1' ? 'patient' : (($type ?? '1') == '3' ? 'pharmacy' : 'doctor'),
                    'user_id' => $store->user_id ?: $store->doctor_id,
                    'fcm_token' => $store->token,
                    'apns_token' => $request->get('apns_token'),
                    'permission_status' => $request->get('permission_status'),
                    'title' => 'FCM Token Synced Successfully',
                    'message' => 'Device registered FCM token (' . substr((string)$store->token, 0, 18) . '...) ' . ($request->filled('apns_token') ? 'with APNs token (' . substr($request->get('apns_token'), 0, 16) . '...)' : 'without APNs token'),
                    'ip_address' => $request->ip(),
                ]);
            }
        } catch (\Exception $e) {}
                    }
                    $getuser->login_type = $request->get("login_type");
                    $getuser->save();
                    if ($getuser->profile_pic != "") {
                        $image = asset("upload/profile") . '/' . $getuser->profile_pic;
                    } else {
                        $image = asset("upload/profile/profile.png");
                    }
                    $response['success'] = "1";
                    $response['headers'] = array('Access-Control-Allow-Origin' => '*');
                    $response['register'] = array("user_id" => $getuser->id, "name" => $getuser->name, "phone" => $getuser->phone, "email" => $getuser->email, "profile_pic" => $image);
                } else { //in vaild user
                    $response['success'] = "0";
                    $response['register'] = ($request->input('auth_type') == 'otp' || $request->input('login_type') == 'otp') ? "Invalid OTP Code" : "Invalid Credentials";
                }
            } else if (in_array($request->input('login_type'), ['2', '3', '4'])) {
                // Social Logins (Google, Facebook, Apple)
                $email = $request->get("email");
                $name = $request->get("name") ?: 'Apple User';

                $getuser = Patient::where("email", $email)->first();
                if (!$getuser) {
                    $getuser = new Patient();
                    $getuser->name = $name;
                    $getuser->email = $email;
                    $getuser->phone = null;
                    $getuser->password = Hash::make(\Illuminate\Support\Str::random(16));
                    $getuser->save();
                }

                $tokenVal = $request->get("token");
                if ($tokenVal && !in_array($tokenVal, ['no_fcm_token', 'no_token_provided', 'null', 'undefined'])) {
                    $oldTokens = TokenData::where("user_id", $getuser->id)->where('token', '!=', $tokenVal)->pluck('token')->toArray();
                    if (!empty($oldTokens)) {
                        $notifService = new \App\Services\NotificationService();
                        $notifService->sendForceLogoutNotification($oldTokens);
                        TokenData::where("user_id", $getuser->id)->where('token', '!=', $tokenVal)->delete();
                    }
                    $store = TokenData::where('token', $tokenVal)->first() ?: new TokenData();
                    $store->token = $tokenVal;
                    $store->user_id = $getuser->id;
                    $store->type = '1';
                    $store->save();
        try {
            $notifService = new \App\Services\NotificationService();
            if (!empty($store->user_id)) {
                $notifService->syncTokenToFirebaseRtdb($store->user_id, 'user_id', $store->token);
            } elseif (!empty($store->doctor_id)) {
                $notifService->syncTokenToFirebaseRtdb($store->doctor_id, 'doctor_id', $store->token);
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('savetoken', 'platform')) {
                if ($request->filled('platform')) $store->platform = $request->get('platform');
                if ($request->filled('apns_token')) $store->apns_token = $request->get('apns_token');
                if ($request->filled('device_model')) $store->device_model = $request->get('device_model');
                if ($request->filled('app_version')) $store->app_version = $request->get('app_version');
                if ($request->filled('permission_status')) $store->permission_status = $request->get('permission_status');
                $store->save();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('mobile_diagnostic_logs')) {
                \App\Models\MobileDiagnosticLog::create([
                    'category' => 'fcm',
                    'level' => 'info',
                    'platform' => $request->get('platform', 'unknown'),
                    'os_version' => $request->get('os_version'),
                    'device_model' => $request->get('device_model'),
                    'app_version' => $request->get('app_version'),
                    'user_role' => ($type ?? '1') == '1' ? 'patient' : (($type ?? '1') == '3' ? 'pharmacy' : 'doctor'),
                    'user_id' => $store->user_id ?: $store->doctor_id,
                    'fcm_token' => $store->token,
                    'apns_token' => $request->get('apns_token'),
                    'permission_status' => $request->get('permission_status'),
                    'title' => 'FCM Token Synced Successfully',
                    'message' => 'Device registered FCM token (' . substr((string)$store->token, 0, 18) . '...) ' . ($request->filled('apns_token') ? 'with APNs token (' . substr($request->get('apns_token'), 0, 16) . '...)' : 'without APNs token'),
                    'ip_address' => $request->ip(),
                ]);
            }
        } catch (\Exception $e) {}
                }

                $getuser->login_type = $request->get("login_type");
                $getuser->save();

                $image = ($getuser->profile_pic != "") 
                    ? asset("upload/profile") . '/' . $getuser->profile_pic 
                    : asset("upload/profile/profile.png");

                $response['success'] = "1";
                $response['headers'] = array('Access-Control-Allow-Origin' => '*');
                $response['register'] = array(
                    "user_id" => $getuser->id, 
                    "name" => $getuser->name, 
                    "phone" => $getuser->phone ?? "", 
                    "email" => $getuser->email, 
                    "profile_pic" => $image
                );
            } else {
                 $response['success'] = "0";
                 $response['register'] = "Login type not supported.";
            }
        }
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    public static function getPhoneVariants($inputField)
    {
        $inputField = trim((string)$inputField);
        $clean = preg_replace('/[^0-9]/', '', $inputField);
        if (empty($clean)) {
            return [$inputField];
        }

        $variants = [$inputField, $clean, '+' . $clean];

        // Strip Zimbabwe country code '263' if present at start
        $national = $clean;
        if (str_starts_with($national, '263')) {
            $national = substr($national, 3);
        }

        // Pure national digits without any leading zeroes (e.g. 771234567)
        $digits = ltrim($national, '0');
        if (!empty($digits)) {
            $variants[] = $digits;               // 771234567 (no leading zero)
            $variants[] = '0' . $digits;         // 0771234567 (with leading zero)
            $variants[] = '263' . $digits;       // 263771234567
            $variants[] = '+263' . $digits;      // +263771234567
            $variants[] = '00263' . $digits;     // 00263771234567
        }

        return array_values(array_unique(array_filter($variants)));
    }

    public function sendOtp(Request $request, \App\Services\ZimflowService $zimflow)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => "0", 'message' => $validator->errors()->first()], 200);
        }

        $phone = trim((string)$request->phone);
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        $nationalDigits = ltrim(str_starts_with($cleanPhone, '263') ? substr($cleanPhone, 3) : $cleanPhone, '0');
        $isDemo = str_ends_with($nationalDigits, '771234567') || str_ends_with($nationalDigits, '771234568') || str_ends_with($nationalDigits, '771234569') || str_ends_with($cleanPhone, '771234567') || str_ends_with($cleanPhone, '771234568') || str_ends_with($cleanPhone, '771234569');

        $variants = self::getPhoneVariants($phone);

        if ($isDemo) {
            $otp = '123456';
            foreach ($variants as $v) {
                \Illuminate\Support\Facades\Cache::put('otp_' . $v, $otp, 3600);
            }
            return response()->json([
                'success' => "1",
                'message' => 'OTP sent successfully (Demo Mode - Code: 123456)'
            ], 200);
        }

        $otp = rand(100000, 999999);

        // Save to cache for 5 minutes across all number variants
        foreach ($variants as $v) {
            \Illuminate\Support\Facades\Cache::put('otp_' . $v, $otp, 300);
        }

        // Send OTP using Zimflow (formatted in standard international format +263...)
        $intlPhone = !empty($nationalDigits) ? '+263' . $nationalDigits : $phone;
        $sent = $zimflow->sendAuthOtp($intlPhone, $otp);

        if ($sent) {
            return response()->json([
                'success' => "1",
                'message' => 'OTP sent successfully via WhatsApp'
            ], 200);
        } else {
            $sentSms = $zimflow->sendSmsOtp($phone, $otp);
            if ($sentSms) {
                return response()->json([
                    'success' => "1",
                    'message' => 'OTP sent successfully via SMS'
                ], 200);
            }
        }

        return response()->json(['success' => "0", 'message' => 'Failed to send OTP code. Please try again later.'], 200);
    }

    public function unifiedLogin(Request $request)
    {
        if (!$request->has('token') || empty($request->input('token'))) {
            $request->merge(['token' => 'no_token_provided']);
        }

        $rules = [
            'email' => 'required',
            'token' => 'required',
        ];
        
        $loginType = $request->input('login_type', '1');
        $authType = $request->input('auth_type', '');

        if ($loginType == '1' && $authType != 'otp') {
            $rules['password'] = 'required';
        }
        if ($authType == 'otp' || $loginType == 'otp') {
            $rules['otp'] = 'required';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => "0",
                'register' => implode(", ", $validator->messages()->all())
            ], 200);
        }

        $loginField = $request->get("email");
        $cleanField = preg_replace('/[^0-9]/', '', $loginField);
        $isDemo = str_ends_with($cleanField, '771234567') || str_ends_with($cleanField, '771234568') || str_ends_with($cleanField, '771234569');

        $variants = self::getPhoneVariants($loginField);

        // Search for user in Doctor/Pharmacy table first
        $doctorUser = \App\Models\Doctors::where(function($q) use ($loginField, $variants) {
            $q->where('email', $loginField)
              ->orWhereIn('phoneno', $variants);
        })->first();

        // Search for user in Patient table
        $patientUser = Patient::where(function($q) use ($loginField, $variants) {
            $q->where('email', $loginField)
              ->orWhereIn('phone', $variants);
        })->first();

        $targetUser = null;
        $role = null;

        if ($doctorUser) {
            $targetUser = $doctorUser;
            $role = ($doctorUser->profile_type == '2') ? 'pharmacy' : 'doctor';
        } elseif ($patientUser) {
            $targetUser = $patientUser;
            $role = 'patient';
        }

        $isValid = false;

        // Google Sign-In (login_type == 2)
        if ($loginType == '2') {
            if ($targetUser) {
                $isValid = true;
            } else {
                // Auto register new Google user as Patient
                $getuser = new Patient();
                $getuser->name = $request->get("name", "Google User");
                $getuser->email = $loginField;
                $getuser->password = Hash::make(\Illuminate\Support\Str::random(12));
                $getuser->save();
                
                $targetUser = $getuser;
                $role = 'patient';
                $isValid = true;
            }
        }
        // OTP Login
        elseif ($authType == 'otp' || $loginType == 'otp') {
            $otp = $request->input('otp');
            $cachedOtp = null;
            foreach ($variants as $v) {
                if (\Illuminate\Support\Facades\Cache::has('otp_' . $v)) {
                    $cachedOtp = \Illuminate\Support\Facades\Cache::get('otp_' . $v);
                    break;
                }
            }

            if ($otp && (($cachedOtp && $otp == $cachedOtp) || ($isDemo && $otp == '123456'))) {
                $isValid = true;
                foreach ($variants as $v) {
                    \Illuminate\Support\Facades\Cache::forget('otp_' . $v);
                }

                if (!$targetUser) {
                    // Create new user as patient if not existing
                    $getuser = new Patient();
                    $getuser->phone = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? null : $loginField;
                    $getuser->name = 'New User';
                    $getuser->email = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? $loginField : $loginField . '@placeholder.chiremba.com';
                    $getuser->password = Hash::make(\Illuminate\Support\Str::random(12));
                    if ($isDemo) {
                        $getuser->is_demo = 1;
                    }
                    $getuser->save();
                    
                    $targetUser = $getuser;
                    $role = 'patient';
                }
            }
        }
        // Password Login
        else {
            if ($targetUser) {
                $isValid = Hash::check($request->get("password"), $targetUser->password);
            }
        }

        if ($isValid && $targetUser) {
            $userId = $targetUser->id;
            $userName = $targetUser->name;
            $userPhone = $targetUser->phone ?? $targetUser->phoneno ?? '';
            $userEmail = $targetUser->email ?? '';

            // Enforce single-device login: remove previous token registrations for this user
            if ($role == 'patient') {
                if ($request->get("token") && !in_array($request->get("token"), ['no_fcm_token', 'no_token_provided', 'null', 'undefined'])) { TokenData::where("user_id", $userId)->where("token", "!=", $request->get("token"))->delete(); }
            } else {
                if ($request->get("token") && !in_array($request->get("token"), ['no_fcm_token', 'no_token_provided', 'null', 'undefined'])) { TokenData::where("doctor_id", $userId)->where("token", "!=", $request->get("token"))->delete(); }
            }

            // Update token
            $token = $request->get("token");
            if ($token && !in_array($token, ['no_fcm_token', 'no_token_provided', 'null', 'undefined'])) {
                $store = TokenData::where('token', $token)->first() ?: new TokenData(); $store->type = ($role == 'patient') ? '1' : (($role == 'pharmacy') ? '3' : '2');
                $store->token = $token;
                if ($role == 'patient') {
                    $store->user_id = $userId;
                } else {
                    $store->doctor_id = $userId;
                }
                $store->save();
        try {
            $notifService = new \App\Services\NotificationService();
            if (!empty($store->user_id)) {
                $notifService->syncTokenToFirebaseRtdb($store->user_id, 'user_id', $store->token);
            } elseif (!empty($store->doctor_id)) {
                $notifService->syncTokenToFirebaseRtdb($store->doctor_id, 'doctor_id', $store->token);
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('savetoken', 'platform')) {
                if ($request->filled('platform')) $store->platform = $request->get('platform');
                if ($request->filled('apns_token')) $store->apns_token = $request->get('apns_token');
                if ($request->filled('device_model')) $store->device_model = $request->get('device_model');
                if ($request->filled('app_version')) $store->app_version = $request->get('app_version');
                if ($request->filled('permission_status')) $store->permission_status = $request->get('permission_status');
                $store->save();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('mobile_diagnostic_logs')) {
                \App\Models\MobileDiagnosticLog::create([
                    'category' => 'fcm',
                    'level' => 'info',
                    'platform' => $request->get('platform', 'unknown'),
                    'os_version' => $request->get('os_version'),
                    'device_model' => $request->get('device_model'),
                    'app_version' => $request->get('app_version'),
                    'user_role' => ($type ?? '1') == '1' ? 'patient' : (($type ?? '1') == '3' ? 'pharmacy' : 'doctor'),
                    'user_id' => $store->user_id ?: $store->doctor_id,
                    'fcm_token' => $store->token,
                    'apns_token' => $request->get('apns_token'),
                    'permission_status' => $request->get('permission_status'),
                    'title' => 'FCM Token Synced Successfully',
                    'message' => 'Device registered FCM token (' . substr((string)$store->token, 0, 18) . '...) ' . ($request->filled('apns_token') ? 'with APNs token (' . substr($request->get('apns_token'), 0, 16) . '...)' : 'without APNs token'),
                    'ip_address' => $request->ip(),
                ]);
            }
        } catch (\Exception $e) {}
            }

            $profilePic = asset('upload/profile/profile.png');
            if (!empty($targetUser->profile_pic)) {
                $profilePic = asset('upload/profile/' . $targetUser->profile_pic);
            } elseif (!empty($targetUser->image)) {
                $profilePic = asset('upload/doctors/' . $targetUser->image);
            }

            $isSubscribed = true;
            $subRequired = false;
            if ($targetUser instanceof \App\Models\Doctors) {
                $isSubscribed = $targetUser->isSubscribedOrGlobalDisabled();
                $subRequired = $targetUser->isSubscriptionRequired();
            }

            return response()->json([
                'success' => "1",
                'role' => $role,
                'register' => [
                    'user_id' => $userId,
                    'doctor_id' => $userId,
                    'name' => $userName,
                    'phone' => $userPhone,
                    'email' => $userEmail,
                    'profile_pic' => $profilePic,
                    'image' => $profilePic,
                    'role' => $role,
                    'is_subscribed' => $isSubscribed,
                    'is_subscription' => $isSubscribed ? "1" : "0",
                    'subscription_required' => $subRequired,
                ]
            ], 200);
        }

        return response()->json([
            'success' => "0",
            'register' => ($authType == 'otp' || $loginType == 'otp') ? "Invalid OTP Code" : "Invalid Email/Phone or Password"
        ], 200);
    }

    /**
     * Account Deletion Endpoint (Complies with Apple App Store Review Guideline 5.1.1(v))
     * Permanently deletes patient/doctor accounts, active tokens, and RTDB nodes.
     */
    public function deleteAccount(Request $request)
    {
        $userId = $request->input('user_id') ?: $request->input('doctor_id');
        $role = strtolower((string)$request->input('role', 'patient'));

        if (empty($userId)) {
            return response()->json([
                'success' => "0",
                'message' => 'User ID is required to process account deletion.'
            ], 200);
        }

        try {
            $notifService = new \App\Services\NotificationService();
            $accessToken = $notifService->generateAccessToken();

            if (in_array($role, ['doctor', 'pharmacy', '2', '3'])) {
                $doc = \App\Models\Doctors::find($userId);
                if ($doc) {
                    TokenData::where('doctor_id', $userId)->delete();
                    if ($accessToken) {
                        try {
                            $ch = curl_init('https://kumba-care-default-rtdb.firebaseio.com/100' . $userId . '.json?access_token=' . urlencode($accessToken));
                            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                            curl_exec($ch);
                            curl_close($ch);
                        } catch (\Throwable $e) {}
                    }
                    $doc->delete();
                }
            } else {
                $pat = Patient::find($userId);
                if ($pat) {
                    TokenData::where('user_id', $userId)->delete();
                    if ($accessToken) {
                        try {
                            $ch = curl_init('https://kumba-care-default-rtdb.firebaseio.com/117' . $userId . '.json?access_token=' . urlencode($accessToken));
                            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                            curl_exec($ch);
                            curl_close($ch);
                        } catch (\Throwable $e) {}
                    }
                    $pat->delete();
                }
            }

            return response()->json([
                'success' => "1",
                'message' => 'Your account and personal data have been permanently deleted.'
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => "0",
                'message' => 'Unable to delete account: ' . $e->getMessage()
            ], 200);
        }
    }
}
