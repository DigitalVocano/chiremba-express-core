<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Doctors;
use App\Models\TokenData;

class DoctorAuthController extends Controller
{
    public function doctorregister(Request $request)
    {
        $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'phone' => 'required',
            'name' => 'required',
            'token' => 'required'
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
            $email = $request->get("email") ?: $request->get("phone") . '@placeholder.chiremba.com';
            $password = $request->get("password") ?: \Illuminate\Support\Str::random(12);
            
            $getuser = Doctors::where("email", $email)->orWhere("phoneno", $request->get("phone"))->first();
            if (empty($getuser)) {
                $inset = new Doctors();
                $inset->phoneno = $request->get("phone");
                $inset->name = $request->get("name");
                $inset->password = $password;
                $inset->email = $email;
                $inset->save();

                $tokenVal = $request->get("token");
                if ($tokenVal && !in_array($tokenVal, ['no_fcm_token', 'no_token_provided', 'null', 'undefined'])) {
                    $store = TokenData::where("token", $tokenVal)->first() ?: new TokenData();
                    $store->token = $tokenVal;
                    $store->doctor_id = $inset->id;
                    $store->type = '2';
                    $store->save();
                }

                $response['success'] = "1";
                $response['register'] = array("doctor_id" => $inset->id, "name" => $request->get("name"), "phone" => $inset->phoneno, "email" => $inset->email);
            } else {
                $response['success'] = "0";
                $response['register'] = "Email or Phone Already Registered";
            }
        }
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    public function doctorlogin(Request $request)
    {
        if (!$request->has('token') || empty($request->input('token'))) {
            $request->merge(['token' => 'no_token_provided']);
        }

        $response = array("success" => "0", "register" => "Validation error");
        $rules = ['email' => 'required', 'token' => 'required'];
        $loginType = (string)$request->get('login_type', '1');
        $authType = (string)$request->get('auth_type', '');

        if (!in_array($loginType, ['2', '3', '4', 'otp']) && $authType !== 'otp') {
            $rules['password'] = 'required';
        } elseif ($authType === 'otp' || $loginType === 'otp') {
            $rules['otp'] = 'required';
        }
        $messages = [
            'email.required' => "Email or Phone is required",
            'token.required' => "token is required"
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $message = implode(", ", $validator->messages()->all());
            $response['register'] = $message;
        } else {
            $loginField = $request->get("email");
            $cleanField = preg_replace('/[^0-9]/', '', $loginField);
            $isDemo = str_ends_with($cleanField, '771234567') || str_ends_with($cleanField, '771234568') || str_ends_with($cleanField, '771234569');

            if (filter_var($loginField, FILTER_VALIDATE_EMAIL)) {
                $getuser = Doctors::where("email", $loginField)->first();
            } else {
                $variants = UserAuthController::getPhoneVariants($loginField);
                $getuser = Doctors::whereIn("phoneno", $variants)->first();
            }

            $isValid = false;
            if (in_array($loginType, ['2', '3', '4'])) {
                if (!$getuser) {
                    $getuser = new Doctors();
                    $getuser->name = $request->get("name") ?: 'Doctor User';
                    $getuser->email = $loginField;
                    $getuser->phoneno = null;
                    $getuser->password = \Illuminate\Support\Str::random(16);
                    $getuser->profile_type = 1;
                    $getuser->is_approve = 0;
                    $getuser->save();
                }
                $isValid = true;
            } elseif ($getuser) {
                if ($authType === 'otp' || $loginType === 'otp') {
                    $otp = $request->get('otp');
                    $variants = UserAuthController::getPhoneVariants($loginField);
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
                    }
                } else {
                    $isValid = Hash::check($request->get("password"), $getuser->password);
                }
            } else {
                if ($authType === 'otp' || $loginType === 'otp') {
                    $otp = $request->get('otp');
                    $variants = UserAuthController::getPhoneVariants($loginField);
                    $cachedOtp = null;
                    foreach ($variants as $v) {
                        if (\Illuminate\Support\Facades\Cache::has('otp_' . $v)) {
                            $cachedOtp = \Illuminate\Support\Facades\Cache::get('otp_' . $v);
                            break;
                        }
                    }
                    if ($otp && (($cachedOtp && $otp == $cachedOtp) || ($isDemo && $otp == '123456'))) {
                        $getuser = new Doctors();
                        $getuser->phoneno = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? null : $loginField;
                        $getuser->name = 'New Provider';
                        $getuser->email = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? $loginField : $loginField . '@placeholder.chiremba.com';
                        $getuser->password = \Illuminate\Support\Str::random(12);
                        $getuser->profile_type = 1;
                        $getuser->is_approve = 0;
                        if ($isDemo) {
                            $getuser->is_demo = 1;
                            $getuser->is_approve = 1;
                        }
                        $getuser->save();
                        $isValid = true;
                        foreach ($variants as $v) {
                            \Illuminate\Support\Facades\Cache::forget('otp_' . $v);
                        }
                    }
                }
            }

            if ($isValid && $getuser) {
                $tokenVal = $request->get("token");
                if ($tokenVal && !in_array($tokenVal, ['no_fcm_token', 'no_token_provided', 'null', 'undefined'])) {
                    $oldTokens = TokenData::where("doctor_id", $getuser->id)->where('token', '!=', $tokenVal)->pluck('token')->toArray();
                    if (!empty($oldTokens)) {
                        $notifService = new \App\Services\NotificationService();
                        $notifService->sendForceLogoutNotification($oldTokens);
                        TokenData::where("doctor_id", $getuser->id)->where('token', '!=', $tokenVal)->delete();
                    }
                    $store = TokenData::where("token", $tokenVal)->first() ?: new TokenData();
                    $store->token = $tokenVal;
                    $store->doctor_id = $getuser->id;
                    $store->type = ($getuser->profile_type == '2') ? '3' : '2';
                    $store->save();
                }
                $role = ($getuser->profile_type == '2') ? 'pharmacy' : 'doctor';
                $response['success'] = "1";
                $response['role'] = $role;
                $response['register'] = array("doctor_id" => $getuser->id, "user_id" => $getuser->id, "name" => $getuser->name, "phone" => $getuser->phoneno, "email" => $getuser->email, "role" => $role);
            } else {
                $response['success'] = "0"; 
                $response['register'] = ($authType === 'otp' || $loginType === 'otp') ? "Invalid OTP Code" : "Invalid Credentials";
            }
        }
        return json_encode($response, JSON_NUMERIC_CHECK);
    }
}
