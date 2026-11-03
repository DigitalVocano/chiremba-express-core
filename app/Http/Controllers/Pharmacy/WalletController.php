<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;
use App\Models\Doctors;
use App\Models\WalletTransaction;
use App\Models\PaymentGatewayDetail;
use App\Models\WalletWithdrawalRequest;
use App\Models\Subscriber;
use App\Models\Subscription;
use App\Services\WalletService;
use App\Services\SubscriptionService;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    public function showPharmacyWallet()
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '3') {
            $setting = Setting::find(1);
            $pharmacydata = Doctors::find(Session::get("user_id"));
            $wallet = $pharmacydata->wallet()->firstOrCreate(['balance' => 0]);
            $transactions = WalletTransaction::where('wallet_id', $wallet->id)->orderBy('created_at', 'desc')->paginate(10);
            $paymentGateways = PaymentGatewayDetail::where('key', 'is_active')
                ->where('value', '1')
                ->select('gateway_name')
                ->distinct()
                ->get();
            return view("user.pharmacy.pharmacywallet", compact('setting', 'pharmacydata', 'wallet', 'transactions', 'paymentGateways'));
        }
        return redirect()->route('doctor.login');
    }

    public function submitWithdrawalRequest(Request $request)
    {
        $setting = Setting::first();
        if (!$setting || $setting->is_wallet_withdrawal_enabled == '0') {
            return redirect()->back()->with(['message' => __('Wallet Withdrawal is currently disabled.'), 'alert-class' => 'alert-danger']);
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

        $pharmacy = Doctors::find(Session::get("user_id"));
        $wallet = $pharmacy->wallet;

        if (!$wallet || $wallet->balance < $request->amount) {
            return redirect()->back()->with(['message' => __('Insufficient wallet balance.'), 'alert-class' => 'alert-danger']);
        }

        $accountDetails = [];
        if ($request->withdrawal_method === 'ecocash') $accountDetails = ['phone' => $request->ecocash_phone];
        elseif ($request->withdrawal_method === 'nostro') $accountDetails = ['bank_name' => $request->nostro_bank_name, 'account_name' => $request->nostro_account_name, 'account_number' => $request->nostro_account_number];
        elseif ($request->withdrawal_method === 'zwl') $accountDetails = ['bank_name' => $request->zwl_bank_name, 'account_name' => $request->zwl_account_name, 'account_number' => $request->zwl_account_number];

        WalletWithdrawalRequest::create([
            'wallet_id' => $wallet->id,
            'amount' => $request->amount,
            'withdrawal_method' => $request->withdrawal_method,
            'account_details' => json_encode($accountDetails),
        ]);

        return redirect()->back()->with(['message' => __('Withdrawal request submitted successfully!'), 'alert-class' => 'alert-success']);
    }

    public function subscribeWithWallet(Request $request, WalletService $walletService, SubscriptionService $subscriptionService)
    {
        $request->validate(['plan_id' => 'required|exists:subscriptions,id']);
        if (!Session::has("user_id") || Session::get("role_id") != '3') {
            return redirect()->route('doctor.login');
        }

        $pharmacy = Doctors::with('wallet')->find(Session::get("user_id"));
        $plan = Subscription::find($request->plan_id);

        if (!$pharmacy || !$plan) return redirect()->back()->with(['message' => __('Invalid data.'), 'alert-class' => 'alert-danger']);

        DB::beginTransaction();
        try {
            $walletService->debit($pharmacy, $plan->price, 'Subscription payment: ' . $plan->name, 'sub_' . $plan->id . '_' . time());
            $subscriptionService->subscribe($pharmacy, $plan);
            DB::commit();
            return redirect()->route('pharmacydashboard')->with(['message' => __('Subscription activated!'), 'alert-class' => 'alert-success']);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['message' => __('Error processing subscription.'), 'alert-class' => 'alert-danger']);
        }
    }

    public function pharmacy_subscription_plan(Request $request)
    {
        if (!Session::has("user_id") || Session::get("role_id") != '3') {
            return redirect()->route('doctor.login');
        }

        $setting = Setting::find(1);
        $subscription = Subscription::with('features')
            ->where('is_active', 1)
            ->where('user_type', 'pharmacy')
            ->get();

        $pharmacydata = Doctors::with('wallet')->find(Session::get("user_id"));
        $activeSubscription = Subscriber::where('subscriber_id', $pharmacydata->id)
            ->where('subscriber_type', Doctors::class)
            ->where('status', '2')
            ->latest('id')
            ->first();

        return view('user.pharmacy.subscription_plan', compact('subscription', 'setting', 'pharmacydata', 'activeSubscription'));
    }

    public function mySubscription()
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '3') {
            $setting = Setting::find(1);
            $pharmacydata = Doctors::find(Session::get("user_id"));
            
            $subscription = Subscriber::with('subscription.features')
                ->where('subscriber_id', $pharmacydata->id)
                ->where('subscriber_type', Doctors::class)
                ->where('status', '2')
                ->latest('id')
                ->first();

            $expiryDate = $subscription ? $subscription->date : null; // Assuming date is already calculated expiry

            return view("user.pharmacy.my_subscription", compact('setting', 'pharmacydata', 'subscription', 'expiryDate'));
        }
        return redirect()->route('doctor.login');
    }
}