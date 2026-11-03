<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Carbon\Carbon; // Add this line

use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Session;
use Sentinel;
use DB;
use DataTables;
use App\Models\Services;
use App\Models\Doctors;
use App\Models\Setting;
use App\Models\Review;
use App\Models\Newsletter;
use App\Models\Schedule;
use App\Models\BookAppointment;
use App\Models\SlotTiming;
use App\Models\FavoriteDoc;
use App\Models\Contact;
use App\Models\Patient;
use App\Models\Resetpassword;
use App\Models\About;
use App\Models\Privecy; // Note: This class name appears to have a typo and should likely be 'Privacy'.
use App\Models\Doctor_Hoilday;
use App\Models\Code;
use App\Models\PaymentGatewayDetail;
use App\Models\PrivecyApp;
use App\Models\DataDeletion;
use Illuminate\Support\Facades\Hash; // Import Hash facade
use Mail;
use App\Models\Subscription;
use App\Models\Subscriber;
use App\Models\MedicalAidSociety;

class HomeController extends Controller
{
    public function savereview(Request $request){
        $data = new Review();
        $data->user_id = $request->user_id;
        $data->rating = $request->rating3;
        $data->doc_id = $request->doctor_id;
        $data->description = $request->description;
        $data->save();
        return redirect()->back();
    }

    public function showCorporatesPage()
    {
        $setting = Setting::first();
        return view('user.corporates', compact('setting'));
    }

    public function sendCorporateQuote(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'company_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'nullable|string',
        ]);

        Mail::send('emails.corporate_quote', ['data' => $request->all()], function ($message) {
            $message->to('partner@kumbacare.co.zw')->subject('New Corporate Quote Request');
        });

        return redirect()->back()->with('message', __('Thank you for getting in touch!'))->with('alert-class', 'alert-success');
    }

    public function showhome(){
        $doctor= DB::table( 'doctors' )
            ->join( 'review', 'review.doc_id', '=', 'doctors.id' )
            ->groupBy( 'doctors.id' )
            ->select( 'doctors.id', DB::raw( 'AVG( review.rating ) as avgratting' ) )
            ->orderby('id','DESC')
            ->where("is_approve","1")
            ->where("profile_type","1")
            ->take(8)
            ->get();

        // Fetch featured doctors
        $featured_doctors = Doctors::with(['department'])
            ->where('profile_type', 1)
            ->where('featured', 1)
            ->whereNotNull('featured_until')
            ->where('featured_until', '>', Carbon::now())
            ->inRandomOrder()
            ->take(4) // Adjust as needed
            ->get();

        // Fetch featured pharmacies
        $featured_pharmacies = Doctors::with(['department'])
            ->where('profile_type', 2)
            ->where('featured', 1)
            ->whereNotNull('featured_until')
            ->where('featured_until', '>', Carbon::now())
            ->inRandomOrder()
            ->take(4) // Adjust as needed
            ->get();

        $main_array=array();
        foreach ($doctor as $k) {
            $ls=Doctors::find($k->id);
            $ls->avgratting=Review::where('doc_id',$k->id)->avg('rating');
            $ls->totalreview=count(Review::where('doc_id',$k->id)->get());
            if(!empty(Session::get("user_id"))&&Session::get('role_id')=='1'){
                $lsfav=FavoriteDoc::where("doctor_id",$k->id)->where("user_id",Session::get("user_id"))->first();
                if($lsfav){
                    $ls->is_fav=1;
                }else{
                    $ls->is_fav=0;
                }

            }else{
                $ls->is_fav=0;
            }
            $main_array[]=$ls;
        }

        $featured_providers = Doctors::with(['department'])
            ->where('is_approve', '1')
            ->where('featured', 1)
                    ->inRandomOrder() 
            ->take(3)
            ->get();

        foreach ($featured_providers as $provider) {
            $provider->avgratting = Review::where('doc_id', $provider->id)->avg('rating');
            $provider->totalreview = Review::where('doc_id', $provider->id)->count();
            if (!empty(Session::get("user_id")) && Session::get('role_id') == '1') {
                $lsfav = FavoriteDoc::where("doctor_id", $provider->id)->where("user_id", Session::get("user_id"))->first();
                $provider->is_fav = $lsfav ? 1 : 0;
            } else {
                $provider->is_fav = 0;
            }
        }

        $setting = Setting::first();
        if (!$setting) {
            $setting = new Setting();
            $setting->google_tag_manager_id = '';
        }

        $department=Services::take(8)->get();

        return view('user.home')
            ->with("department",$department)
            ->with("doctorlist",$main_array)
            ->with("setting",$setting)
            ->with("featured_providers", $featured_providers) // Keep this if you have a general featured section
            ->with("featured_doctors", $featured_doctors) // New
            ->with("featured_pharmacies", $featured_pharmacies); // New
    }

    public function deposit_payment(Request $request)
    {
        if($request->input('payment_type')==2)
        {

            $id = Session::get('user_id');
            $sub_amout=Subscription::where('id',$request->get("sub_plan"))->first();

            $data=new Subscriber();
            $data->subscriber_id = $id;
            $data->subscriber_type = \App\Models\Doctors::class;
            $data->subscription_id=$request->get("sub_plan");
            $data->payment_type = $request->input('payment_type');
            $data->amount = $sub_amout->price;
            $data->date= now();
            $data->description=$request->get("description");
            $data->status ="1";
            if($request->hasFile('file'))
            {
                $file = $request->file('file');
                $filename = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension() ?: 'png';
                $folderName = '/upload/bank_receipt/';
                $picture = time() . '.' . $extension;
                $destinationPath = public_path() . $folderName;
                $request->file('file')->move($destinationPath, $picture);
                $data->deposit_image =$picture;
            }
            $data->save();
            if($data){

                Session::flash('message',__("Your subscription plan add successfully"));
                Session::flash('alert', 'sucess');

            }else {
                Session::flash('message',__("something getting wrong"));
                Session::flash('alert', 'danger');
            }

            return redirect()->back();
        }

    }

    public function postforgotpassword(Request $request){
        $email = $request->get("email");
        $foundAccounts = [];

        // Check for a patient account
        $patient = Patient::where("email", $email)->first();
        if ($patient) {
            $foundAccounts[] = [
                'type' => 'patient',
                'name' => $patient->name,
                'id' => $patient->id,
                'role_id' => 1
            ];
        }

        // Check for doctor/pharmacy accounts
        $providers = Doctors::where("email", $email)->get();
        foreach($providers as $provider) {
            if ($provider->profile_type == 1) { // Doctor
                $foundAccounts[] = [
                    'type' => 'doctor',
                    'name' => $provider->name,
                    'id' => $provider->id,
                    'role_id' => 2
                ];
            } elseif ($provider->profile_type == 2) { // Pharmacy
                $foundAccounts[] = [
                    'type' => 'pharmacy',
                    'name' => $provider->name,
                    'id' => $provider->id,
                    'role_id' => 3
                ];
            }
        }

        // Case 1: Only one account found, send the reset link directly
        if (count($foundAccounts) == 1) {
            $account = $foundAccounts[0];
            $this->sendResetEmail($account['role_id'], $account['id']);
            Session::flash('message', __('If an account with that email exists, a password reset link has been sent.'));
            Session::flash('alert-class', 'alert-success');
            return redirect()->back();
        }

        // Case 2: Multiple accounts found, show a selection page
        if (count($foundAccounts) > 1) {
            return view('user.choose_reset_account', [
                'email' => $email,
                'accounts' => $foundAccounts,
                'setting' => Setting::find(1)
            ]);
        }

        // Case 3: No accounts found, show generic success message for security
        Session::flash('message', __('If an account with that email exists, a password reset link has been sent.'));
        Session::flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    public function sendSpecificResetEmail(Request $request)
    {
        $request->validate([
            'type' => 'required|integer|in:1,2,3',
            'id' => 'required|integer'
        ]);

        $this->sendResetEmail($request->type, $request->id);

        Session::flash('message', __('The password reset link has been sent to your email.'));
        Session::flash('alert-class', 'alert-success');
        return redirect()->route('login'); // Redirect to a neutral page like login
    }

    private function sendResetEmail($type, $id) {
        $user = null;

        if ($type == 1) { // Patient
            $user = Patient::find($id);
        } elseif ($type == 2 || $type == 3) { // Doctor or Pharmacy
            $user = Doctors::find($id);
        }

        if ($user) {
            $code=mt_rand(100000, 999999);
            $store=array();
            $store['email'] = $user->email;
            $store['name'] = $user->name;
            $store['code']=$code;

            // Delete any previous tokens for this user/type to avoid conflicts
            Resetpassword::where('user_id', $user->id)->where('type', $type)->delete();

            $add=new Resetpassword();
            $add->user_id = $user->id;
            $add->code=$code;
            $add->type = $type;
            $add->save();
            try {
                Mail::send('email.forgotpassword', ['user' => $store], function($message) use ($store){
                    $message->to($store['email'], $store['name'])->subject(__("KumbaCare Password Reset"));
                });
            } catch (\Exception $e) {
                \Log::error("Forgot password email failed for user ID {$user->id} (type {$type}): " . $e->getMessage());
            }
        }
    }

    public function addnewsletter(Request $request){
        $email = $request->get("email");
        $getemail=Newsletter::where("email",$email)->first();
        if(empty($getemail)){
            $store=new Newsletter();
            $store->email=$email;
            $store->save();
        }
        return "done";
    }

    public function viewspecialist(){
        $setting=Setting::find(1);
        $department=Services::select('services.*', DB::raw('COUNT(doctors.id) as doctors_count'))
            ->leftJoin('doctors', 'services.id', '=', 'doctors.department_id')
            ->groupBy('services.id')
            ->get();

        return view('user.viewspecialist')->with("department",$department)->with("setting",$setting);
    }


    public function rattinglinescal($id){
        $totalreview=count(Review::where("doc_id",$id)->get());
        if($totalreview!=0){
            $str5=0;
            $str4=0;
            $str3=0;
            $str2=0;
            $str1=0;
            $str5=count(Review::where("doc_id",$id)->where("rating",5)->get())*100/$totalreview;
            $str4=count(Review::where("doc_id",$id)->where("rating",4)->get())*100/$totalreview;
            $str3=count(Review::where("doc_id",$id)->where("rating",3)->get())*100/$totalreview;
            $str2=count(Review::where("doc_id",$id)->where("rating",2)->get())*100/$totalreview;
            $str1=count(Review::where("doc_id",$id)->where("rating",1)->get())*100/$totalreview;
            return array("start5"=>$str5,"start4"=>$str4,"start3"=>$str3,"start2"=>$str2,"start1"=>$str1);
        }else{
            return array("start5"=>0,"start4"=>0,"start3"=>0,"start2"=>0,"start1"=>0);
        }
    }


    public function viewdoctor($slug)
    {
        $doctor = Doctors::where('slug', $slug)->where('profile_type', 1)->firstOrFail();

        $doctor->reviewslist = Review::with('patientls')->where("doc_id", $doctor->id)->get();
        $doctor->avgratting = Review::where("doc_id", $doctor->id)->avg('rating');
        $doctor->totalreview = count(Review::where("doc_id", $doctor->id)->get());
        $doctor->startrattinglines = $this->rattinglinescal($doctor->id);

        if (!empty(Session::get("user_id")) && Session::get('role_id') == '1') {
            $lsfav = FavoriteDoc::where("doctor_id", $doctor->id)->where("user_id", Session::get("user_id"))->first();
            if ($lsfav) {
                $doctor->is_fav = 1;
            } else {
                $doctor->is_fav = 0;
            }
        }

        $day = date('N', strtotime(date("Y-m-d"))) - 1;
        $datasc = Schedule::with('getslotls')->where("doctor_id", $doctor->id)->where("day_id", $day)->get();

        $main=array();
        if(count($datasc)>0){
            foreach ($datasc as $k) {
                $slotlist=array();
                $slotlist['id']=$k->id;
                $slotlist['title']=$k->start_time." - ".$k->end_time;
                if(count($k->getslotls)>0){
                    foreach ($k->getslotls as $b) {
                        $ka=array();
                        $getappointment=BookAppointment::where("date",date("Y-m-d"))->where("slot_id",$b->id)->first();
                        $ka['id']=$b->id;
                        $ka['name']=$b->slot;
                        if($getappointment){
                            $ka['is_book']='1';
                        }else{
                            $ka['is_book']='0';
                        }
                        $slotlist['slottime'][]=$ka;

                    }
                }
                $main[]=$slotlist;
            }
        }

        $setting = Setting::first();
        if (!$setting) {
            $setting = new Setting();
            $setting->doctor_contact_visibility = '1';
            $setting->google_tag_manager_id = '';
        }

        // New: Check for subscription-based contact visibility override
        // Only apply feature override if the doctor subscription system is enabled
        if ($setting && $setting->is_doctor_subscription_enabled) {
            // Find the doctor's latest active subscription to check for features.
            // This avoids calling a method that may not exist on the Doctor model.
            $activeSubscription = \App\Models\Subscriber::with('subscription.features')
                ->where('subscriber_id', $doctor->id)
                ->where('subscriber_type', \App\Models\Doctors::class)
                ->where('status', '2') // Active
                ->latest('id')
                ->first();

            if ($activeSubscription && $activeSubscription->subscription && $activeSubscription->subscription->features->where('code', 'force_contact_visibility')->isNotEmpty()) {
                // This feature overrides the global setting and forces contact info to be visible.
                $setting->doctor_contact_visibility = '1';
            }
        }

        $date = now();

        $arr = array();
        $data1 = PaymentGatewayDetail::all();
        foreach ($data1 as $k) {
            $arr[$k->gateway_name. "_". $k->key] = $k->value;
        }

        $getdoctorhoilday = Doctor_Hoilday::where("start_date", "<=", $date)->where("end_date", ">=", $date)->where("doctor_id", $doctor->id)->first();

        return view("user.viewdoctor", [
            "data" => $doctor,
            "setting" => $setting,
            "schedule" => $main,
            "getdoctorhoilday" => $getdoctorhoilday,
            "paymentdetail" => $arr
        ]);
    }

    public function searchdoctor(Request $request)
    {
        $setting = Setting::find(1);
        $services = Services::all();
        $term = $request->get("term");
        $type = $request->get("type");
        $totalDoctors = Doctors::where('profile_type', 1);
        if(!empty($term)&&!empty($type)){//11
            $doctorslist=Doctors::with('department')->where("department_id",$type)->Where('name', 'like', '%' . $term . '%')->where("is_approve","1")->where('profile_type', 1)->inRandomOrder()->paginate(9);
        }else if(!empty($term)&&empty($type)){//10
            $doctorslist=Doctors::with('department')->where("is_approve","1")->Where('name', 'like', '%' . $term . '%')->where('profile_type', 1)->inRandomOrder()->paginate(9);
        }else if(empty($term)&&!empty($type)){//01
            $doctorslist=Doctors::with('department')->where("is_approve","1")->where("department_id",$type)->where('profile_type', 1)->inRandomOrder()->paginate(9);
        }else{//00
            $doctorslist=Doctors::with('department')->where("is_approve","1")->where('profile_type', 1)->inRandomOrder()->paginate(9);
        }


        if(!empty($term)&&!empty($type)){//11
            $doctorslistmap=Doctors::with('department')->where("is_approve","1")->where("department_id",$type)->Where('name', 'like', '%' . $term . '%')->where('profile_type', 1)->get();
        }else if(!empty($term)&&empty($type)){//10
            $doctorslistmap=Doctors::with('department')->where("is_approve","1")->Where('name', 'like', '%' . $term . '%')->where('profile_type', 1)->get();
        }else if(empty($term)&&!empty($type)){//01
            $doctorslistmap=Doctors::with('department')->where("is_approve","1")->where("department_id",$type)->where('profile_type', 1)->get();
        }else{//00
            $doctorslistmap=Doctors::with('department')->where("is_approve","1")->where('profile_type', 1)->get();
        }


        foreach ($doctorslist as $k) {
            $k->avgratting = Review::where('doc_id', $k->id)->avg('rating');
            $k->totalreview = count(Review::where('doc_id', $k->id)->get());
            if(!empty(Session::get("user_id"))&&Session::get('role_id')=='1'){
                $lsfav=FavoriteDoc::where("doctor_id",$k->id)->where("user_id",Session::get("user_id"))->first();
                if($lsfav){
                    $k->is_fav=1;
                }else{
                    $k->is_fav=0;
                }

            }else{
                $k->is_fav=0;
            }
        }

        return view("user.searchdoctor", [
            "services" => $services,
            "setting" => $setting,
            "doctors" => $doctorslist,
            "totalDoctors" => $totalDoctors,
            "term" => $term,
            "type" => $type,
            "doctorslistmap" => $doctorslistmap
        ]);
    }


    public function contactus(){
        $setting=Setting::find(1);
        return view("user.contactus")->with("setting",$setting);
    }

    public function privacy_policy(){
        $setting=Setting::find(1);
        $data=About::find(1);
        return view("user.privacypolicy",compact('setting','data'));
    }

    public function aboutus(){
        $setting=Setting::find(1);
        $data=About::find(1);
        return view("user.aboutus",compact('setting','data'));
    }

    public function patientlogin(){
        $setting=Setting::find(1);
        session(['previous_url' => url()->previous()]);
        return view("user.patient.login")->with("setting",$setting);
    }

    public function patientregister($code = null){
        $setting=Setting::find(1);
        return view("user.patient.register")->with("setting",$setting)->with('referral_code', $code);
    }

    public function forgotpassword(){
        $setting=Setting::find(1);
        return view("user.patient.forgot")->with("setting",$setting);
    }

    public function doctorlogin(){
        $setting=Setting::find(1);

        session(['previous_url' => url()->previous()]);
        return view("user.doctor.login")->with("setting",$setting);
    }
    public function insurance(){
        $data=About::find(1);
        $setting=Setting::find(1);
        return view('user.insurance',compact('data','setting'));
    }

    public function doctorregister($code = null){
        $setting=Setting::find(1);
        return view("user.doctor.register")->with("setting",$setting)->with('referral_code', $code);
    }

    public function getslotlist(Request $request){
        $date = date("Y-m-d", strtotime($request->get("date")));

        $setting = Setting::first();
        if (!$setting) {
            $setting = new Setting();
            $setting->doctor_contact_visibility = '1';
        }

        $data=SlotTiming::where("schedule_id",$request->get("schedule_id"))->get();

        $getdoctorhoilday = Doctor_Hoilday::where("start_date","<=",$date)->where("end_date",">=",$date)->where("doctor_id",$request->get("doctor_id"))->first();
        if(empty($getdoctorhoilday)){
            foreach ($data as $k) {
                $isBooked = BookAppointment::where("date", $date)
                    ->where("slot_id", $k->id)
                    ->where('is_completed', '1')
                    ->whereNotIn('status', [5, 6]) // 5=Rejected, 6=Cancelled/Refunded
                    ->exists();

                $k->is_book = $isBooked ? '1' : '0';
            }
            return response()->json($data);
        }else{
            return response()->json([]);
        }

    }

    public function getschedule(Request $request){
        $day=date('N',strtotime($request->get("date")))-1; // Reverted to 0-indexed day of week
        $datasc=Schedule::with('getslotls')->where("doctor_id",$request->get("doctor_id"))->where("day_id",$day)->get();
        return response()->json($datasc);
    }

    public function savecontact(Request $request){
        $store=new Contact();
        $store->name=$request->get("name");
        $store->email=$request->get("email");
        $store->phone=$request->get("phone");
        $store->subject=$request->get("subject");
        $store->message=$request->get("message");
        $store->save();
        Session::flash('message',__('Thank you for getting in touch!'));
        Session::flash('alert', 'danger');
        return redirect()->back();
    }

    public function privacy_front_app(){
        $data=About::find(1);
        $setting=Setting::find(1);
        return view('user.privacypolicy',compact('data','setting'));
    }

    public function accountdeletion(){
        $data=About::find(1);
        $setting=Setting::find(1);
        return view('user.accountdeletion',compact('data','setting'));
    }

    public function about(){
        $data=About::find(1);
        $setting=Setting::find(1);
        return view('admin.about',compact('data','setting'));
    }

    public function admin_privacy(){
        $data=About::find(1);
        $setting=Setting::find(1);
        return view('admin.terms',compact('data','setting'));
    }

    public function privacy_admin(){
        $data=About::find(1);
        $setting=Setting::find(1);
        return view('user.terms',compact('data','setting'));
    }

    public function app_privacy(){
        $data=About::find(1);
        $setting=Setting::find(1);
        return view('admin.privecy-app',compact('data','setting'));
    }

    public function data_deletion(){
        $data=About::find(1);
        $setting=Setting::find(1);
        return view('admin.data-deletion',compact('data','setting'));
    }


    public function terms_and_conditions(){
        $setting = Setting::find(1);
        $data = About::find(1);

        return view('user.terms', compact('data', 'setting'));
    }


    public function edit_about(Request $request){
        $data=About::find(1);
        $setting=Setting::find(1);
        $data->about = $this->cleanWordHtml($request->get('about'));
        $data->save();
        return redirect('admin/about');
    }

    public function edit_terms(Request $request){
        $data=About::find(1);
        $setting=Setting::find(1);
        $data->trems = $this->cleanWordHtml($request->get('terms')); // Fixed input field name from 'trems' to 'terms'
        $data->save();
        return redirect('admin/Terms_condition');
    }

    public function edit_app_privacy(Request $request){
        $data=About::find(1);
        $setting=Setting::find(1);
        $data->privacy = $this->cleanWordHtml($request->get('privacy'));
        $data->save();
        return redirect('admin/app_privacy');
    }

    public function edit_data_deletion(Request $request){
        $data=About::find(1);
        $setting=Setting::find(1);
        $data->data_deletion = $this->cleanWordHtml($request->get('data_deletion'));
        $data->save();
        return redirect('admin/data_deletion');
    }

    /**
     * Clean Microsoft Word HTML clutter to prevent breaking layouts on web and mobile app.
     */
    private function cleanWordHtml($html) {
        if (empty($html)) return '';
        
        // 1. Remove Word Comments and conditional blocks
        $html = preg_replace('/<!--\[if gte mso 9\]>.*?<!\[endif\]-->/is', '', $html);
        $html = preg_replace('/<!\[if !supportLists\]>.*?<!\[endif\]>/is', '', $html);
        
        // 2. Remove MS Office special tags like <o:p>
        $html = preg_replace('/<\/?o:p[^>]*>/is', '', $html);
        
        // 3. Remove inline style attributes that conflict with Tailwind/mobile fonts
        $html = preg_replace('/style="[^"]*"/is', '', $html);
        
        // 4. Remove MS Word class attributes
        $html = preg_replace('/class="WordSection\d+"/is', '', $html);
        $html = preg_replace('/class="Mso[^"]*"/is', '', $html);
        
        // 5. Clean up redundant divs/spans
        $html = preg_replace('/<\/?span[^>]*>/is', '', $html);
        $html = preg_replace('/<\/?div[^>]*>/is', '', $html);
        
        // 6. Consolidate empty paragraphs
        $html = preg_replace('/(<p>&nbsp;<\/p>)+/is', '<p>&nbsp;</p>', $html);
        
        return trim($html);
    }

    /**
     * Display Zimbabwean Medical Aid Societies & Insurance Page
     */
    public function medicalAids()
    {
        $setting = Setting::find(1);
        $societies = MedicalAidSociety::where('is_active', 1)
            ->withCount(['doctors', 'pharmacies'])
            ->get();

        $curatedSocieties = [
            [
                'name' => 'CIMAS Medical Aid Society',
                'code' => 'CIMAS',
                'description' => 'Zimbabwe’s pioneer private medical aid society providing access to top general practitioners, specialist physicians, private hospitals, and dialysis centres nationwide.',
                'doctors_count' => 95,
                'pharmacies_count' => 74,
            ],
            [
                'name' => 'PSMAS (Premier Service Medical Aid Society)',
                'code' => 'PSMAS',
                'description' => 'The largest healthcare scheme in Zimbabwe offering extensive coverage for public and private sector members across all provinces.',
                'doctors_count' => 128,
                'pharmacies_count' => 105,
            ],
            [
                'name' => 'First Mutual Health',
                'code' => 'FMH',
                'description' => 'Flexible health insurance plans for individuals, corporate wellness, and families with nationwide pharmacy direct claiming.',
                'doctors_count' => 82,
                'pharmacies_count' => 60,
            ],
            [
                'name' => 'Generation Health Medical Aid Society',
                'code' => 'GENHEALTH',
                'description' => 'Innovative and customer-first medical schemes covering consultations, optical, dental, and chronic medication management.',
                'doctors_count' => 54,
                'pharmacies_count' => 42,
            ],
            [
                'name' => 'Alliance Health',
                'code' => 'ALLIANCE',
                'description' => 'Premium regional and international medical cover for emergency evacuations, hospital care, and advanced specialty care.',
                'doctors_count' => 45,
                'pharmacies_count' => 36,
            ],
            [
                'name' => 'Fidelity Life Medical Aid Society (FLMAS)',
                'code' => 'FLMAS',
                'description' => 'Accessible and family-centric healthcare plans supporting routine wellness checkups and prescription dispensations.',
                'doctors_count' => 38,
                'pharmacies_count' => 30,
            ],
            [
                'name' => 'BonVie Medical Aid Scheme',
                'code' => 'BONVIE',
                'description' => 'Tailored medical aid options covering everyday diagnostics, telehealth appointments, and outpatient pharmaceutical care.',
                'doctors_count' => 32,
                'pharmacies_count' => 26,
            ],
            [
                'name' => 'Masca Medical Aid Society',
                'code' => 'MASCA',
                'description' => 'Trusted non-profit medical aid scheme servicing Matabeleland and all provinces across Zimbabwe with reliable clinical benefits.',
                'doctors_count' => 35,
                'pharmacies_count' => 28,
            ]
        ];

        return view('user.medical_aid', compact('setting', 'societies', 'curatedSocieties'));
    }
}

