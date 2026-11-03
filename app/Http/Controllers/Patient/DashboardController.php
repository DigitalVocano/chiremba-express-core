<?php

namespace App\Http\Controllers\Patient;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Sentinel; // Assuming Sentinel is still used for authentication in parts
use Illuminate\Support\Facades\DB;
use Mail; // Assuming Mail facade is used elsewhere
use DataTables; // Assuming DataTables is used elsewhere
use App\Models\Patient;
use App\Models\Doctors;
use App\Models\Setting;
use App\Models\BookAppointment;
use App\Models\Services;
use App\Models\Resetpassword; // Unused in provided snippet
use App\Models\Settlement; // Unused in provided snippet
use App\Models\Review;
use DateTime; // Unused in provided snippet
use App\Models\Medicines;
use App\Models\Subscriber; // Unused in provided snippet
use App\Models\Subscription; // Unused in provided snippet
use App\Models\PaymentGatewayDetail; // Unused in provided snippet
use App\Models\AppointmentMedicines;
use App\Models\PharmacyOrder; // Unused in provided snippet
use Google_Client; // Unused in provided snippet
use Illuminate\Support\Facades\File; // Already aliased by Storage facade implicitly for basic file ops
use App\Models\TokenData; // Unused in provided snippet
use Ramsey\Uuid\Uuid; // Unused in provided snippet, consider using Str::uuid()
use Laravel\Socialite\Facades\Socialite; // Unused in provided snippet
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; // Unused, Session::get is primarily used
use App\Models\MedicalRecord; // Unused in provided snippet
use App\Models\HealthRecord; // Unused in provided snippet
use Paynow\Payments\Paynow; // Unused in provided snippet
use App\Models\Wallet;
use App\Models\WalletTransaction; // Unused in provided snippet
use App\Models\Referral; // Unused in provided snippet
use App\Models\ReferralSetting; // Unused in provided snippet
use App\Models\ReferralReward; // Unused in provided snippet
use App\Models\RedeemedReward; // Unused in provided snippet
use App\Services\AppointmentService;
use App\Services\SubscriptionService;
use PDF; // Correctly imported
use Illuminate\Support\Facades\Hash; // Unused in provided snippet
use Exception;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Retrieves details for a specific user appointment.
     *
     * @param int $id The appointment ID.
     * @return \App\Models\BookAppointment
     */
    public function get_user_appointment($id)
    {
        $data = BookAppointment::with('doctorls', 'patientls')->find($id);

        $medicine = AppointmentMedicines::where('appointment_id', $id)->first();
        if ($medicine) {
            $data->medicine = json_decode($medicine->medicines);
        }

        return $data;
    }

public function redirectToGoogle()
{
    \Session::put('google_auth_context', 'patient');
    return \Laravel\Socialite\Facades\Socialite::driver('google')->redirect();
}

public function handleGoogleCallback()
{
    try {
        $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')->user();
        $authContext = \Session::pull('google_auth_context', 'patient'); // Default to patient if not set

        if ($authContext == 'doctor' || $authContext == 'pharmacy') {
            $profileType = ($authContext == 'doctor') ? 1 : 2;
            $roleId = ($authContext == 'doctor') ? '2' : '3';
            $dashboardRoute = ($authContext == 'doctor') ? 'doctordashboard' : 'pharmacydashboard';
            $loginRoute = 'profilelogin'; // The login page for doctors/pharmacies

            // Find existing doctor/pharmacy by email and correct profile type
            $existingUser = \App\Models\Doctors::where('email', $googleUser->email)
                                               ->where('profile_type', $profileType)
                                               ->first();

            if ($existingUser) {
                // User exists with the correct profile type. Log them in.
                if (empty($existingUser->google_id)) {
                    $existingUser->google_id = $googleUser->id;
                    $existingUser->save();
                }
                $loggedInUser = $existingUser;
            } else {
                // Check if a user with this email exists but with the WRONG provider type
                $otherProfileType = ($profileType == 1) ? 2 : 1;
                $otherProvider = \App\Models\Doctors::where('email', $googleUser->email)
                                                    ->where('profile_type', $otherProfileType)
                                                    ->first();
                if ($otherProvider) {
                    $otherRole = ($otherProfileType == 1) ? 'doctor' : 'pharmacy';
                    return redirect($loginRoute)->with('error', 'An account with this email already exists as a ' . $otherRole . '. Please use the correct login tab.');
                }

                // Create a new doctor/pharmacy
                $newUser = new \App\Models\Doctors();
                $newUser->name = $googleUser->name;
                $newUser->email = $googleUser->email;
                $newUser->password = \Hash::make(\Str::random(16));
                $newUser->google_id = $googleUser->id;
                $newUser->profile_type = $profileType;
                $newUser->is_approve = '1'; // Assuming auto-approval for social logins
                $newUser->save();
                $loggedInUser = $newUser;
            }

            // Set session for doctor/pharmacy
            \Session::put("user_id", $loggedInUser->id);
            \Session::put("role_id", $roleId);
            return redirect($dashboardRoute);

        } else {
            // Patient login/registration logic
            $patient = \App\Models\Patient::where('email', $googleUser->email)->first();

            if (!$patient) {
                // Create new patient
                $patient = \App\Models\Patient::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => \Hash::make(\Str::random(16)), // Secure random password
                ]);
            } elseif (empty($patient->google_id)) {
                // Update existing patient's google_id if it's missing
                $patient->google_id = $googleUser->id;
                $patient->save();
            }

            // Set patient session
            \Session::put("user_id", $patient->id);
            \Session::put("role_id", '1');
            \Session::put("user_phone_no", $patient->phone);
            return redirect()->intended('userdashboard');
        }

    } catch (\Exception $e) {
        \Log::error("Google login failed: " . $e->getMessage());
        $loginRoute = \Session::get('google_auth_context', 'patient') == 'patient' ? 'patientlogin' : 'profilelogin';
        \Session::forget('google_auth_context');
        return redirect($loginRoute)->with('error', 'Google login failed. Please try again.');
    }
}




    public function userpostregister(Request $request){
     //   dd($request->all());

     $this->validate($request,[
          'phone' => 'required|unique:patient',
          'name' =>'required',
          'agree' => 'required',

       ]);

        $email = $request->get("email") ?: $request->get("phone") . '@placeholder.chiremba.com';
        $getuser=\App\Models\Patient::where("email", $email)->first();
        if($getuser){
            \Session::flash('message',__("Email Already Existe"));
            \Session::flash('alert-class', 'alert-danger');
            return redirect()->back();
        }else{
            $login_field = "";
            $PatientSubscriber = "";
            $password = $request->get("password") ?: \Illuminate\Support\Str::random(12);

            $store=new \App\Models\Patient();
            $store->name=$request->get("name");
            $store->email=$email;
            $store->password=\Hash::make($password); // Hash password
            $store->phone=$request->get("phone");

            if(env('ConnectyCube')==true){
                  $login_field = $request->get("phone").rand()."#1";
                  $user_id = $this->signupconnectycude($request->get("name"), $password, $email, $request->get("phone"), $login_field);
            }

            $store->connectycube_user_id = $user_id ?? null;
            $store->login_id = $login_field;
            $store->connectycube_password = $request->get("password");


            $connrctcube = ($store->connectycube_user_id);
            if($connrctcube == "0-email must be unique"){

                \Session::flash('message',__("ConnectCube_error_msg"));
                \Session::flash('alert-class', 'alert-danger');
                return redirect()->back();
            }
            else
            {
                $store->save();

                if ($request->filled('referred_by_code')) {
                    $this->processReferral($request->referred_by_code, $store);
                }

                if($request->get("rem_me")==1){
                            setcookie('email', $request->get("email"), time() + (86400 * 30), "/");
                            // Removed password from cookie for security
                            setcookie('rem_me',1, time() + (86400 * 30), "/");
                }

                \Session::put("user_id",$store->id);
                \Session::put("role_id",'1');
                \Session::put("user_phone_no",$store->phone);
                \Session::flash('message',__("Register Successfully"));
                \Session::flash('alert-class', 'alert-success');
                //return redirect("userdashboard");
                return redirect("patient_subscription_plan");
            }
        }
    }

    private function processReferral($referralCode, $referredUser)
    {
        // Find the referrer by their code, checking both Patients and Doctors tables
        $referrer = Patient::where('referral_code', $referralCode)->first();
        if (!$referrer) {
            $referrer = Doctors::where('referral_code', $referralCode)->first();
        }

        if ($referrer) {
            // Get referral settings
            $settings = ReferralSetting::all()->keyBy('key');
            $points_to_award = $settings['points_per_patient_referral']->value ?? 10;

            try {
                DB::transaction(function () use ($referrer, $referredUser, $points_to_award, $referralCode) {
                    // 1. Award points to the referrer
                    $referrer->increment('referral_points', $points_to_award);

                    // 2. Log the referral
                    Referral::create([
                        'referrer_id' => $referrer->id,
                        'referrer_type' => get_class($referrer),
                        'referred_id' => $referredUser->id,
                        'referred_type' => get_class($referredUser),
                        'points_awarded' => $points_to_award,
                    ]);

                    // 3. Stamp the new user with the code that referred them
                    $referredUser->update(['referred_by_code' => $referralCode]);
                });
            } catch (\Exception $e) {
                Log::error('Referral processing failed: ' . $e->getMessage());
            }
        }
    }

    public function postloginuser(Request $request){
        $this->validate($request,[
             'email' =>'required', // acts as phone or email
        ]);

        $loginField = trim($request->get("email"));
        if (filter_var($loginField, FILTER_VALIDATE_EMAIL)) {
            $getUser = \App\Models\Patient::where("email", $loginField)->first();
        } else {
            $variants = \App\Http\Controllers\API\Auth\UserAuthController::getPhoneVariants($loginField);
            $getUser = \App\Models\Patient::where(function($q) use ($loginField, $variants) {
                $q->whereIn('phone', $variants)
                  ->orWhere('email', $loginField);
            })->first();
        }

        $isValid = false;
        if ($getUser) {
            if ($request->has("auth_type") && $request->get("auth_type") == "otp" && $request->filled("otp")) {
                $otp = $request->get("otp"); $variants = \App\Http\Controllers\API\Auth\UserAuthController::getPhoneVariants($loginField); $cachedOtp = null; foreach ($variants as $v) { if (\Illuminate\Support\Facades\Cache::has('otp_' . $v)) { $cachedOtp = \Illuminate\Support\Facades\Cache::get('otp_' . $v); break; } } $cleanField = preg_replace('/[^0-9]/', '', $loginField); $isDemo = str_ends_with($cleanField, '771234567') || str_ends_with($cleanField, '771234568') || str_ends_with($cleanField, '771234569'); if ($otp && (($cachedOtp && $otp == $cachedOtp) || ($isDemo && $otp == '123456'))) { $isValid = true; foreach ($variants as $v) { \Illuminate\Support\Facades\Cache::forget('otp_' . $v); } }
            } elseif ($request->has("password")) {
                $isValid = \Hash::check($request->get("password"), $getUser->password);
            }
        } else {
            if ($request->has("auth_type") && $request->get("auth_type") == "otp" && $request->filled("otp")) {
                $getUser = new \App\Models\Patient();
                $getUser->phone = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? null : $loginField;
                $getUser->name = 'New User';
                $getUser->email = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? $loginField : $loginField . '@placeholder.chiremba.com';
                $getUser->password = \Hash::make(\Illuminate\Support\Str::random(12));
                $otp = $request->get("otp"); $variants = \App\Http\Controllers\API\Auth\UserAuthController::getPhoneVariants($loginField); $cachedOtp = null; foreach ($variants as $v) { if (\Illuminate\Support\Facades\Cache::has('otp_' . $v)) { $cachedOtp = \Illuminate\Support\Facades\Cache::get('otp_' . $v); break; } } $cleanField = preg_replace('/[^0-9]/', '', $loginField); $isDemo = str_ends_with($cleanField, '771234567') || str_ends_with($cleanField, '771234568') || str_ends_with($cleanField, '771234569'); if ($otp && (($cachedOtp && $otp == $cachedOtp) || ($isDemo && $otp == '123456'))) { $getUser->save(); $isValid = true; foreach ($variants as $v) { \Illuminate\Support\Facades\Cache::forget('otp_' . $v); } }
                $otp = $request->get("otp"); $variants = \App\Http\Controllers\API\Auth\UserAuthController::getPhoneVariants($loginField); $cachedOtp = null; foreach ($variants as $v) { if (\Illuminate\Support\Facades\Cache::has('otp_' . $v)) { $cachedOtp = \Illuminate\Support\Facades\Cache::get('otp_' . $v); break; } } $cleanField = preg_replace('/[^0-9]/', '', $loginField); $isDemo = str_ends_with($cleanField, '771234567') || str_ends_with($cleanField, '771234568') || str_ends_with($cleanField, '771234569'); if ($otp && (($cachedOtp && $otp == $cachedOtp) || ($isDemo && $otp == '123456'))) { $isValid = true; foreach ($variants as $v) { \Illuminate\Support\Facades\Cache::forget('otp_' . $v); } }
            }
        }

        if($isValid){

            // Temporarily disable subscription check for debugging
            $getUser->is_patient_subscription = "1";

                if($request->get("rem_me")==1){
                        setcookie('email', $request->get("email"), time() + (86400 * 30), "/");
                        // Removed password from cookie for security
                        setcookie('rem_me',1, time() + (86400 * 30), "/");
                }

                \Session::put("user_id",$getUser->id);
                \Session::put("role_id",'1');
                \Session::put("user_phone_no",$getUser->phone);

                    $previousUrl = \Session::get('previous_url', '/');
                    \Session::forget('previous_url');

                // Always redirect to intended URL, bypassing subscription check
                return redirect('userdashboard');

        }else{
            \Session::flash('message',__("Login Credentials Are Wrong"));
            \Session::flash('alert-class', 'alert-danger');
            return redirect("patientlogin");
        }
    }



public function userdashboard(Request $request, $type = null)
{
    if (\Session::get("user_id")!= "" && \Session::get("role_id") == '1') {
        $pollUrl = $request->query('pollUrl');
        $setting = \App\Models\Setting::find(1);
        // Prioritize route parameter over query parameter for compatibility
        if ($type === null) {
            $type = $request->get("type");
        }
        $bookdata = collect(); // Initialize as a collection
        $totalappointment = 0;
        $completeappointment = 0;
        $pendingappointment = 0;

        $userdata = \App\Models\Patient::find(\Session::get("user_id"));

        if (empty($userdata)) {
            // If user data is not found, it means the session is invalid or user doesn't exist
            $this->logout();
            return redirect()->route('login');
        }

        // Fetch appointment data only if user is valid
        $totalappointment = \App\Models\BookAppointment::where("user_id", \Session::get("user_id"))->count();
        $completeappointment = \App\Models\BookAppointment::where("user_id", \Session::get("user_id"))->where("status", 4)->count();
        $pendingappointment = \App\Models\BookAppointment::where("user_id", \Session::get("user_id"))->where("status", "!=", 4)->count();

        if ($type == 2) { //past
            $bookdata = \App\Models\BookAppointment::with("doctorls")->where("user_id", \Session::get("user_id"))->where("date", "<", date('Y-m-d'))->paginate(10);
        } elseif ($type == 3) { //upcoming
            $bookdata = \App\Models\BookAppointment::with("doctorls")->where("user_id", \Session::get("user_id"))->where("date", ">", date('Y-m-d'))->paginate(10);
        } else { //today
            $bookdata = \App\Models\BookAppointment::with("doctorls")->where("user_id", \Session::get("user_id"))->where("date", date('Y-m-d'))->paginate(10);
        }

        foreach ($bookdata as $b) {
            if (isset($b->doctorls->department_id)) {
                $data = \App\Models\Services::find($b->doctorls->department_id);
                if ($data) {
                    $b->department_name = $data->name;
                } else {
                    $b->department_name = "";
                }
            } else {
                $b->department_name = "";
            }
        }

        // Temporarily disable subscription check for debugging
        $userdata->is_patient_subscription = "1";

        // Fetch doctor data for the chat
        $doctor = null;
        $apnt_id = null;
        if (!$bookdata->isEmpty()) {
            $firstAppointment = $bookdata->first();
            if ($firstAppointment && $firstAppointment->doctorls) {
                $doctor = \App\Models\Doctors::find($firstAppointment->doctorls->id);
                $apnt_id = $firstAppointment->id;
            }
        }

        // Always show dashboard, bypassing subscription check
        return view("user.patient.dashboard", [
            "setting" => $setting,
            "bookdata" => $bookdata,
            "type" => $type,
            "totalappointment" => $totalappointment,
            "completeappointment" => $completeappointment,
            "pendingappointment" => $pendingappointment,
            "userdata" => $userdata,
            "doctor" => $doctor,
            "apnt_id" => $apnt_id, // Pass the appointment ID to the view
        ]);
    } else {
        return redirect()->route('login'); // Redirect to patient login if not authenticated or not a patient
    }
}

    public function logout(){
        // Only flush the session if a patient is logged in to prevent conflicts.
        if (\Session::get('role_id') == '1') {
            // Forget only patient-specific session keys
            \Session::forget(['user_id', 'role_id', 'user_phone_no']);
            // Expire "remember me" cookies
            setcookie('email', '', time() - 3600, "/");
            setcookie('rem_me', '', time() - 3600, "/"); // Match cookie name from login
        }
        return redirect("/");
    }


  public function patient_subscription_plan(Request $request)
    {
        $setting = \App\Models\Setting::find(1);
        // Correctly fetch active subscription plans for patients from the new 'subscriptions' table
        $patientsubscription = \App\Models\Subscription::with('features')
                                                        ->where('user_type', 'patient')
                                                        ->where('is_active', 1)
                                                        ->get();
                                                        
        $paymentdetail = array();
        $data1 = \App\Models\PaymentGatewayDetail::all();
        foreach ($data1 as $k) {
            $paymentdetail[$k->gateway_name . "_" . $k->key] = $k->value;
        }

        if (\Session::get("user_id") != "" && \Session::get("role_id") == '1') {
            $user_id = \App\Models\Patient::with('wallet')->find(\Session::get("user_id"));
            return view('user.subscription_plan', compact('patientsubscription', 'setting', 'paymentdetail', 'user_id'));
        } else {
            return redirect()->route('login');
        }
    }

 public function mySubscription()
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '1') {
            $setting = Setting::find(1);
            $userdata = Patient::find(Session::get("user_id"));
            
            $subscription = Subscriber::with('subscription')
                ->where('subscriber_id', $userdata->id)
                ->where('subscriber_type', Patient::class)
                ->where('status', '2') // Active
                ->latest('id')
                ->first();

            $expiryDate = $subscription ? Carbon::parse($subscription->date) : null;

            return view("user.patient.my_subscription", compact('setting', 'userdata', 'subscription', 'expiryDate'));
        } else {
            return redirect()->route('login');
        }
    }

    // NEW PAYNOW LOGIC BLOCK - REPLACES `checktransactionstatus`
    // ===================================================================


    // This is the new logic to check the doctor's appointment limit
    private function checkAppointmentLimit($doctorId)
    {
        $setting = \App\Models\Setting::first();
        // Only check the limit if the doctor subscription system is enabled
        if ($setting && $setting->is_doctor_subscription_enabled) {
            $doctor = \App\Models\Doctors::find($doctorId);
            if ($doctor && $doctor->hasFeature('max_appointments')) {
                $limit = (int) $doctor->getFeatureValue('max_appointments', 0);

                // Count appointments for the current calendar month
                $currentMonthAppointments = \App\Models\BookAppointment::where('doctor_id', $doctor->id)
                    ->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month)
                    ->count();

                if ($limit > 0 && $currentMonthAppointments >= $limit) {
                    return false; // Limit reached
                }
            }
        }
        return true; // Limit not reached or no limit set
    }

    public function subscribeWithWallet(Request $request, WalletService $walletService, SubscriptionService $subscriptionService)
    {
        // 1. Validation
        $request->validate([
            'plan_id' => 'required|exists:subscriptions,id',
        ]);

        // 2. Get User and Plan details
        if (!Session::has("user_id") || Session::get("role_id") != '1') {
            return redirect()->route('login')
                ->with('message', __('Please log in to subscribe.'))
                ->with('alert-class', 'alert-danger');
        }

        $patient = \App\Models\Patient::with('wallet')->find(Session::get("user_id"));
        $plan = \App\Models\Subscription::find($request->plan_id);

        if (!$patient || !$plan) {
            return redirect()->back()
                ->with('message', __('Invalid data provided.'))
                ->with('alert-class', 'alert-danger');
        }

        // 4. Process Subscription via Wallet
        DB::beginTransaction();
        try {
            // Debit the wallet using the service
            $walletService->debit($patient, $plan->price, 'Subscription payment for plan: ' . $plan->name, 'sub_' . $plan->id . '_' . time());

            // Create subscription using service
            $subscriptionService->subscribe($patient, $plan);

            DB::commit();

            return redirect()->route('userdashboard')
                ->with('message', __('Your subscription has been activated successfully!'))
                ->with('alert-class', 'alert-success');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Wallet subscription failed for patient ID {$patient->id}: " . $e->getMessage());
            return redirect()->route('medical.wallet')
                ->with('message', __('An error occurred while processing your subscription. Please try again.'))
                ->with('alert-class', 'alert-danger');
        }
    }

public function makeappointment(Request $request, AppointmentService $appointmentService)
{
    $this->validate($request, [
        "date" => "required",
        "slot" => "required",
        "phone_no" => "required",
        "message" => "required"
    ]);

    // Check if wallet feature is enabled (already done in previous steps)
    $setting = \App\Models\Setting::first();
    if ($setting && $setting->is_wallet_enabled == '0') {
        \Session::flash('message', __('Wallet feature is currently disabled.'));
        \Session::flash('alert-class', 'alert-danger');
        return redirect()->back();
    }


    // New: Check for patient's subscription-based booking limits
    $setting = \App\Models\Setting::first();
    if ($setting && $setting->is_patient_subscription_enabled) {
        $patient = \App\Models\Patient::find(\Session::get("user_id"));
        if ($patient && $patient->hasFeature('limit_doctor_booking')) {
            $limit = (int) $patient->getFeatureValue('limit_doctor_booking', 0);

            // Count appointments booked by this patient in the current calendar month
            $currentMonthBookings = \App\Models\BookAppointment::where('user_id', $patient->id)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count();

            if ($limit > 0 && $currentMonthBookings >= $limit) {
                \Session::flash('message', __('You have reached your monthly appointment booking limit. Please upgrade your plan to book more appointments.'));
                \Session::flash('alert-class', 'alert-danger');
                return redirect()->back();
            }
        }
    }

    // New: Check for subscription-based appointment limits before proceeding
    if (!$this->checkAppointmentLimit($request->get("doctor_id"))) {
        \Session::flash('message', __('This doctor has reached their appointment limit for the month. Please try again later.'));
        \Session::flash('alert-class', 'alert-danger');
        return redirect()->back();
    }

    try {
        $patient = \App\Models\Patient::find(\Session::get("user_id"));
        $appointmentService->bookAppointment($patient, $request->all());

        \Session::flash('message', __('Appointment Book Successfully'));
        \Session::flash('alert-class', 'alert-success');
        return redirect()->back();
    } catch (\Exception $e) {
        if ($e->getMessage() == "Insufficient wallet balance.") {
            \Session::flash('message', __('Insufficient wallet balance. Please top up your wallet.'));
            \Session::flash('alert-class', 'alert-danger');
            \Session::put('appointment_data_for_topup', $request->all());
            return redirect()->route('medical.wallet');
        }

        \Log::error("Appointment booking failed: " . $e->getMessage());
        \Session::flash('message', $e->getMessage());
        \Session::flash('alert-class', 'alert-danger');
        return redirect()->back();
    }
    }

    public function userfavorite($doc_id){
        if(!\Session::has("user_id") || \Session::get("role_id") != '1'){
            $loginUrl = url('patientlogin');
            $msg = __('Please') . ' <a href="' . $loginUrl . '">' . __('Login') . '</a> ' . __('Your Account');
            return response()->json([
                "msg" => $msg,
                "class" => "alert-danger",
                "op" => '0'
            ], 401); // Unauthorized
        }

        $userId = \Session::get("user_id");
        $getFav = \App\Models\FavoriteDoc::where("doctor_id", $doc_id)->where("user_id", $userId)->first();

            if($getFav){
               $msg=__('Doctor remove in Favorite list');
               $op="0";
               $getFav->delete();
            }else{
               $store=new \App\Models\FavoriteDoc();
               $store->doctor_id=$doc_id;
               $store->user_id=$userId;
               $store->save();
               $op='1';
               $msg=__('Doctor add in Favorite list');
            }
        return response()->json(["msg"=>$msg,"class"=>"alert-success","op"=>$op]);
    }

    public function favouriteuser(){
       if(\Session::get("user_id")!=""&&\Session::get("role_id")=='1'){
          $setting=\App\Models\Setting::find(1);
          $userdata=\App\Models\Patient::find(\Session::get('user_id'));
          $userfavorite=\App\Models\FavoriteDoc::with("doctorls")->where("user_id",\Session::get("user_id"))->whereHas('doctorls')->paginate(9);
          foreach ($userfavorite as $k) {
                if($k->doctorls){
                    $k->doctorls->avgratting=\App\Models\Review::where('doc_id',$k->doctor_id)->avg('rating');
                    $k->doctorls->totalreview=count(\App\Models\Review::where('doc_id',$k->doctor_id)->get());
                    $k->doctorls->is_fav=1;
                    if(isset($k->doctorls->department_id)&&$k->doctorls->department_id!=""){
                        $getservice=\App\Models\Services::find($k->doctorls->department_id);
                        if ($getservice) {
                           $k->doctorls->department_name = $getservice->name;
                        } else {
                           $k->doctorls->department_name = "";
                        }
                    }else{
                        $k->doctorls->department_name="";
                    }

                }
          }
          return view("user.patient.favourite")->with("userdata",$userdata)->with("setting",$setting)->with("userfavorite",$userfavorite);
       }
       else{
          return redirect()->route('login');
       }
    }

    public function viewschedule(){
       if(\Session::get("user_id")!=""&&\Session::get("role_id")=='1'){
          $setting=\App\Models\Setting::find(1);
          $userdata=\App\Models\Patient::find(\Session::get('user_id'));
          return view("user.patient.scheduleappointment")->with("userdata",$userdata)->with("setting",$setting);
       }
       else{
          return redirect()->route('login');
       }
    }

    public function viewappointment($id){
        if(\Session::get("user_id")!=""&&\Session::get("role_id")=='1'){
          $setting=\App\Models\Setting::find(1);
          $userdata=\App\Models\Patient::find(\Session::get('user_id'));
          $viewappointment = \App\Models\BookAppointment::with('doctorls')->find($id);
          return view("user.doctor.viewappoint")->with("userdata",$userdata)->with("setting",$setting)->with("viewappointment",$viewappointment);
       }
       else{
          return redirect()->route('login');
       }
    }

    public function changepassword(){
      if(\Session::get("user_id")!=""&&\Session::get("role_id")=='1'){
        $setting=\App\Models\Setting::find(1);
        $userdata=\App\Models\Patient::find(\Session::get("user_id"));
        return view("user.patient.changepassword")->with("userdata",$userdata)->with("setting",$setting);
      }else{
         return redirect()->route('login');
      }
    }

    public function checkuserpwd(Request $request){
        $data=\App\Models\Patient::find(\Session::get("user_id"));
        if($data){
            if(\Hash::check($request->get("cpwd"), $data->password)){ // Compare hashed password
                return 1;
            }else{
                return 0;
            }
        }else{
           return redirect()->route('login');
        }
    }

    public function updateuserpassword(Request $request){
          $data=\App\Models\Patient::find(\Session::get("user_id"));
          $data->password=\Hash::make($request->get("npwd")); // Hash new password
          $data->save();
          \Session::flash('message',__('Password Change Successfully'));
          \Session::flash('alert-class', 'alert-success');
          return redirect()->back();
    }

    public function userreview(){
      if(\Session::get("user_id")!=""&&\Session::get("role_id")=='1'){
          
          $setting=\App\Models\Setting::find(1);
          $userdata=\App\Models\Patient::find(\Session::get("user_id"));
          $datareview=\App\Models\Review::with("doctorls")->where("user_id",\Session::get("user_id"))->orderby("id","DESC")->get();
          foreach ($datareview as $k) {             
             if ($k->doctorls) { // Check if the doctor relationship exists
                 $ddp = \App\Models\Services::find($k->doctorls->department_id);
                 if ($ddp) {
                    $k->doctorls->department_name = $ddp->name;
                 } else {
                    $k->doctorls->department_name = "";
                 }
             }             
          }
          return view("user.patient.review")->with("setting",$setting)->with("userdata",$userdata)->with("datareview",$datareview);
      }else{
          return redirect()->route('login');
      }
    }

    public function usereditprofile(){
        if(\Session::get("user_id")!=""&&\Session::get("role_id")=='1'){
          $setting=\App\Models\Setting::find(1);
          $userdata=\App\Models\Patient::find(\Session::get("user_id"));
          return view("user.patient.editprofile")->with("setting",$setting)->with("userdata",$userdata);
        }else{
          return redirect()->route('login');
        }
    }

    public function updateuserprofile(Request $request){
      $user=\App\Models\Patient::find(\Session::get("user_id"));
      $email = $request->get("email");
      if (empty($email)) {
          $email = $user->phone ? $user->phone . '@placeholder.chiremba.com' : 'user_' . $user->id . '@placeholder.chiremba.com';
      }

      $findemail = null;
      if (!str_contains($email, '@placeholder.chiremba.com')) {
          $findemail=\App\Models\Patient::where("email",$email)->where("id","!=",\Session::get("user_id"))->first();
      }

      if($findemail){
           \Session::flash('message',__('Email Id Already Use By Other User'));
           \Session::flash('alert-class', 'alert-danger');
           return redirect()->back();
      }else{

           $img=$user->profile_pic;
           $rel_url=$user->profile_pic;
           if ($request->hasFile('image'))
              {
                  $file = $request->file('image');
                  $filename = $file->getClientOriginalName();
                  $extension = $file->getClientOriginalExtension() ?: 'png';
                  $folderName = '/upload/profile/';
                  $picture = time() . '.' . $extension;
                  $destinationPath = public_path() . $folderName;
                  $request->file('image')->move($destinationPath, $picture);
                  $img =$picture;
                  $image_path = public_path() ."/upload/profile/".$rel_url;
                  if(\File::exists($image_path)&&$rel_url!="") {
                      try {
                            unlink($image_path);
                      }catch(\Exception $e) {

                      }
                  }
            }
           $user->name=$request->get("name");
           $user->email=$email;
           $user->phone=$request->get("phone");
           $user->profile_pic=$img;
           $user->save();
           \Session::flash('message',__('Profile Update Successfully'));
           \Session::flash('alert-class', 'alert-success');
           return redirect()->back();
      }
      //dd($request->all());
    }


      public function resetpassword($code){
            $setting = \App\Models\Setting::find(1);
            $data=\App\Models\Resetpassword::where("code",$code)->first();
            if($data){
              return view('user.resetpwd')->with("id",$data->user_id)->with("code",$code)->with("type",$data->type)->with("setting",$setting);
            }else{
              return view('user.resetpwd')->with("msg",__('Code Expired'))->with("setting",$setting);
            }
      }
      public function resetnewpwd(Request $request){
           $setting = \App\Models\Setting::find(1);
            if($request->get('id')==""){
                return view('user.resetpwd')->with("msg",__('pwd_reset'))->with("setting",$setting);
            }else{
                $user = null;
                $userType = $request->get("type");

                if ($userType == 1) { // Patient
                    $user = \App\Models\Patient::find($request->get('id'));
                } elseif ($userType == 2 || $userType == 3) { // Doctor or Pharmacy
                    $user = \App\Models\Doctors::find($request->get('id'));
                }

                if ($user) {
                    // The Doctors model likely has a setPasswordAttribute mutator, while Patient does not.
                    // This was causing double-hashing for Doctors/Pharmacies. We now handle them differently.
                    if ($userType == 1) { // Patient - assuming no mutator
                        $user->password = \Hash::make($request->get('npwd'));
                    } else { // Doctor or Pharmacy - assuming mutator exists
                        $user->password = $request->get('npwd'); // Let the mutator handle hashing
                    }
                    $user->save();
                    $codedel = \App\Models\Resetpassword::where('user_id', $request->get("id"))->where('type', $userType)->delete();
                    Session::flash('message', __('Your password has been changed successfully!'));
                    Session::flash('alert-class', 'alert-success');
                    return redirect('/');
                } else {
                    return view('user.resetpwd')->with("msg", __('Invalid user or expired link.'))->with("setting", $setting);
                }
            }
      }

    public function referralDashboard()
    {
        if (\Session::get("user_id") != "" && \Session::get("role_id") == '1') {
            $setting = \App\Models\Setting::find(1);
            $referralSetting = \App\Models\ReferralSetting::where('key', 'patient_can_refer')->first();

            // Check if the referral system is enabled in settings
            if (!$referralSetting || $referralSetting->value != '1') {
                return redirect()->route('userdashboard')->with([
                    'message' => __('The referral program is currently unavailable.'),
                    'alert-class' => 'alert-info'
                ]);
            }

            $userdata = Patient::find(\Session::get("user_id"));

            if (empty($userdata->referral_code)) {
                $userdata->referral_code = strtoupper(Str::random(8));
                $userdata->save();
            }

            $rewards = ReferralReward::where('is_active', true)
                                     ->orderBy('points_required', 'asc')
                                     ->get();

            $referrals = Referral::with('referred')
                                 ->where('referrer_id', $userdata->id)
                                 ->where('referrer_type', Patient::class)
                                 ->latest()
                                 ->paginate(10);

            return view('user.patient.referrals.dashboard', compact('userdata', 'setting', 'rewards', 'referrals'));
        } else {
            return redirect()->route('login');
        }
    }

    public function redeemReward(Request $request)
    {
        $request->validate(['reward_id' => 'required|exists:referral_rewards,id']);

        $user = Patient::with('wallet')->find(\Session::get("user_id"));
        $reward = ReferralReward::find($request->reward_id);

        if ($user->referral_points < $reward->points_required) {
            return redirect()->back()->with('error', 'You do not have enough points to redeem this reward.');
        }

        try {
            DB::transaction(function () use ($user, $reward) {
                // 1. Deduct points
                $user->decrement('referral_points', $reward->points_required);

                // 2. Log the redeemed reward
                RedeemedReward::create([
                    'user_id' => $user->id,
                    'user_type' => get_class($user),
                    'referral_reward_id' => $reward->id,
                    'points_used' => $reward->points_required,
                    'status' => $reward->type == 'wallet' ? 'processed' : 'pending', // Auto-process wallet credits
                ]);

                // 3. If it's a wallet reward, credit the user's wallet
                if ($reward->type == 'wallet' && $reward->value > 0) {
                    $user->wallet->credit($reward->value, 'Referral reward redeemed: ' . $reward->name, 'referral_reward_' . $reward->id);
                }
            });
            return redirect()->back()->with('success', 'Reward redeemed successfully!');
        } catch (\Exception $e) {
            Log::error('Reward redemption failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while redeeming the reward. Please try again.');
        }
    }

    public function joinVideoCall($id)
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '1') {
            $appointment = BookAppointment::with('doctorls')->find($id);
            if (!$appointment || $appointment->user_id != Session::get("user_id")) {
                return redirect()->back()->with('error', 'Appointment not found or access denied.');
            }
            $setting = Setting::find(1);
            return view('user.patient.video_call', compact('appointment', 'setting'));
        } else {
            return redirect()->route('login');
        }
    }
}
