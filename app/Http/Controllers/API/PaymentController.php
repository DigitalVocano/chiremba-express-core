<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\PaymentService;
use App\Services\EcocashService;
use App\Models\Patient;
use App\Models\Doctors;
use Ramsey\Uuid\Uuid;

class PaymentController extends Controller
{
    protected $paymentService;
    protected $ecocashService;
    protected $walletService;

    public function __construct(PaymentService $paymentService, EcocashService $ecocashService, \App\Services\WalletService $walletService)
    {
        $this->paymentService = $paymentService;
        $this->ecocashService = $ecocashService;
        $this->walletService = $walletService;
    }

    public function initiate_payment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required_without_all:doctor_id,pharmacy_id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:paynow,ecocash',
            'phone' => 'required_if:payment_method,ecocash',
            'email' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => "0", 'message' => $validator->errors()->first()], 200);
        }

        $amount = $request->amount;
        $method = $request->payment_method;
        
        $roleId = $request->has('role_id') 
            ? (int)$request->role_id 
            : ($request->has('pharmacy_id') ? 3 : ($request->has('doctor_id') ? 2 : 1));

        $userId = $request->has('pharmacy_id') 
            ? $request->pharmacy_id 
            : ($request->has('doctor_id') ? $request->doctor_id : $request->user_id);

        $user = ($roleId == 1) ? Patient::find($userId) : Doctors::find($userId);

        if ($method == 'paynow') {
            $reference = "PN-{$roleId}-{$userId}-" . time();
            $email = $request->email ?? ($user->email ?? '');
            
            $result = $this->paymentService->initiatePaynow($email, $amount, $reference);
            if ($result['success']) {
                // Save pending transaction state to database
                \App\Models\PollUrl::create([
                    'reference' => $reference,
                    'poll_url' => $result['pollUrl'],
                    'amount' => $amount,
                    'user_id' => $userId,
                    'role_id' => $roleId,
                    'status' => 'pending'
                ]);

                if ($user) {
                    $this->walletService->logTransaction($user, $amount, 'credit', 'pending', 'Paynow Top-up Pending', $reference);
                }

                return response()->json([
                    'success' => "1",
                    'message' => 'Payment initiated',
                    'redirect_url' => $result['redirectUrl'],
                    'poll_url' => $result['pollUrl'],
                    'reference' => $reference
                ], 200);
            }

            if ($user) {
                $this->walletService->logTransaction($user, $amount, 'credit', 'failed', 'Paynow Top-up Failed: ' . ($result['message'] ?? 'Error'), $reference);
            }

            return response()->json(['success' => "0", 'message' => $result['message']], 200);
        }

        if ($method == 'ecocash') {
            // Short reference format: "EC-{roleId}-{userId}-{shortTimestamp}{random}" to ensure < 20 chars
            $shortTime = substr((string)time(), -5);
            $reference = "EC-{$roleId}-{$userId}-" . $shortTime . rand(10, 99);
            $response = $this->ecocashService->initiateTransaction($amount, $request->phone, $reference);

            if ($response['success']) {
                if ($user) {
                    $this->walletService->logTransaction($user, $amount, 'credit', 'pending', 'EcoCash Top-up Pending', $reference);
                }

                return response()->json([
                    'success' => "1",
                    'message' => $response['message'],
                    'reference' => $reference
                ], 200);
            }

            if ($user) {
                $this->walletService->logTransaction($user, $amount, 'credit', 'failed', 'EcoCash Top-up Failed: ' . ($response['message'] ?? 'Failed'), $reference);
            }

            return response()->json(['success' => "0", 'message' => $response['message']], 200);
        }
    }

    public function verify_payment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference' => 'required|exists:poll_urls,reference'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => "0", 'message' => $validator->errors()->first()], 200);
        }

        $dbTx = \App\Models\PollUrl::where('reference', $request->reference)->first();

        // If transaction is still pending, verify status from Paynow gateway
        if ($dbTx->status === 'pending') {
            if ($this->paymentService->verifyTransaction($dbTx->poll_url)) {
                $roleId = $dbTx->role_id;
                $user = ($roleId == 1) ? Patient::find($dbTx->user_id) : Doctors::find($dbTx->user_id);

                if ($user) {
                    $this->walletService->credit(
                        $user, 
                        $dbTx->amount, 
                        'Wallet Top-up via Paynow', 
                        $dbTx->reference
                    );
                }

                $dbTx->status = 'completed';
                $dbTx->save();
            } else {
                return response()->json([
                    'success' => "1",
                    'status' => 'pending',
                    'message' => 'Payment is still pending verification.'
                ], 200);
            }
        }

        return response()->json([
            'success' => "1",
            'status' => $dbTx->status,
            'message' => $dbTx->status == 'completed' ? 'Payment successful' : 'Payment failed'
        ], 200);
    }
}
