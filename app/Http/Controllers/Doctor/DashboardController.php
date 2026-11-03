<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use validate;
use Sentinel; // Keep Sentinel for admin login if it's still used there
use DB;
use App\Models\Doctors;
use App\Models\Services;
use App\Models\SlotTiming;
use App\Models\Schedule;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Patient;
use DataTables;
use App\Models\TokenData;
use App\Models\User;
use App\Models\PaymentGatewayDetail;
use Mail;
use App\Models\Subscriber;
use Google_Client;
use App\Models\Subscription;
use DateTime;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Paynow\Payments\Paynow;
use App\Models\Wallet;
use App\Models\Referral;
use App\Models\ReferralSetting;
use Illuminate\Support\Facades\Hash; // Import Hash facade

class DashboardController extends Controller
{
    public function logout(){
        // Only flush the session if a doctor is logged in to prevent conflicts.
        if (\Session::get('role_id') == '2') {
            \Session::forget(['user_id', 'role_id']);
            // Expire "remember me" cookies
            setcookie('email', '', time() - 3600, "/");
            setcookie('rem_me', '', time() - 3600, "/"); // Match cookie name from login
        }
        return redirect("/");
    }

    public function redirectToGoogle()
    {
        \Session::put('google_auth_context', 'doctor');
        return \Laravel\Socialite\Facades\Socialite::driver('google')->redirect();
    }


     public function showdoctors(){
        return view("admin.doctor.default");
     }

         public function doctorstable(Request $request){
          $doctors = \App\Models\Doctors::with('department')->where('profile_type', 1);
 
         // Search filter for name, email, or address
         if ($request->has('search_term') && !empty($request->search_term)) {
             $searchTerm = $request->search_term;
             $doctors->where(function ($query) use ($searchTerm) {
                 $query->where('name', 'like', '%' . $searchTerm . '%')
                       ->orWhere('email', 'like', '%' . $searchTerm . '%')
                       ->orWhere('address', 'like', '%' . $searchTerm . '%');
             });
         }
 
         // Sorting logic
         $sortBy = $request->get('sort_by', 'newest'); // Default to 'newest'
         switch ($sortBy) {
             case 'oldest':
                 $doctors->orderBy('created_at', 'asc');
                 break;
             case 'name_asc':
                 $doctors->orderBy('name', 'asc');
                 break;
             case 'name_desc':
                 $doctors->orderBy('name', 'desc');
                 break;
             case 'newest':
             default:
                 $doctors->orderBy('created_at', 'desc');
                 break;
         }
 
         return DataTables::of($doctors)
            ->editColumn('id', function ($doctors) {
                return $doctors->id;
            })
            ->editColumn('image', function ($doctors) {
                if($doctors->image!=""){
                    return asset("upload/doctors").'/'.$doctors->image;
                }else{
                    return asset("upload/doctors/doctor_default.png");
                }

            })
            ->editColumn('name', function ($doctors) {
                return $doctors->name;
            })
            ->editColumn('email', function ($doctors) {
                return $doctors->email;
            })
            ->editColumn('phone', function ($doctors) {
                return $doctors->phoneno;
            })
            ->editColumn('service', function ($doctors) {

                return isset($doctors->department)?$doctors->department->name:"";
            })
            ->addColumn('status', function ($doctors) {
                if ($doctors->is_approve == '1') {
                    return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100">' . __("Approved") . '</span>';
                } else {
                    return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100">' . __("Pending") . '</span>';
                }
            })
            ->addColumn('recommendable', function ($doctors) {
                $checked = $doctors->recommendable ? 'checked' : '';
                $route = route('admin.doctors.toggle_recommendable', $doctors->id);
                $csrf = csrf_token();
                return '<label class="relative inline-flex items-center cursor-pointer">
                          <input type="checkbox" value="" class="sr-only peer" onchange="toggleRecommendable(\''.$route.'\', \''.$csrf.'\')" '.$checked.'>
                          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[\'\'] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>';
            })
            ->editColumn('action', function ($doctors) {
                $editUrl = route('admin.doctors.save', ['id' => $doctors->id]);
                $timingUrl = route('admin.doctors.timing', ['id' => $doctors->id]);
                $deleteUrl = route('admin.doctors.delete', ['id' => $doctors->id]);

                $actions = '<button type="button" onclick="quickAiEnhanceDoctor(' . $doctors->id . ', this)" title="Auto-enhance Bio with AI" class="inline-flex items-center px-2.5 py-1.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white text-xs font-medium rounded-md shadow-sm transition-all duration-150"><i class="fa-solid fa-wand-magic-sparkles mr-1"></i> AI Bio</button>';
                $actions .= '<a href="' . $editUrl . '" class="inline-flex items-center px-2.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md shadow-sm transition-colors duration-150"><i class="uil uil-edit mr-1"></i> Edit</a>';
                $actions .= '<a href="' . $timingUrl . '" class="inline-flex items-center px-2.5 py-1.5 bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium rounded-md shadow-sm transition-colors duration-150"><i class="uil uil-clock mr-1"></i> Schedule</a>';

                if ($doctors->is_approve == '0') {
                    $approveUrl = route('admin.doctors.approve', ['id' => $doctors->id, 'status' => '1']);
                    $actions .= '<a href="' . $approveUrl . '" class="inline-flex items-center px-2.5 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-md shadow-sm transition-colors duration-150"><i class="uil uil-check-circle mr-1"></i> Approve</a>';
                }

                $actions .= '<a href="javascript:void(0)" onclick="delete_record(\'' . $deleteUrl . '\')" class="inline-flex items-center px-2.5 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-md shadow-sm transition-colors duration-150"><i class="uil uil-trash-alt mr-1"></i> Delete</a>';

                return '<div class="flex items-center justify-end space-x-1.5 whitespace-nowrap">' . $actions . '</div>';
            })
            ->rawColumns(['status', 'action', 'recommendable'])
            ->make(true);
     }



     public function postapprovedoctor($id,$status){
         if(\Session::get("is_demo")=='0'){
                \Session::flash('message',"This Action Disable In Demo");
                \Session::flash('alert-class', 'alert-danger');
                return redirect()->back();
         }else{
                $store=\App\Models\Doctors::find($id);
                $store->is_approve=$status;
                $store->save();
                if($status=='1'){
                    $msg=__('Profile Enable Successfully');
                }else{
                    $msg=__('Profile Disable Successfully');
                }
                \Session::flash('message',$msg);
                \Session::flash('alert-class', 'alert-success');
                return redirect()->back();
         }
     }

    public function toggleRecommendable(Doctors $doctor)
    {
        $doctor->recommendable = !$doctor->recommendable;
        $doctor->save();
        return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
    }

     public function savedoctor($id){
        $data=\App\Models\Doctors::find($id);
        $department=\App\Models\Services::all();
        return view("admin.doctor.savedoctor")->with("id",$id)->with("data",$data)->with("department",$department);
     }

     public function updatedoctor(Request $request){
          if($request->get("id")==0){
            $store=new \App\Models\Doctors();
            $data=\App\Models\Doctors::where("email",$request->get("email"))->first();
            if($data){
                 \Session::flash('message',__("Email Already Existe"));
                 \Session::flash('alert-class', 'alert-danger');
                 return redirect()->back();
            }
            $msg=__("Doctor Add Successfully");
            $img_url="profile.png";
            $rel_url="";
          }else{
            $store=\App\Models\Doctors::find($request->get("id"));
            $msg=__("Doctor Update Successfully");
            $img_url=$store->image;
            $rel_url=$store->image;
          }
            if ($request->hasFile('upload_image'))
              {
                 $file = $request->file('upload_image');
                 $filename = $file->getClientOriginalName();
                 $extension = $file->getClientOriginalExtension() ?: 'png';
                 $folderName = '/upload/doctors/';
                 $picture = time() . '.' . $extension;
                 $destinationPath = public_path() . $folderName;
                 $request->file('upload_image')->move($destinationPath, $picture);
                 $img_url =$picture;
                  $image_path = public_path() ."/upload/doctors/".$rel_url;
                    if(\File::exists($image_path)&&$rel_url!="") {
                        try {
                             \File::delete($image_path);
                        }
                        catch(\Exception $e) {

                        }
                  }
             }
          $store->name=$request->get("name");
          $store->department_id=$request->get("department_id");
          if ($request->filled('password')) {
            $store->password = $request->get("password"); // Let the model's mutator handle hashing
          }
          $store->phoneno=$request->get("phoneno");
          $store->aboutus=$request->get("aboutus");
          $store->services=$request->get("services");
          $store->healthcare=$request->get("healthcare");
          $store->address=$request->get("address");
          $store->lat=$request->get("lat");
          $store->lon=$request->get("lon");
          $store->consultation_fees = $request->get("consultation_fees");
          $store->email=$request->get("email");
          $store->working_time=$request->get("working_time");
          $store->image=$img_url;
          $store->is_approve='1';
          $store->save();

          \Session::flash('message',$msg);
          \Session::flash('alert-class', 'alert-success');
          return redirect("admin/doctors");
     }

     public function savescheduledata(Request $request){

        $arr=$request->get("arr");
        if(!empty($arr)){
           $removedata=\App\Models\Schedule::where("doctor_id",$request->get("doc_id"))->get();
           if(count($removedata)>0){
              foreach ($removedata as $k) {
                  $findslot=\App\Models\SlotTiming::where("schedule_id",$k->id)->delete();
                  $k->delete();
              }
           }

           for($i=0;$i<count($arr);$i++){
               if($arr[$i]['start_time']){
                   for($j=0;$j<count($arr[$i]['start_time']);$j++){
                      if(isset($arr[$i]['start_time'][$j])&&$arr[$i]['start_time'][$j]!=""&&isset($arr[$i]['end_time'][$j])&&$arr[$i]['end_time'][$j]!=""&&isset($arr[$i]['duration'][$j])&&$arr[$i]['duration'][$j]!=""){
                            $getslot=$this->getslotvalue($arr[$i]['start_time'][$j],$arr[$i]['end_time'][$j],$arr[$i]['duration'][$j]);
                            $store=new \App\Models\Schedule();
                            $store->doctor_id=$request->get("doc_id");
                            $store->day_id=$i;
                            $store->start_time=$arr[$i]['start_time'][$j];
                            $store->end_time=$arr[$i]['end_time'][$j];
                            $store->duration=$arr[$i]['duration'][$j];
                            $store->save();
                            foreach ($getslot as $g) {
                                $aslot=new \App\Models\SlotTiming();
                                $aslot->schedule_id=$store->id;
                                $aslot->slot=$g;
                                $aslot->save();
                            }
                      }
                   }
               }
           }

          \Session::flash('message',__("Schedule Save Successfully"));
          \Session::flash('alert-class', 'alert-success');
          return redirect("admin/doctors");
        }
        return redirect("admin/doctors");
     }

     public function deletedoctor($id){
        $doctor=\App\Models\Doctors::find($id);
        if($doctor){
            // Delete from ConnectyCube before deleting locally
            if(env('ConnectyCube') == true && !empty($doctor->connectycube_user_id)){
                $this->deleteconnectycubeuser($doctor->connectycube_user_id);
            }

            $deletsolt=\App\Models\Schedule::with('getslotls')->where("doctor_id",$id)->get();
            foreach ($deletsolt as $de) {
                foreach ($de->getslotls as $k) {
                   $k->delete();
                }
                $de->delete();
            }
            $bookappointment=\App\Models\BookAppointment::where("doctor_id",$id)->delete();
            $review=\App\Models\Review::where("doc_id",$id)->delete();
            $subscriber = \App\Models\Subscriber::where("subscriber_id",$id)->where('subscriber_type', \App\Models\Doctors::class)->delete();
            $image_path = public_path() ."/upload/doctors/".$doctor->image;
                if(\File::exists($image_path)&&$doctor->image!="") {
                    try {
                            \File::delete($image_path);
                        }
                    catch(\Exception $e) {
                    }
                }
            $doctor->delete();
        }
        \Session::flash('message',__("Doctor Delete Successfully"));
        \Session::flash('alert-class', 'alert-success');
        return redirect()->back();
     }

     public function showreviews(){
            return view("admin.doctor.review");
     }

     public function reviewtable(){
          $review =\App\Models\Review::with(['doctorls', 'patientls']);

        return DataTables::of($review)
            ->editColumn('id', function ($review) {
                return $review->id;
            })
            ->editColumn('doctor_name', function ($review) {
                return $review->doctorls->name ?? '';
            })
            ->editColumn('username', function ($review) {
                return $review->patientls->name ?? '';
            })
             ->editColumn('ratting', function ($review) {
                return $review->rating ?? '';
            })
             ->editColumn('comment', function ($review) {
                return $review->description ?? '';
            })
            ->editColumn('action', function ($review) {
                $deleteUrl = route('admin.reviews.delete', ['id' => $review->id]);
                return '<a href="javascript:void(0)" onclick="delete_record(\'' . $deleteUrl . '\')" class="inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">Delete</a>';
            })
            ->rawColumns(['action'])
            ->make(true);
     }

     public function deletereview($id){
        $store=\App\Models\Review::find($id);
        $store->delete();
        \Session::flash('message',__("Review Delete Successfully"));
        \Session::flash('alert-class', 'alert-success');
        return redirect()->back();
     }

        public function doctordashboard(Request $request){
         if(\Session::get("user_id")!=""&&\Session::get("role_id")=='2'){
            $setting=\App\Models\Setting::find(1);
            $totalappointment=count(\App\Models\BookAppointment::where("doctor_id",\Session::get("user_id"))->get());
            $totalreview=count(\App\Models\Review::where("doc_id",\Session::get("user_id"))->get());
            $totalnewappointment=count(\App\Models\BookAppointment::where("doctor_id",\Session::get("user_id"))->where("notify",'1')->get());
            $getnewapp=\App\Models\BookAppointment::where("doctor_id",\Session::get("user_id"))->where("notify",'1')->get();
            foreach ($getnewapp as $k) {
                $k->notify='0';
                $k->save();
            }
            $type=$request->get("type");
            if($type==2){ //past
                $bookdata=\App\Models\BookAppointment::with("patientls")->where("doctor_id",\Session::get("user_id"))->where("date","<",date('Y-m-d'))->paginate(10);
              }elseif($type==3){ //upcoming
                  $bookdata=\App\Models\BookAppointment::with("patientls")->where("doctor_id",\Session::get("user_id"))->where("date",">",date('Y-m-d'))->paginate(10);
              }else{ //today
                  $bookdata=\App\Models\BookAppointment::with("patientls")->where("doctor_id",\Session::get("user_id"))->where("date",date('Y-m-d'))->paginate(10);
              }
            $doctordata=\App\Models\Doctors::with('department')->find(\Session::get("user_id"));

            // Check for an active subscription
            $is_subscribed = false;
            // Corrected the query to use subscriber_id and subscriber_type
            $subscription = \App\Models\Subscriber::where('subscriber_id', \Session::get("user_id"))
                ->where('subscriber_type', \App\Models\Doctors::class)
                ->where('status', 2)
                ->latest('id') // Get the most recent subscription record
                ->first();

            if ($subscription && isset($subscription->subscription)) {
                $startDate = new \DateTime($subscription->date);
                $duration = $subscription->subscription->duration;
                $unit = $subscription->subscription->duration_unit;
                
                // Check if duration and unit are valid before trying to modify the date
                if (is_numeric($duration) && !empty($unit)) {
                    // Clone the start date before modifying to avoid changing the original object
                    $expiryDate = (clone $startDate)->modify('+' . $duration . ' ' . \Str::plural($unit, $duration));
                    if ($expiryDate > new \DateTime("now", $startDate->getTimezone())) {
                        $is_subscribed = true;
                    }
                }
            }

            return view("user.doctor.dashboard")->with("setting", $setting)->with("doctordata", $doctordata)->with("totalappointment", $totalappointment)->with("totalreview", $totalreview)->with("totalnewappointment", $totalnewappointment)->with("type", $type)->with("bookdata", $bookdata)->with('is_subscribed', $is_subscribed);
        } else {
            return redirect()->route('doctor.login'); // Redirect to doctor login if not authenticated or not a doctor
        }
     }


     public function postdoctorregister(Request $request){
        $email = $request->get("email") ?: $request->get("phone") . '@placeholder.chiremba.com';
        $getuser=\App\Models\Doctors::where("email", $email)->orWhere("phoneno", $request->get("phone"))->first();
        if($getuser){
            \Session::flash('message',__("Email or Phone Already Exists"));
            \Session::flash('alert-class', 'alert-danger');
            return redirect()->back();
        }else{
            $password = $request->get("password") ?: \Illuminate\Support\Str::random(12);
            $store=new \App\Models\Doctors();
            $store->name=$request->get("name");
            $store->email=$email;
            $store->password=\Hash::make($password); // Hash password
            $store->phoneno=$request->get("phone");
            $store->profile_type=1;

            if(env('ConnectyCube')==true){
                  $login_field = $request->get("phone").rand()."#2";
                  $user_id = $this->signupconnectycude($request->get("name"),$request->get("password"),$request->get("email"),$request->get("phone"),$login_field);
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
                    $this->processReferral($request->referred_by_code, $store, 'doctor');
                }
                $store->save();
                if($request->get("rem_me")==1){
                        setcookie('email', $request->get("email"), time() + (86400 * 30), "/");
                        // Removed password from cookie for security
                        setcookie('rem_me',1, time() + (86400 * 30), "/");
                }

                \Session::put("user_id",$store->id);
                \Session::put("role_id",'2');
                \Session::flash('message',__("Register Successfully"));
                \Session::flash('alert-class', 'alert-success');
                return redirect("doctor_subscription_plan");
            }
        }
     }

    private function processReferral($referralCode, $referredUser, $referredUserType)
    {
        // Find the referrer by their code, checking both Patients and Doctors tables
        $referrer = Patient::where('referral_code', $referralCode)->first();
        if (!$referrer) {
            $referrer = Doctors::where('referral_code', $referralCode)->first();
        }

        if ($referrer) {
            // Get referral settings
            $settings = ReferralSetting::all()->keyBy('key');
            $points_to_award = 0;

            if ($referredUserType == 'patient') {
                $points_to_award = $settings['points_per_patient_referral']->value ?? 10;
            } elseif ($referredUserType == 'doctor') {
                $points_to_award = $settings['points_per_doctor_referral']->value ?? 50;
            } elseif ($referredUserType == 'pharmacy') {
                $points_to_award = $settings['points_per_pharmacy_referral']->value ?? 50;
            }

            try {
                DB::transaction(function () use ($referrer, $referredUser, $points_to_award, $referralCode) {
                    $referrer->increment('referral_points', $points_to_award);
                    Referral::create([
                        'referrer_id' => $referrer->id,
                        'referrer_type' => get_class($referrer),
                        'referred_id' => $referredUser->id,
                        'referred_type' => get_class($referredUser),
                        'points_awarded' => $points_to_award,
                    ]);
                    $referredUser->update(['referred_by_code' => $referralCode]);
                });
            } catch (\Exception $e) {
                Log::error('Referral processing failed: ' . $e->getMessage());
            }
        }
    }

    public function postlogindoctor(Request $request){
        $loginField = trim($request->get("email")); // email field acts as email or phone
        
        if (filter_var($loginField, FILTER_VALIDATE_EMAIL)) {
            $getUser = \App\Models\Doctors::where("email", $loginField)->where("profile_type", '1')->first();
        } else {
            $variants = \App\Http\Controllers\API\Auth\UserAuthController::getPhoneVariants($loginField);
            $getUser = \App\Models\Doctors::where("profile_type", '1')
                ->where(function($q) use ($loginField, $variants) {
                    $q->whereIn('phoneno', $variants)
                      ->orWhere('email', $loginField);
                })->first();
        }
        
        $setting = \App\Models\Setting::find(1);
        
        $isValid = false;
        if ($getUser) {
            if ($request->has("auth_type") && $request->get("auth_type") == "otp") {
                $otp = $request->get("otp");
                $variants = \App\Http\Controllers\API\Auth\UserAuthController::getPhoneVariants($loginField);
                $cachedOtp = null;
                foreach ($variants as $v) {
                    if (\Illuminate\Support\Facades\Cache::has('otp_' . $v)) {
                        $cachedOtp = \Illuminate\Support\Facades\Cache::get('otp_' . $v);
                        break;
                    }
                }
                $cleanField = preg_replace('/[^0-9]/', '', $loginField);
                $isDemo = str_ends_with($cleanField, '771234567') || str_ends_with($cleanField, '771234568') || str_ends_with($cleanField, '771234569');
                if ($otp && (($cachedOtp && $otp == $cachedOtp) || ($isDemo && $otp == '123456'))) {
                    $isValid = true;
                    foreach ($variants as $v) {
                        \Illuminate\Support\Facades\Cache::forget('otp_' . $v);
                    }
                }
            } elseif ($request->has("password")) {
                $isValid = \Hash::check($request->get("password"), $getUser->password);
            }
        } else {
            if ($request->has("auth_type") && $request->get("auth_type") == "otp") {
                $otp = $request->get("otp");
                $variants = \App\Http\Controllers\API\Auth\UserAuthController::getPhoneVariants($loginField);
                $cachedOtp = null;
                foreach ($variants as $v) {
                    if (\Illuminate\Support\Facades\Cache::has('otp_' . $v)) {
                        $cachedOtp = \Illuminate\Support\Facades\Cache::get('otp_' . $v);
                        break;
                    }
                }
                $cleanField = preg_replace('/[^0-9]/', '', $loginField);
                $isDemo = str_ends_with($cleanField, '771234567') || str_ends_with($cleanField, '771234568') || str_ends_with($cleanField, '771234569');
                if ($otp && (($cachedOtp && $otp == $cachedOtp) || ($isDemo && $otp == '123456'))) {
                    $getUser = new \App\Models\Doctors();
                    $getUser->phoneno = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? null : $loginField;
                    $getUser->name = 'New Provider';
                    $getUser->email = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? $loginField : $loginField . '@placeholder.chiremba.com';
                    $getUser->password = \Illuminate\Support\Str::random(12);
                    $getUser->profile_type = 1;
                    $getUser->is_approve = 0;
                    $getUser->save();
                    $isValid = true;
                    foreach ($variants as $v) {
                        \Illuminate\Support\Facades\Cache::forget('otp_' . $v);
                    }
                }
            }
        }

        if ($isValid) {
            // Removed all subscription and approval checks for redirection after login
            if ($request->get("rem_me") == 1) {
                setcookie('email', $request->get("email"), time() + (86400 * 30), "/");
                // Removed password from cookie for security
                setcookie('rem_me', 1, time() + (86400 * 30), "/");
            }
            \Session::put("user_id", $getUser->id);
            \Session::put("role_id", '2');
            return redirect("doctordashboard"); // Always redirect to dashboard
        } else {
            \Session::flash('message', __("Login Credentials Are Wrong"));
            \Session::flash('alert-class', 'alert-danger');
            return redirect()->route('doctor.login');
        }

    }

    public function doctorchangepassword(){
         if(\Session::get("user_id")!=""&&\Session::get("role_id")=='2'){
            $setting=\App\Models\Setting::find(1);
            $doctordata=\App\Models\Doctors::with('department')->find(\Session::get("user_id"));
            return view("user.doctor.changepassword")->with("setting",$setting)->with("doctordata",$doctordata);
         }else{
            return redirect()->route('doctor.login');
         }
    }

    public function show_doctor_hoilday(){
         if(\Session::get("user_id")!=""&&\Session::get("role_id")=='2'){
            $setting=\App\Models\Setting::find(1);
            $doctorhoilday=\App\Models\Doctor_Hoilday::where("doctor_id",\Session::get("user_id"))->get();
            $doctordata=\App\Models\Doctors::with('department')->find(\Session::get("user_id"));
            return view("user.doctor.doctor_hoilday")->with("setting",$setting)->with("doctordata",$doctordata)->with("doctorhoilday",$doctorhoilday);
         }else{
            return redirect()->route('doctor.login');
         }
    }

    public function checkdoctorpwd(Request $request){
        $data=\App\Models\Doctors::find(\Session::get("user_id"));
        if($data){
            if(\Hash::check($request->get("cpwd"), $data->password)){ // Compare hashed password
                return 1;
            }else{
                return 0;
            }
        }else{
           return redirect()->route('doctor.login');
        }
    }

    public function updatedoctorpassword(Request $request){
          $data=\App\Models\Doctors::find(\Session::get("user_id"));
          $data->password = $request->get("npwd"); // Let the model's mutator handle hashing
          $data->save();
          \Session::flash('message',__("Password Change Successfully"));
          \Session::flash('alert-class', 'alert-success');
          return redirect()->back();
    }

    public function doctoreditprofile(){
         if(\Session::get("user_id")!=""&&\Session::get("role_id")=='2'){
            $setting=\App\Models\Setting::find(1);
            $department=\App\Models\Services::all();
            $doctordata=\App\Models\Doctors::with('department')->find(\Session::get("user_id"));
            return view("user.doctor.editprofile")->with("setting",$setting)->with("doctordata",$doctordata)->with("department",$department);
         }else{
            return redirect()->route('doctor.login');
         }
    }

    public function updatedoctorsideprofile(Request $request){
          $store=\App\Models\Doctors::find(\Session::get("user_id"));
          $email = $request->get("email");
          if (empty($email)) {
              $email = $store->phoneno ? $store->phoneno . '@placeholder.chiremba.com' : 'provider_' . $store->id . '@placeholder.chiremba.com';
          }

          $doctoremail = null;
          if (!str_contains($email, '@placeholder.chiremba.com')) {
              $doctoremail=\App\Models\Doctors::where("email",$email)->where("id","!=",\Session::get("user_id"))->first();
          }

          if($doctoremail){
                 \Session::flash('message',__("Email Already Existe"));
                 \Session::flash('alert-class', 'alert-danger');
                 return redirect()->back();
          }else{
             $msg=__("Doctor Update Successfully");
             $img_url=$store->image;
             $rel_url=$store->image;
          }
            if ($request->hasFile('upload_image'))
              {
                 $file = $request->file('upload_image');
                 $filename = $file->getClientOriginalName();
                 $extension = $file->getClientOriginalExtension() ?: 'png';
                 $folderName = '/upload/doctors/';
                 $picture = time() . '.' . $extension;
                 $destinationPath = public_path() . $folderName;
                 $request->file('upload_image')->move($destinationPath, $picture);
                 $img_url =$picture;
                  $image_path = public_path() ."/upload/doctors/".$rel_url;
                    if(\File::exists($image_path)&&$rel_url!="") {
                        try {
                             \File::delete($image_path);
                        }
                        catch(\Exception $e) {

                        }
                  }
             }
          $store->name=$request->get("name");
          $store->department_id=$request->get("department_id");
          $store->phoneno=$request->get("phoneno");
          $store->aboutus=$request->get("aboutus");
          $store->services=$request->get("services");
          $store->healthcare=$request->get("healthcare");
          $store->address=$request->get("address");
          $store->lat=$request->get("lat");
          $store->lon=$request->get("lon");
          $store->facebook_url=$request->get("facebook_url");
          $store->twitter_url=$request->get("twitter_url");
          $store->email=$email;
          $store->working_time=$request->get("working_time");
          $store->consultation_fees = $request->get("consultation_fees");
          $store->ecocash_number = $request->get("ecocash_number");
          $store->ecocash_name = $request->get("ecocash_name");
          $store->bank_name = $request->get("bank_name");
          $store->bank_account_number = $request->get("bank_account_number");
          $store->bank_account_name = $request->get("bank_account_name");
          $store->innbucks_number = $request->get("innbucks_number");
          $store->image=$img_url;
          $store->save();

          if ($request->has('medical_aids')) {
              $store->medicalAids()->sync($request->get('medical_aids'));
          } else {
              $store->medicalAids()->detach();
          }

          \Session::flash('message',$msg);
          \Session::flash('alert-class', 'alert-success');
          return redirect()->back();
    }

    public function doctorreview(){
        if(\Session::get("user_id")!=""&&\Session::get("role_id")=='2'){
            $setting=\App\Models\Setting::find(1);
            $doctordata=\App\Models\Doctors::find(\Session::get("user_id"));
            $reviewdata=\App\Models\Review::with('patientls')->where("doc_id",\Session::get("user_id"))->get();
            return view("user.doctor.review")->with("setting",$setting)->with("doctordata",$doctordata)->with("reviewdata",$reviewdata);
        }else{
            return redirect()->route('doctor.login');
        }
    }

    public function doctorappointment(Request $request){
         if(\Session::get("user_id")!=""&&\Session::get("role_id")=='2'){
            $setting=\App\Models\Setting::find(1);
            $doctordata=\App\Models\Doctors::find(\Session::get("user_id"));
            
            $query = \App\Models\BookAppointment::with('patientls')->where("doctor_id", \Session::get("user_id"));

            if ($request->has('status') && $request->status !== null && $request->status !== '') {
                $query->where('status', $request->status);
            }

            $appointmentdata = $query->orderby("id", "DESC")->paginate(15);

            $medi = \App\Models\Medicines::get();

            return view("user.doctor.appointmentlist")
                        ->with("setting", $setting)
                        ->with("doctordata", $doctordata)
                        ->with("appointmentdata", $appointmentdata)
                        ->with("medi", $medi);
        } else {
            return redirect()->route('doctor.login');
        }
    }


    public function doctortimingfront(Request $request){
        if(\Session::get("user_id")!=""&&\Session::get("role_id")=='2'){
            $setting=\App\Models\Setting::find(1);
            $doctordata=\App\Models\Doctors::find(\Session::get("user_id"));
            $datats=\App\Models\Schedule::with('getslotls')->where("doctor_id",\Session::get("user_id"))->get();
            foreach ($datats as $k) {
               $k->options=$this->getdurationoption($k->start_time,$k->end_time,$k->duration);
            }
            return view("user.doctor.doctortiming")->with("setting",$setting)->with("doctordata",$doctordata)->with("data",$datats);
        }else{
            return redirect()->route('doctor.login');
         }
    }

    public function paymenthistory(){
         if(\Session::get("user_id")!=""&&\Session::get("role_id")=='2'){
            $setting=\App\Models\Setting::find(1);
            $data = \App\Models\Complement_settlement::where("doctor_id",\Session::get("user_id"))->get();
            $doctordata=\App\Models\Doctors::with('department')->find(\Session::get("user_id"));
            return view("user.doctor.paymenthistory")->with("setting",$setting)->with("data",$data)->with("doctordata",$doctordata);
        }else{
            return redirect()->route('doctor.login');
         }
    }

     public function updatedoctortiming(Request $request){
       $arr=$request->get("arr");

       // New: Check for subscription-based time slot limits
       $setting = \App\Models\Setting::first();
        if ($setting && $setting->is_doctor_subscription_enabled) {
            $doctor = \App\Models\Doctors::find($request->get("doctor_id"));
            if ($doctor && $doctor->hasFeature('max_time_slots')) {
                $limit = (int) $doctor->getFeatureValue('max_time_slots', 0);
                $incomingSlotCount = 0;

                if (!empty($arr) && $limit > 0) {
                    foreach ($arr as $day) {
                        if (!empty($day['start_time'])) {
                            // Use array_values to reset keys after potential filtering
                            $start_times = array_values($day['start_time']);
                            $end_times = array_values($day['end_time']);
                            $durations = array_values($day['duration']);

                            foreach ($start_times as $key => $start) {
                                if (!empty($start) && !empty($end_times[$key]) && !empty($durations[$key])) {
                                    try {
                                        $startTimeObj = new DateTime($start);
                                        $endTimeObj = new DateTime($end_times[$key]);
                                        $duration = (int)$durations[$key];
                                        if ($duration > 0 && $endTimeObj > $startTimeObj) {
                                            $minutes = ($endTimeObj->getTimestamp() - $startTimeObj->getTimestamp()) / 60;
                                            $incomingSlotCount += floor($minutes / $duration);
                                        }
                                    } catch (\Exception $e) { /* Ignore invalid time formats */ }
                                }
                            }
                        }
                    }
                    if ($incomingSlotCount > $limit) {
                        return redirect()->back()->with('message', __('You have exceeded the maximum number of time slots allowed by your subscription plan (Limit: ' . $limit . '). Please adjust your schedule.'))->with('alert-class', 'alert-danger');
                    }
                }
            }
        }

        if(!empty($arr)){
           $removedata=\App\Models\Schedule::where("doctor_id",$request->get("doctor_id"))->get();
           if(count($removedata)>0){
              foreach ($removedata as $k) {
                  $findslot=\App\Models\SlotTiming::where("schedule_id",$k->id)->delete();
                  $k->delete();
              }
           }

           for($i=0;$i<count($arr);$i++){
               if($arr[$i]['start_time']){
                    $start_date = array_values($arr[$i]['start_time']);
                    $end_date = array_values($arr[$i]['end_time']);
                    $duration= array_values($arr[$i]['duration']);

                   for($j=0;$j<count($start_date);$j++){
                      if(isset($start_date[$j])&&$start_date[$j]!=""&&isset($end_date[$j])&&$end_date[$j]!=""&&isset($duration[$j])&&$duration[$j]!=""){
                            $getslot=$this->getslotvalue($start_date[$j],$end_date[$j],$duration[$j]);

                            $store=new \App\Models\Schedule();
                            $store->doctor_id=$request->get("doctor_id");
                            $store->day_id=$i;
                            $store->start_time=$start_date[$j];
                            $store->end_time=$end_date[$j];
                            $store->duration=$duration[$j];
                            $store->save();
                            foreach ($getslot as $g) {
                                $aslot=new \App\Models\SlotTiming();
                                $aslot->schedule_id=$store->id;
                                $aslot->slot=$g;
                                $aslot->save();
                            }
                      }
                   }
               }
           }


          \Session::flash('message',__("Schedule Save Successfully"));
          \Session::flash('alert-class', 'alert-success');
          return redirect()->back();
        }
        return redirect()->back();
    }


    public function changeappointmentdoctor($status, $id, AppointmentService $appointmentService){
        try {
            $appointment = $appointmentService->updateStatus($id, $status);
            
            // Send email (optional, as notification service handles push)
            try {
                $user = $appointment->patientls;
                // Reconstruct message for email if needed, or rely on NotificationService
                $msg = __("Your appointment status has been updated.");
                $user->msg = $msg;
                \Mail::send('email.Ordermsg', ['user' => $user], function($message) use ($user){
                    $message->to($user->email,$user->name)->subject(__('KumbaCare'));
                });
            } catch (\Exception $e) {}

            \Session::flash('message', __('Appointment status updated successfully.'));
            \Session::flash('alert-class', 'alert-success');
            return redirect()->back();
        } catch (\Exception $e) {
            \Session::flash('message', $e->getMessage());
            \Session::flash('alert-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function doctor_subscription_plan(Request $request)
    {
        // Check if the user is a logged-in doctor
        if (!Session::has("user_id") || Session::get("role_id") != '2') {
            return redirect()->route('doctor.login');
        }

        $setting = \App\Models\Setting::find(1);
        
        // Fetch only active subscription plans for doctors, along with their features
        $subscription = \App\Models\Subscription::with('features')
            ->where('is_active', 1)
            ->where('user_type', 'doctor') // Only show plans specifically for doctors
            ->get();

        // Get the authenticated doctor with their wallet information
        $doctordata = \App\Models\Doctors::with('wallet')->find(Session::get("user_id"));

        // The payment modal and external gateways are no longer needed for this view.
        // We will only use the wallet.
        
        return view('user.doctor.subscription_plan', compact('subscription', 'setting', 'doctordata'));
    }

    public function getslotvalue($start_time, $end_time, $duration)
    {
        return app(\App\Http\Controllers\Doctor\ScheduleController::class)->getslotvalue($start_time, $end_time, $duration);
    }

    public function getdurationoption($start_time, $end_time, $duration)
    {
        return app(\App\Http\Controllers\Doctor\ScheduleController::class)->getdurationoption($start_time, $end_time, $duration);
    }

    public function doctortiming($id)
    {
        $data = \App\Models\Schedule::with('getslotls')->where("doctor_id", $id)->get();
        foreach ($data as $k) {
            $k->options = $this->getdurationoption($k->start_time, $k->end_time, $k->duration);
        }
        return view("admin.doctor.schedule", compact("id", "data"));
    }

    public function findpossibletime(Request $request)
    {
        return app(\App\Http\Controllers\Doctor\ScheduleController::class)->findpossibletime($request);
    }

    public function generateslot(Request $request)
    {
        return app(\App\Http\Controllers\Doctor\ScheduleController::class)->generateslotfront($request);
    }

    public function handleGoogleCallback(Request $request)
    {
        return app(\App\Http\Controllers\Patient\DashboardController::class)->handleGoogleCallback($request);
    }
}
