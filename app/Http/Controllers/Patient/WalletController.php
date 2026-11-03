<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Patient;
use App\Models\Setting;
use App\Models\WalletTransaction;
use App\Models\PaymentGatewayDetail;
use App\Models\MedicalAidSubmission;
use App\Models\AiCreditPackage;
use App\Services\WalletService;

class WalletController extends Controller
{
    public function showMedicalWallet()
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '1') {
            $setting = Setting::find(1);
            $userdata = Patient::find(Session::get("user_id"));

            $wallet = $userdata->wallet;
            if (!$wallet) {
                $wallet = $userdata->wallet()->create(['balance' => 0]);
            }

            $transactions = WalletTransaction::where('wallet_id', $wallet->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $paymentGateways = PaymentGatewayDetail::where('key', 'is_active')
                ->where('value', '1')
                ->select('gateway_name')
                ->distinct()
                ->get();

            return view("user.patient.patientwallet", [
                "setting" => $setting,
                "userdata" => $userdata,
                "wallet" => $wallet,
                "transactions" => $transactions,
                "paymentGateways" => $paymentGateways,
            ]);
        } else {
            return redirect()->route('login');
        }
    }

    public function showBuyCreditsPage()
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '1') {
            $setting = Setting::find(1);
            $userdata = Patient::with('wallet')->find(Session::get('user_id'));
            $creditPackages = AiCreditPackage::where('is_active', true)->orderBy('price')->get();
            return view("user.patient.buy_credits", compact('setting', 'userdata', 'creditPackages'));
        } else {
            return redirect()->route('login');
        }
    }

    public function purchaseCreditsWithWallet(Request $request)
    {
        $request->validate(['package_id' => 'required|exists:ai_credit_packages,id']);

        if (!Session::has("user_id") || Session::get("role_id") != '1') {
            return redirect()->route('login');
        }

        $patient = Patient::with('wallet')->find(Session::get("user_id"));
        $package = AiCreditPackage::find($request->package_id);
        $wallet = $patient->wallet;

        if (!$wallet || $wallet->balance < $package->price) {
            return redirect()->route('medical.wallet')->with([
                'message' => __('Insufficient wallet balance.'),
                'alert-class' => 'alert-danger'
            ]);
        }

        DB::beginTransaction();
        try {
            $wallet->decrement('balance', $package->price);
            $wallet->increment('ai_credits', $package->credits);
            WalletTransaction::create([
                'wallet_id' => $wallet->id, 'amount' => $package->price, 'type' => 'debit',
                'description' => 'Purchase of ' . $package->name, 'reference' => 'ai_pack_' . $package->id . '_' . time(),
            ]);
            DB::commit();
            return redirect()->route('patient.ai_assistant')->with('success', __('Successfully purchased credits!'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', __('An error occurred.'));
        }
    }
}