<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Setting;
use App\Models\Doctors;
use App\Models\WalletTransaction;
use App\Models\PaymentGatewayDetail;
use App\Models\WalletWithdrawalRequest;
use App\Models\ReferralSetting;
use App\Models\ReferralReward;
use App\Models\Referral;
use App\Models\RedeemedReward;
use App\Models\Subscriber;
use App\Models\Subscription;
use App\Models\AiCreditPackage;
use App\Services\WalletService;
use App\Services\SubscriptionService;
use DateTime;

class WalletController extends Controller
{
    public function showDoctorWallet()
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '2') {
            $setting = Setting::find(1);
            $doctordata = Doctors::find(Session::get("user_id"));

            $wallet = $doctordata->wallet;
            if (!$wallet) {
                $wallet = $doctordata->wallet()->create(['balance' => 0]);
            }

            $transactions = WalletTransaction::where('wallet_id', $wallet->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $paymentGateways = PaymentGatewayDetail::where('key', 'is_active')
                ->where('value', '1')
                ->select('gateway_name')
                ->distinct()
                ->get();

            return view("user.doctor.doctorwallet", compact('setting', 'doctordata', 'wallet', 'transactions', 'paymentGateways'));
        } else {
            return redirect()->route('doctor.login');
        }
    }

    public function submitWithdrawalRequest(Request $request)
    {
        $setting = Setting::first();
        if (!$setting || $setting->is_wallet_withdrawal_enabled == '0') {
            Session::flash('message', __('Wallet Withdrawal is currently disabled.'));
            Session::flash('alert-class', 'alert-danger');
            return redirect()->back();
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'withdrawal_method' => 'required|in:ecocash,nostro,zwl',
            'ecocash_phone' => 'required_if:withdrawal_method,ecocash|nullable|string|max:20',
            'nostro_bank_name' => 'required_if:withdrawal_method,nostro|nullable|string|max:255',
            'nostro_account_name' => 'required_if:withdrawal_method,nostro|nullable|string|max:255',
            'nostro_account_number' => 'required_if:withdrawal_method,nostro|nullable|string|max:255',
            'zwl_bank_name' => 'required_if:withdrawal_method,zwl|nullable|string|max:255',
            'zwl_account_name' => 'required_if:withdrawal_method,zwl|nullable|string|max:255',
            'zwl_account_number' => 'required_if:withdrawal_method,zwl|nullable|string|max:255',
        ]);

        $doctor = Doctors::find(Session::get("user_id"));
        if (!$doctor || !$doctor->wallet) {
            Session::flash('message', __('Wallet not found for your account.'));
            Session::flash('alert-class', 'alert-danger');
            return redirect()->back();
        }

        $wallet = $doctor->wallet;
        $amount = $request->amount;

        if ($wallet->balance < $amount) {
            Session::flash('message', __('Insufficient wallet balance for withdrawal.'));
            Session::flash('alert-class', 'alert-danger');
            return redirect()->back();
        }

        $accountDetails = [];
        if ($request->withdrawal_method === 'ecocash') {
            $accountDetails = ['phone' => $request->ecocash_phone];
        } elseif ($request->withdrawal_method === 'nostro') {
            $accountDetails = ['bank_name' => $request->nostro_bank_name, 'account_name' => $request->nostro_account_name, 'account_number' => $request->nostro_account_number];
        } elseif ($request->withdrawal_method === 'zwl') {
            $accountDetails = ['bank_name' => $request->zwl_bank_name, 'account_name' => $request->zwl_account_name, 'account_number' => $request->zwl_account_number];
        }

        WalletWithdrawalRequest::create([
            'wallet_id' => $wallet->id,
            'amount' => $amount,
            'withdrawal_method' => $request->withdrawal_method,
            'account_details' => json_encode($accountDetails),
        ]);

        Session::flash('message', __('Withdrawal request submitted successfully!'));
        Session::flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    public function referralDashboard()
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '2') {
            $setting = Setting::find(1);
            $referralSetting = ReferralSetting::where('key', 'doctor_can_refer')->first();

            if (!$referralSetting || $referralSetting->value != '1') {
                return redirect()->route('doctordashboard')->with([
                    'message' => __('The referral program is currently unavailable.'),
                    'alert-class' => 'alert-info'
                ]);
            }

            $user = Doctors::find(Session::get("user_id"));
            if (empty($user->referral_code)) {
                $user->referral_code = strtoupper(Str::random(8));
                $user->save();
            }

            $rewards = ReferralReward::where('is_active', true)->orderBy('points_required', 'asc')->get();
            $referrals = Referral::with('referred')->where('referrer_id', $user->id)->where('referrer_type', Doctors::class)->latest()->paginate(10);

            return view('user.doctor.referrals.dashboard', compact('user', 'setting', 'rewards', 'referrals'));
        } else {
            return redirect()->route('doctor.login');
        }
    }

    public function redeemReward(Request $request)
    {
        $request->validate(['reward_id' => 'required|exists:referral_rewards,id']);
        $user = Doctors::with('wallet')->find(Session::get("user_id"));
        $reward = ReferralReward::find($request->reward_id);

        if ($user->referral_points < $reward->points_required) {
            return redirect()->back()->with('error', 'You do not have enough points to redeem this reward.');
        }

        try {
            DB::transaction(function () use ($user, $reward) {
                $user->decrement('referral_points', $reward->points_required);
                RedeemedReward::create([
                    'user_id' => $user->id,
                    'user_type' => get_class($user),
                    'referral_reward_id' => $reward->id,
                    'points_used' => $reward->points_required,
                    'status' => $reward->type == 'wallet' ? 'processed' : 'pending',
                ]);

                if ($reward->type == 'wallet' && $reward->value > 0) {
                    if (!$user->wallet) {
                        $user->wallet()->create(['balance' => 0]);
                        $user->load('wallet');
                    }
                    $user->wallet->credit($reward->value, 'Referral reward redeemed: ' . $reward->name, 'referral_reward_' . $reward->id);
                }
            });
            return redirect()->back()->with('success', 'Reward redeemed successfully!');
        } catch (\Exception $e) {
            Log::error('Reward redemption failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while redeeming the reward. Please try again.');
        }
    }

    public function showBuyCreditsPage()
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '2') {
            $setting = Setting::find(1);
            $doctordata = Doctors::with('wallet')->find(Session::get('user_id'));
            $activeSubscription = Subscriber::with('subscription.features')->where('subscriber_id', $doctordata->id)->where('subscriber_type', Doctors::class)->where('status', '2')->latest('id')->first();

            if ($activeSubscription && is_null($activeSubscription->remaining_ai_credits)) {
                if ($activeSubscription->subscription && $activeSubscription->subscription->features->where('code', 'ai_prescription_assistant')->isNotEmpty()) {
                    $activeSubscription->remaining_ai_credits = $activeSubscription->subscription->ai_credits ?? 0;
                    $activeSubscription->save();
                }
            }
            $creditPackages = AiCreditPackage::where('is_active', true)->orderBy('price')->get();
            return view("user.doctor.buy_credits", compact('setting', 'doctordata', 'creditPackages', 'activeSubscription'));
        } else {
            return redirect()->route('doctor.login');
        }
    }

    public function purchaseCreditsWithWallet(Request $request)
    {
        $request->validate(['package_id' => 'required|exists:ai_credit_packages,id']);
        if (!Session::has("user_id") || Session::get("role_id") != '2') {
            return redirect()->route('doctor.login');
        }

        $doctor = Doctors::with('wallet')->find(Session::get("user_id"));
        $package = AiCreditPackage::find($request->package_id);
        $activeSubscription = Subscriber::where('subscriber_id', $doctor->id)->where('subscriber_type', Doctors::class)->where('status', '2')->latest('id')->first();

        if (!$activeSubscription || is_null($activeSubscription->remaining_ai_credits)) {
            return redirect()->back()->with(['message' => __('Your current subscription plan does not support AI features.'), 'alert-class' => 'alert-danger']);
        }

        $wallet = $doctor->wallet;
        if (!$wallet || $wallet->balance < $package->price) {
            return redirect()->route('doctor.wallet')->with(['message' => __('Insufficient wallet balance.'), 'alert-class' => 'alert-danger']);
        }

        DB::beginTransaction();
        try {
            $wallet->decrement('balance', $package->price);
            $activeSubscription->increment('remaining_ai_credits', $package->credits);
            WalletTransaction::create([
                'wallet_id' => $wallet->id, 'amount' => $package->price, 'type' => 'debit',
                'description' => 'Purchase of ' . $package->name, 'reference' => 'ai_pack_' . $package->id . '_' . time(),
            ]);
            DB::commit();
            return redirect()->route('doctor.buy_credits')->with('message', __('Successfully purchased credits!'))->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('message', __('An error occurred.'))->with('alert-class', 'alert-danger');
        }
    }

    public function paymenthistory()
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '2') {
            $setting = Setting::find(1);
            $data = \App\Models\Complement_settlement::where("doctor_id", Session::get("user_id"))->get();
            $doctordata = Doctors::with('department')->find(Session::get("user_id"));
            return view("user.doctor.paymenthistory", compact('setting', 'data', 'doctordata'));
        } else {
            return redirect()->route('doctor.login');
        }
    }
}