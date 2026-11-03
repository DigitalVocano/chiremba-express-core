<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Doctors;
use App\Models\Setting;
use App\Models\Patient;
use App\Models\ReferralSetting;
use App\Models\Referral;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function pharmacylogin(Request $request)
    {
        $loginField = trim($request->get("email"));
        $variants = \App\Http\Controllers\API\Auth\UserAuthController::getPhoneVariants($loginField);
        if (filter_var($loginField, FILTER_VALIDATE_EMAIL)) {
            $getUser = Doctors::where("email", $loginField)->where("profile_type", '2')->first();
        } else {
            $getUser = Doctors::where("profile_type", '2')
                ->where(function($q) use ($loginField, $variants) {
                    $q->whereIn('phoneno', $variants)
                      ->orWhere('email', $loginField);
                })->first();
        }

        $isValid = false;
        $cleanField = preg_replace('/[^0-9]/', '', $loginField);
        $isDemo = str_ends_with($cleanField, '771234567') || str_ends_with($cleanField, '771234568') || str_ends_with($cleanField, '771234569');

        if ($getUser) {
            if ($request->has("auth_type") && $request->get("auth_type") == "otp") {
                $otp = $request->get("otp");
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
            } elseif ($request->has("password")) {
                $isValid = Hash::check($request->get("password"), $getUser->password);
            }
        } else {
            if ($request->has("auth_type") && $request->get("auth_type") == "otp") {
                $otp = $request->get("otp");
                $cachedOtp = null;
                foreach ($variants as $v) {
                    if (\Illuminate\Support\Facades\Cache::has('otp_' . $v)) {
                        $cachedOtp = \Illuminate\Support\Facades\Cache::get('otp_' . $v);
                        break;
                    }
                }
                if ($otp && (($cachedOtp && $otp == $cachedOtp) || ($isDemo && $otp == '123456'))) {
                    $getUser = new Doctors();
                    $getUser->phoneno = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? null : $loginField;
                    $getUser->name = 'New Pharmacy';
                    $getUser->email = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? $loginField : $loginField . '@placeholder.chiremba.com';
                    $getUser->password = Str::random(12);
                    $getUser->profile_type = '2';
                    $getUser->is_approve = '1';
                    $getUser->save();
                    $isValid = true;
                    foreach ($variants as $v) {
                        \Illuminate\Support\Facades\Cache::forget('otp_' . $v);
                    }
                }
            }
        }

        if ($isValid) {
            if ($request->get("rem_me") == 1) {
                setcookie('email', $request->get("email"), time() + (86400 * 30), "/");
                setcookie('rem_me', 1, time() + (86400 * 30), "/");
            }
            Session::put("user_id", $getUser->id);
            Session::put("role_id", '3');
            return redirect()->route('pharmacydashboard');
        } else {
            Session::flash('message', __('Login Credentials Are Wrong'));
            Session::flash('alert-class', 'alert-danger');
            return redirect("profilelogin");
        }
    }

    public function postpharmacyregister(Request $request)
    {
        $email = $request->get("email") ?: $request->get("phone") . '@placeholder.chiremba.com';
        $getuser = Doctors::where("email", $email)->first();
        if ($getuser) {
            Session::flash('message', __("Email or Phone Already Exists"));
            Session::flash('alert-class', 'alert-danger');
            return redirect()->back();
        }

        $store = new Doctors();
        $store->name = $request->get("name");
        $store->email = $email;
        $store->password = $request->get("password");
        $store->phoneno = $request->get("phone");
        $store->profile_type = '2';
        $store->is_approve = '1';
        $store->save();

        Session::put("user_id", $store->id);
        Session::put("role_id", '3');
        Session::flash('message', __("Successful Register"));
        Session::flash('alert-class', 'alert-success');
        return redirect("pharmacydashboard");
    }

    public function logout()
    {
        if (Session::get('role_id') == '3') {
            Session::forget(['user_id', 'role_id']);
            setcookie('email', '', time() - 3600, "/");
            setcookie('rem_me', '', time() - 3600, "/");
        }
        return redirect("/");
    }

    public function redirectToGoogle()
    {
        Session::put('google_auth_context', 'pharmacy');
        return Socialite::driver('google')->redirect();
    }
}
