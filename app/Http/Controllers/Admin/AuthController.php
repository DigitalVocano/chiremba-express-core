<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Session;
use validate;
use Sentinel;
use DB;
use DataTables;
use App\Models\User;
use App\Models\Services;
use App\Models\Review;
use App\Models\Patient;
use App\Models\Doctors;
use App\Models\Reportspam;
use App\Models\BookAppointment;
use App\Models\Resetpassword;
use App\Models\Code;
use App\Models\Setting;
use DateTimeZone;
use App\Models\Wallet;
use App\Models\WalletWithdrawalRequest; // New: Import WalletWithdrawalRequest model
use App\Models\WalletTransaction;
use DateTime; // Corrected namespace usage
use Hash;
use Mail;
class AuthController extends Controller
{

    public function showlogin(){
       // Auto-login from persistent cookie if session expired & remember_me was checked
       if (!Session::has('admin') && isset($_COOKIE['admin_remember_token']) && isset($_COOKIE['remember_me']) && $_COOKIE['remember_me'] == 1) {
           try {
               $tokenData = explode(':', base64_decode($_COOKIE['admin_remember_token']));
               if (count($tokenData) == 2) {
                   $userId = $tokenData[0];
                   $passHash = $tokenData[1];
                   $userModel = User::find($userId);
                   if ($userModel && sha1($userModel->password) === $passHash) {
                       $sentinelUser = Sentinel::findById($userModel->id);
                       if ($sentinelUser) {
                           Sentinel::login($sentinelUser, true);
                           Session::put('admin', [
                               'id' => $sentinelUser->id,
                               'name' => $sentinelUser->first_name,
                               'profile_pic' => $sentinelUser->profile_pic
                           ]);
                           return redirect("admin/dashboard");
                       }
                   }
               }
           } catch (\Exception $e) {}
       }

       $setting=Setting::find(1);
       if ($setting) {
           Session::put("is_demo",$setting->is_demo);
           Session::put("favicon",asset('image_web').'/'.$setting->favicon);
           Session::put("logo",asset('image_web').'/'.$setting->logo);
       }
       return view("admin.login", compact('setting'));
    }

    public function showSystemGuide(){
        $setting = Setting::find(1);
        return view("admin.system_guide", compact('setting'));
    }

    public function postlogin(Request $request){
        $email = $request->get('email');
        $password = $request->get('password');
        $remember = $request->has('remember_me') && $request->get('remember_me') == 1;

        $userModel = User::where('email', $email)->first();
        $user = Sentinel::authenticate([
            'email' => $email,
            'password' => $password
        ], $remember);

        if ($user) {
            // Check 2FA requirement
            if ($userModel && $userModel->two_factor_enabled && !empty($userModel->two_factor_secret)) {
                Session::put('2fa_pending_admin_id', $user->id);
                Session::put('2fa_pending_remember', $remember);
                Sentinel::logout();
                return redirect()->route('admin.2fa.verify');
            }

            return $this->completeAdminLogin($user, $remember, $email);
        } else {
            Session::flash('message', __("Login Credentials Are Wrong"));
            Session::flash('alert', 'danger');
            return redirect()->back();
        }
    }

    private function completeAdminLogin($user, $remember, $email)
    {
        Session::put('admin', [
            'id' => $user->id,
            'name' => $user->first_name,
            'profile_pic' => $user->profile_pic
        ]);

        if ($remember) {
            setcookie('email', $email, time() + (86400 * 30), "/");
            setcookie('remember_me', 1, time() + (86400 * 30), "/");
            setcookie('admin_remember_token', base64_encode($user->id . ':' . sha1($user->password)), time() + (86400 * 30), "/");
        } else {
            setcookie('remember_me', 0, time() - 3600, "/");
            setcookie('admin_remember_token', '', time() - 3600, "/");
        }

        return redirect("admin/dashboard");
    }

    public function show2faVerify()
    {
        if (!Session::has('2fa_pending_admin_id')) {
            return redirect()->route('admin.login');
        }

        $setting = Setting::find(1);
        return view("admin.verify_2fa", compact('setting'));
    }

    public function post2faVerify(Request $request)
    {
        if (!Session::has('2fa_pending_admin_id')) {
            return redirect()->route('admin.login');
        }

        $adminId = Session::get('2fa_pending_admin_id');
        $remember = Session::get('2fa_pending_remember', false);
        $code = trim($request->get('code', ''));

        $userModel = User::find($adminId);
        if (!$userModel) {
            Session::forget(['2fa_pending_admin_id', '2fa_pending_remember']);
            return redirect()->route('admin.login');
        }

        $isValid = \App\Helpers\Google2FA::verifyCode($userModel->two_factor_secret, $code);

        if (!$isValid && !empty($userModel->two_factor_recovery_codes)) {
            $recoveryCodes = json_decode($userModel->two_factor_recovery_codes, true) ?? [];
            if (in_array($code, $recoveryCodes)) {
                $isValid = true;
                $recoveryCodes = array_values(array_diff($recoveryCodes, [$code]));
                $userModel->two_factor_recovery_codes = json_encode($recoveryCodes);
                $userModel->save();
            }
        }

        if ($isValid) {
            $sentinelUser = Sentinel::findById($userModel->id);
            Sentinel::login($sentinelUser, $remember);
            Session::forget(['2fa_pending_admin_id', '2fa_pending_remember']);
            return $this->completeAdminLogin($sentinelUser, $remember, $userModel->email);
        }

        Session::flash('message', __("Invalid 2FA Verification Code. Please try again."));
        Session::flash('alert', 'danger');
        return redirect()->back();
    }

    public function show2faSetup()
    {
        $sentinelUser = Sentinel::getUser();
        $user = User::find($sentinelUser->id);

        $isEnabled = (bool)($user->two_factor_enabled && !empty($user->two_factor_secret));
        $secret = $user->two_factor_secret;

        if (!$isEnabled && empty($secret)) {
            $secret = \App\Helpers\Google2FA::generateSecretKey();
        }

        $otpAuthUrl = \App\Helpers\Google2FA::getOtpAuthUrl('ChirembaExpress Admin', $user->email, $secret);
        $qrCodeUrl = \App\Helpers\Google2FA::getQrCodeUrl($otpAuthUrl);

        return view('admin.2fa_setup', compact('user', 'isEnabled', 'secret', 'qrCodeUrl'));
    }

    public function enable2fa(Request $request)
    {
        $sentinelUser = Sentinel::getUser();
        $user = User::find($sentinelUser->id);

        $secret = trim($request->get('secret', ''));
        $code = trim($request->get('code', ''));

        if (empty($secret) || empty($code)) {
            Session::flash('error', __("Secret key and verification code are required."));
            return redirect()->back();
        }

        $isValid = \App\Helpers\Google2FA::verifyCode($secret, $code);

        if ($isValid) {
            $user->two_factor_secret = $secret;
            $user->two_factor_enabled = 1;

            $recoveryCodes = [];
            for ($i = 0; $i < 5; $i++) {
                $recoveryCodes[] = sprintf('%06d', random_int(100000, 999999));
            }
            $user->two_factor_recovery_codes = json_encode($recoveryCodes);
            $user->save();

            Session::flash('message', __("2FA Authentication has been enabled successfully on your account!"));
            return redirect()->route('admin.2fa.setup');
        }

        Session::flash('error', __("Invalid 6-digit verification code. Please scan the QR code and try again."));
        return redirect()->back();
    }

    public function disable2fa(Request $request)
    {
        $sentinelUser = Sentinel::getUser();
        $user = User::find($sentinelUser->id);

        $user->two_factor_enabled = 0;
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->save();

        Session::flash('message', __("Two-Factor Authentication disabled."));
        return redirect()->route('admin.2fa.setup');
    }

    public  function checkcurrentpassword($pwd){
          $user=Sentinel::getUser(); // Corrected namespace usage
           if (Hash::check($pwd, $user->password))
           {
              $data=1;
           }
          else{
              $data=0;
           }
         return $data;
     }

     public function reset_password($code){
            $setting = Setting::find(1); // Corrected namespace usage
            $data=Resetpassword::where("code",$code)->first();
            if($data){
              return view('admin.reset_pwd')->with("id",$data->user_id)->with("code",$code)->with("type",$data->type)->with("setting",$setting);
            }
            else{
              return view('admin.reset_pwd')->with("msg",__('Code Expired'))->with("setting",$setting);
            }
      }

      public function reset_new_pwd(Request $request){
          
           $setting = Setting::find(1); // Corrected namespace usage
            if($request->get('id')==""){
                return view('admin.reset_pwd')->with("msg",__('pwd_reset'))->with("setting",$setting);
            }else{
                if($request->get("type")==1){
                     $user=Patient::find($request->get('id'));
                }else{
                    $user=Doctors::find($request->get('id')); // Corrected namespace usage
                }
                $user->password=\Hash::make($request->get('npwd')); // Hash new password
                
                $user->save();

                $codedel=Resetpassword::where('user_id',$request->get("id"))->delete();
               return view('admin.reset_pwd')->with("msg",__('pwd_reset'))->with("setting",$setting);
            }
      }

    public function showdashboard(){
      $totaldoctor=count(Doctors::all()); // Corrected namespace usage
      $totalappointment=count(BookAppointment::all());
      $totalreview=count(Review::all());
      $totalpatient=count(Patient::all());
    	return view("admin.dashboard")->with("totaldoctor",$totaldoctor)->with("totalappointment",$totalappointment)->with("totalreview",$totalreview)->with("totalpatient",$totalpatient);
    }

    public  function check_password_same($pwd){
    $user=Sentinel::getUser(); // Corrected namespace usage
     if (Hash::check($pwd, $user->password))
     {
        $data=1;
     }
    else{
        $data=0;
     }
   return $data;
   }

   public function updatepassword(Request $request){
     $user=Sentinel::getUser(); // Corrected namespace usage
       if (Hash::check($request->get('currentpwd'), $user->password))
        {
            Sentinel::update($user, array('password' => $request->get('newpwd')));
            Session::flash('message',__("Password Update Successfully"));
            Session::flash('alert-class', 'alert-success');
            return redirect()->back();
        }
       else{
          Session::flash('message',__("Current Password Is Incorrect"));
          Session::flash('alert-class', 'alert-danger');
          return redirect()->back();
       }

   }

    public function logout(){
      $user=Sentinel::getUser();
      \Sentinel::logout($user, true); // Log out the current user and invalidate their session.
      // Forget only the admin session to allow other user types to remain logged in.
      Session::forget('admin');
      setcookie('email', '', time() - 3600, "/"); // Expire "remember me" email cookie
      setcookie('remember_me', '', time() - 3600, "/"); // Expire "remember me" checkbox cookie
      return redirect("/");
    }

    public function showsuser(){
      return view("admin.user.default");
    }

    public function showcontact(){
       return view("admin.contactus");
    }

    public function userstable(Request $request){
       $users = Patient::query();

        // Search filter for name, email, or phone
        if ($request->has('search_term') && !empty($request->search_term)) {
            $searchTerm = $request->search_term;
            $users->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('email', 'like', '%' . $searchTerm . '%')
                      ->orWhere('phone', 'like', '%' . $searchTerm . '%');
            });
        }

        // Sorting logic
        $sortBy = $request->get('sort_by', 'newest'); // Default to 'newest'
        switch ($sortBy) {
            case 'oldest':
                $users->orderBy('created_at', 'asc');
                break;
            case 'name_asc':
                $users->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $users->orderBy('name', 'desc');
                break;
            case 'newest':
            default:
                $users->orderBy('created_at', 'desc');
                break;
        }
        
        return DataTables::of($users)
            ->editColumn('id', function ($users) {
                return $users->id;
            })
            ->addColumn('avatar', function ($users) {
                return $users->profile_pic ? asset('upload/profile/' . $users->profile_pic) : '';
            })
            ->editColumn('name', function ($users) {
                return $users->name;
            })
            ->editColumn('email', function ($users) {
                return $users->email;
            })
            ->editColumn('phone', function ($users) {
                return $users->phone;
            })
            ->editColumn('action', function ($users) {
                $deleteUrl = route('admin.patients.delete', ['id' => $users->id]);
                return '<div class="flex items-center justify-end space-x-2 whitespace-nowrap">' .
                       '<a href="javascript:void(0)" onclick="delete_record(\'' . $deleteUrl . '\')" class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white font-medium text-xs rounded-md shadow-sm transition-colors duration-150"><i class="uil uil-trash-alt mr-1 text-sm"></i> Delete</a>' .
                       '</div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function deleteuser($id){
        $data=Patient::find($id); // Corrected namespace usage
        if($data){
            // Delete from ConnectyCube before deleting locally
            if(env('ConnectyCube') == true && !empty($data->connectycube_user_id)){
                $this->deleteconnectycubeuser($data->connectycube_user_id);
            }

            $deleteAppointment=BookAppointment::where("user_id",$id)->delete();
            $deleteremove=Review::where("user_id",$id)->delete();
            $data->delete();
        }
        Session::flash('message',__("Patient Delete Successfully"));
          Session::flash('alert-class', 'alert-success');
          return redirect()->back();
    }


    public function editprofile(){
        $userdata=Sentinel::getUser(); // Corrected namespace usage
        return view("admin.profile")->with("userdata",$userdata);
    }

    public function changepassword(Request $request){
        return view("admin.changepassword");
    }

    public function shownewsletter(){
       return view("admin.newsletter");
    }

    public function updatenewsletter(Request $request){
        $msg=$request->get("description");
          $getall=Newsletter::all();
          foreach($getall as $g){ // Corrected namespace usage
              $data=array();
              $data['email']=$g->email;
              $data['msg']=$msg;
              try {
                      $result=Mail::send('email.news', ['user' => $data], function($message) use ($data){
                         $message->to($data['email'],'customer')->subject("Freaktemplate");
                      });

              } catch (\Exception $e) {
              }

          }
       Session::flash('message',__("News Send Successfully"));
       Session::flash('alert-class', 'alert-success');
       return redirect()->back();
    }

       public function updateaccount(Request $request){
        $user=Sentinel::getUser(); // Corrected namespace usage
        $user->first_name=$request->get("first_name");
        $user->last_name=$request->get("last_name");
        $user->save();
        $rel_url=$user->profile_pic;
        if ($request->hasFile('profile_pic'))
           {
              $file = $request->file('profile_pic');
              $filename = $file->getClientOriginalName();
              $extension = $file->getClientOriginalExtension() ?: 'png';
              $folderName = '/upload/profile/';
              $picture = time() . '.' . $extension;
              $destinationPath = public_path() . $folderName;
              $request->file('profile_pic')->move($destinationPath, $picture);
              $user->profile_pic =$picture;
              $user->save();
              $image_path = public_path() ."/upload/profile/".$rel_url;
              if(file_exists($image_path)) {
                            try{
                                 unlink($image_path);
                            }
                            catch(\Exception $e)
                            {

                            }

                        }
            }
            Session::flash("successorder",__("Account Details Update Successfully"));
            return redirect()->back();
     }


     public function showcomplain(){
        $data=Reportspam::all();
        return view("admin.reportspam")->With("data",$data);
     }

     public function compaintable(){
         $compain =Reportspam::all();

        return DataTables::of($compain)
            ->editColumn('id', function ($compain) {
                return $compain->id;
            })
            ->editColumn('username', function ($compain) {
                $data=Patient::find($compain->user_id);
                return isset($data->name)?$data->name:"";
            })
            ->editColumn('title', function ($compain) {
                return $compain->title;
            })
            ->editColumn('description', function ($compain) {
                return \Illuminate\Support\Str::limit($compain->description, 50);
            })
            ->editColumn('action', function ($compain) {
                $patient = Patient::find($compain->user_id);
                $compain->patient_email = $patient ? $patient->email : '';
                $compain->patient_name = $patient ? $patient->name : 'N/A';
                $json_data = htmlspecialchars(json_encode($compain), ENT_QUOTES, 'UTF-8');
                
                return '<button @click="openModal(' . $json_data . ')" class="inline-flex items-center px-3 py-1.5 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">View</button>';
            })
            ->rawColumns(['action'])
            ->make(true);
     }

    public function showWalletManagement()
    {
        $setting = Setting::find(1); // Corrected namespace usage
        return view("admin.wallet.index")->with("setting", $setting);
    }

    public function walletListTable(Request $request)
    {
        $patients = DB::table('patient')
            ->leftJoin('wallets', function ($join) {
                $join->on('patient.id', '=', 'wallets.owner_id')
                     ->where('wallets.owner_type', '=', Patient::class);
            })
            ->select('patient.id as user_id', 'patient.name as owner_name', DB::raw("'Patient' as owner_type"), 'wallets.id as wallet_id', 'wallets.balance');

        $providers = DB::table('doctors')
            ->leftJoin('wallets', function ($join) {
                $join->on('doctors.id', '=', 'wallets.owner_id')
                     ->where('wallets.owner_type', '=', Doctors::class);
            })
            ->select('doctors.id as user_id', 'doctors.name as owner_name', DB::raw("IF(profile_type = 1, 'Doctor', 'Pharmacy') as owner_type"), 'wallets.id as wallet_id', 'wallets.balance');

        $users = $patients->unionAll($providers);

        // Wrap the union in a subquery to treat it as a single table for DataTables, which fixes sorting/searching issues.
        $query = DB::query()->fromSub($users, 'users');

        // Search filter for owner name
        if ($request->has('search_term') && !empty($request->search_term)) {
            $searchTerm = $request->search_term;
            $query->where('owner_name', 'like', '%' . $searchTerm . '%');
        }

        // Filter by owner type
        if ($request->has('owner_type_filter') && !empty($request->owner_type_filter)) {
            $query->where('owner_type', $request->owner_type_filter);
        }

        // Sorting logic
        $sortBy = $request->get('sort_by', 'balance_desc'); // Default sort
        switch ($sortBy) {
            case 'balance_asc':
                $query->orderBy('balance', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('owner_name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('owner_name', 'desc');
                break;
            case 'balance_desc':
            default:
                $query->orderBy('balance', 'desc');
                break;
        }

        return DataTables::of($query)
            ->editColumn('id', function ($wallet) {
                return $wallet->wallet_id ?? 'N/A';
            })
            ->editColumn('owner_type', function ($wallet) {
                return $wallet->owner_type;
            })
            ->editColumn('owner_name', function ($wallet) {
                return $wallet->owner_name;
            })
            ->editColumn('balance', function ($wallet) {
                $setting = Setting::first(); // Assuming you have a Setting model
                $balance = $wallet->balance ?? 0.00;
                return ($setting->currency ?? '$') . number_format($balance, 2);
            })
            ->editColumn('action', function ($wallet) {
                $viewTransactionsUrl = $wallet->wallet_id ? route('admin.wallet.transactions', ['id' => $wallet->wallet_id]) : '#';
                $ownerName = e($wallet->owner_name); // Sanitize owner name for use in JS
                $balance = $wallet->balance ?? 0.00;
                $user_id = $wallet->user_id;
                $user_type_key = $wallet->owner_type; // 'Patient', 'Doctor', or 'Pharmacy'

                $viewBtn = '<a href="' . $viewTransactionsUrl . '" class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 ' . ($wallet->wallet_id ? '' : 'opacity-50 cursor-not-allowed') . '">View Transactions</a>';
                $adjustBtn = '<button type="button" @click="openModal(' . $user_id . ', \'' . $user_type_key . '\', \'' . $ownerName . '\', ' . $balance . ')" class="ml-2 inline-flex items-center px-3 py-1.5 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">Adjust</button>';

                return '<div class="flex items-center">' . $viewBtn . $adjustBtn . '</div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function showWalletTransactions($id)
    {
        $setting = Setting::find(1); // Corrected namespace usage
        $wallet = Wallet::with('owner')->findOrFail($id);
        // Paginate transactions for better performance
        $transactions = $wallet->transactions()->orderBy('created_at', 'desc')->paginate(15);
        return view("admin.wallet.transactions", compact('setting', 'wallet', 'transactions'));
    }

    public function adjustWalletBalance(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'user_type' => 'required|string', // This will be 'Patient', 'Doctor', or 'Pharmacy'
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:credit,debit',
            'description' => 'nullable|string|max:255',
        ]);

        // Map the string identifier back to the correct model class
        $userTypeKey = $request->user_type;
        $modelClass = null;
        if ($userTypeKey === 'Patient') {
            $modelClass = Patient::class;
        } elseif ($userTypeKey === 'Doctor' || $userTypeKey === 'Pharmacy') {
            $modelClass = Doctors::class;
        }

        if (!$modelClass) {
            Session::flash('message', __('Invalid user type provided.'));
            Session::flash('alert-class', 'alert-danger');
            return redirect()->back();
        }

        $owner = $modelClass::find($request->user_id);
        if (!$owner) {
            Session::flash('message', __('User not found.'));
            Session::flash('alert-class', 'alert-danger');
            return redirect()->back();
        }

        // Get or create the wallet for the user
        $wallet = $owner->wallet()->firstOrCreate([]);

        DB::beginTransaction();
        try {
            if ($request->type === 'credit') {
                $wallet->increment('balance', $request->amount);
            } else {
                // Prevent negative balance unless explicitly allowed by business logic
                if ($wallet->balance < $request->amount) {
                    DB::rollBack();
                    Session::flash('message', __('Insufficient balance for debit operation.'));
                    Session::flash('alert-class', 'alert-danger');
                    return redirect()->back();
                }
                $wallet->decrement('balance', $request->amount);
            }

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'amount' => $request->amount,
                'type' => $request->type,
                'description' => $request->description ?? 'Manual adjustment by admin',
                'reference' => 'admin_manual_adjustment_' . now()->timestamp,
            ]);

            DB::commit();
            Session::flash('message', __('Wallet balance adjusted successfully!'));
            Session::flash('alert-class', 'alert-success');
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error("Admin wallet adjustment failed: " . $e->getMessage());
            Session::flash('message', __('Failed to adjust wallet balance.'));
            Session::flash('alert-class', 'alert-danger');
        }

        return redirect()->back();
    }

    /**
     * Show all wallet withdrawal requests for admin.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showWithdrawalRequests()
    {
        $setting = Setting::find(1); // Corrected namespace usage
        // Fetch all withdrawal requests, ordered by creation date
        $withdrawalRequests = WalletWithdrawalRequest::with('wallet.owner', 'processedByAdmin')
                                                    ->orderBy('created_at', 'desc')
                                                    ->paginate(15); // Paginate the results
        return view("admin.wallet.withdrawals", compact('setting', 'withdrawalRequests'));
    }

    /**
     * Process a wallet withdrawal request (approve or reject).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processWithdrawalRequest(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:wallet_withdrawal_requests,id',
            'action' => 'required|in:approve,reject',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $withdrawalRequest = WalletWithdrawalRequest::with('wallet')->findOrFail($request->request_id); // Corrected namespace usage

        // Prevent processing already processed requests
        if ($withdrawalRequest->status !== 'pending') {
            Session::flash('message', __('This withdrawal request has already been processed.'));
            Session::flash('alert-class', 'alert-warning');
            return redirect()->back();
        }

        DB::beginTransaction();
        try {
            $withdrawalRequest->processed_by_admin_id = Session::get("admin.id");
            $withdrawalRequest->processed_at = now();
            $withdrawalRequest->admin_notes = $request->admin_notes;

            if ($request->action === 'approve') {
                $wallet = $withdrawalRequest->wallet;

                // Ensure sufficient balance before debiting
                if ($wallet->balance < $withdrawalRequest->amount) { // Corrected namespace usage
                    DB::rollback();
                    Session::flash('message', __('Insufficient wallet balance to approve this withdrawal.'));
                    Session::flash('alert-class', 'alert-danger');
                    return redirect()->back();
                }

                $wallet->decrement('balance', $withdrawalRequest->amount);
                $wallet->save();

                WalletTransaction::create([ // Corrected namespace usage
                    'wallet_id' => $wallet->id,
                    'amount' => $withdrawalRequest->amount,
                    'type' => 'debit',
                    'description' => 'Wallet withdrawal approved by admin',
                    'reference' => 'withdrawal_req_' . $withdrawalRequest->id,
                ]);

                $withdrawalRequest->status = 'approved';
                Session::flash('message', __('Withdrawal request approved successfully!'));
                Session::flash('alert-class', 'alert-success');
            } else { // Reject
                $withdrawalRequest->status = 'rejected';
                Session::flash('message', __('Withdrawal request rejected successfully!'));
                Session::flash('alert-class', 'alert-info');
            }
            $withdrawalRequest->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error("Wallet withdrawal processing failed: " . $e->getMessage());
            Session::flash('message', __('Failed to process withdrawal request.'));
            Session::flash('alert-class', 'alert-danger');
        }

        return redirect()->back();
    }
}
