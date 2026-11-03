<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Models\Patient;
use App\Models\Doctors;
use App\Services\PaymentService;
use App\Services\WalletService;
use Ramsey\Uuid\Uuid;
use App\Services\EcocashService;

class PaymentController extends Controller
{
    protected $paymentService;
    protected $walletService;

    public function __construct(PaymentService $paymentService, WalletService $walletService)
    {
        $this->paymentService = $paymentService;
        $this->walletService = $walletService;
    }

    public function initiatePaymentForWallet(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'user_id' => 'required|integer',
            'context' => 'nullable|string',
        ]);

        $amount = $request->amount;
        $paymentMethod = strtolower($request->payment_method);
        $userId = $request->user_id;
        $context = $request->context ?? 'general_topup';

        if ($paymentMethod == 'paynow') {
            Log::info('Payment Method: Paynow selected.');

            // Get the user's email based on their role
            $roleId = Session::get('role_id');
            $user = null;
            if ($roleId == 1) { // Patient
                $user = Patient::find($userId);
            } elseif ($roleId == 2 || $roleId == 3) { // Doctor or Pharmacy
                $user = Doctors::find($userId);
            }

            if (!$user || !$user->email) {
                return redirect()->back()->with('error', 'User email not found.');
            }

            $transactionId = 'TOPUP-' . Uuid::uuid4()->toString();
            
            $result = $this->paymentService->initiatePaynow($user->email, $amount, $transactionId);

            if ($result['success']) {
                Session::put('paynow_poll_url', $result['pollUrl']);
                Session::put('paynow_transaction_id', $transactionId);
                Session::put('wallet_topup_data', [
                    'user_id' => $userId,
                    'amount' => $amount,
                    'context' => $context,
                    'role_id' => $roleId
                ]);
                return redirect($result['redirectUrl']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }
        }

        if ($paymentMethod == 'ecocash') {
            Log::info('Payment Method: EcoCash selected.');
            
            $roleId = Session::get('role_id');
            $user = null;
            if ($roleId == 1) $user = Patient::find($userId);
            elseif ($roleId == 2 || $roleId == 3) $user = Doctors::find($userId);

            if (!$user) return redirect()->back()->with('error', 'User not found.');

            // Use provided phone or fallback to user profile phone
            $phone = $request->input('ecocash_phone') ?? $user->phone ?? $user->phoneno;
            
            if (!$phone) {
                 return redirect()->back()->with('error', 'Phone number is required for EcoCash.');
            }

            $ecocashService = new EcocashService();
            $reference = "EC-{$roleId}-{$userId}-" . time();
            $response = $ecocashService->initiateTransaction($amount, $phone, $reference);
            
            if ($response['success']) {
                $this->walletService->logTransaction($user, (float)$amount, 'credit', 'pending', 'Wallet Top-up via EcoCash', $reference);
                return redirect()->back()->with('success', $response['message']);
            } else {
                $this->walletService->logTransaction($user, (float)$amount, 'credit', 'failed', 'EcoCash initiation failed: ' . $response['message'], $reference);
                return redirect()->back()->with('error', 'EcoCash initiation failed: ' . $response['message']);
            }
        }
        
        return redirect()->back()->with('error', 'Unsupported payment method.');
    }

    public function handlePaynowCallback(Request $request)
    {
        $reference = $request->input('reference');
        $pollUrl = null;
        $topUpData = null;
        $transactionId = null;
        $isStateless = false;
        
        $dbTx = null;
        if ($reference) {
            $dbTx = \App\Models\PollUrl::where('reference', $reference)->first();
            if ($dbTx) {
                $pollUrl = $dbTx->poll_url;
                $transactionId = $dbTx->reference;
                $topUpData = [
                    'user_id' => $dbTx->user_id,
                    'amount' => $dbTx->amount,
                    'role_id' => $dbTx->role_id,
                ];
                $isStateless = true;
            }
        }

        // Fallback to session for web-based top-ups
        if (!$pollUrl) {
            $pollUrl = Session::get('paynow_poll_url');
            $topUpData = Session::get('wallet_topup_data');
            $transactionId = Session::get('paynow_transaction_id');
        }

        if (!$pollUrl || !$topUpData) {
            Log::warning("Paynow Callback received but no transaction state found. Reference: {$reference}");
            if ($request->expectsJson() || $isStateless) {
                return response()->json(['status' => 'error', 'message' => 'Transaction state not found.'], 400);
            }
            return redirect('/')->with('error', 'Session expired or transaction not found.');
        }

        // If the transaction in the database is already completed, don't re-process
        if ($dbTx && $dbTx->status === 'completed') {
            if ($request->expectsJson() || $isStateless) {
                return response()->json(['status' => 'OK']);
            }
            $redirectRoute = ($dbTx->role_id == 1) ? 'medical.wallet' : (($dbTx->role_id == 2) ? 'doctor.wallet' : 'pharmacy.wallet');
            return redirect()->route($redirectRoute)->with('success', 'Wallet topped up successfully!');
        }

        if ($this->paymentService->verifyTransaction($pollUrl)) {
            if ($dbTx && !empty($dbTx->meta_data)) {
                $meta = json_decode($dbTx->meta_data, true);
                if (is_array($meta) && isset($meta['doctor_id'])) {
                    if (!isset($meta['appointment_id'])) {
                        $patient = Patient::find($dbTx->user_id);
                        if ($patient) {
                            $meta['proof_of_payment'] = "Paynow Callback Ref: " . $dbTx->reference;
                            $appointmentService = app(\App\Services\AppointmentService::class);
                            $appointment = $appointmentService->bookAppointment($patient, $meta);
                            $meta['appointment_id'] = $appointment->id;
                            $dbTx->meta_data = json_encode($meta);
                        }
                    }
                    $dbTx->status = 'completed';
                    $dbTx->save();

                    if ($request->expectsJson() || $isStateless) {
                        return response()->json(['status' => 'OK']);
                    }
                    return redirect('/')->with('success', 'Appointment booked successfully!');
                }
            }

            $roleId = $topUpData['role_id'];
            $user = ($roleId == 1) ? Patient::find($topUpData['user_id']) : Doctors::find($topUpData['user_id']);

            if ($user) {
                $this->walletService->credit(
                    $user, 
                    $topUpData['amount'], 
                    'Wallet Top-up via Paynow', 
                    $transactionId
                );

                if ($dbTx) {
                    $dbTx->status = 'completed';
                    $dbTx->save();
                }
            }

            if (!$isStateless) {
                Session::forget(['paynow_poll_url', 'wallet_topup_data', 'paynow_transaction_id']);
                $redirectRoute = ($roleId == 1) ? 'medical.wallet' : (($roleId == 2) ? 'doctor.wallet' : 'pharmacy.wallet');
                return redirect()->route($redirectRoute)->with('success', 'Wallet topped up successfully!');
            }

            return response()->json(['status' => 'OK']);
        } else {
            if ($dbTx) {
                $dbTx->status = 'failed';
                $dbTx->save();
            }
            
            if ($isStateless) {
                return response()->json(['status' => 'failed', 'message' => 'Verification failed.'], 400);
            }
            return redirect()->back()->with('error', 'Payment verification failed.');
        }
    }

    public function payment_success()
    {
        return view('payment_success');
    }

    public function payment_failed()
    {
        return view('payment_failed');
    }
}
