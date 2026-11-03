<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\WalletTransaction;
use App\Models\Patient;
use App\Models\Doctors;
use App\Services\WalletService;

class EcocashController extends Controller
{
    public function notify(Request $request)
    {
        Log::info('EcoCash Webhook Notify Received: ' . json_encode($request->all()));

        $status = strtoupper((string)($request->input('transactionOperationStatus') ?? $request->input('status') ?? ''));
        $reference = $request->input('clientCorrelator') ?? $request->input('referenceCode');
        $amount = $request->input('paymentAmount.charginginformation.amount') ?? $request->input('amount') ?? 0;
        $responseCode = $request->input('responseCode') ?? $request->input('responseDescription') ?? $status;

        if ($status === 'COMPLETED' || $status === 'CHARGED' || $status === 'SUCCESS') {
            $parts = explode('-', $reference ?? '');
            if (count($parts) >= 4 && $parts[0] === 'EC') {
                $roleId = $parts[1];
                $userId = $parts[2];

                $user = null;
                if ($roleId == 1) {
                    $user = Patient::find($userId);
                } elseif ($roleId == 2 || $roleId == 3) {
                    $user = Doctors::find($userId);
                }

                if ($user) {
                    $exists = WalletTransaction::where('reference', $reference)->where('status', 'completed')->exists();
                    if (!$exists) {
                        $walletService = app(WalletService::class);
                        $walletService->credit(
                            $user,
                            (float)$amount,
                            'Wallet Top-up via EcoCash',
                            $reference
                        );
                        Log::info("EcoCash transaction $reference credited to user $userId");
                    } else {
                        Log::info("EcoCash transaction $reference already processed");
                    }
                }
            }
        } else {
            $parts = explode('-', $reference ?? '');
            if (count($parts) >= 4 && $parts[0] === 'EC') {
                $roleId = $parts[1];
                $userId = $parts[2];
                $user = ($roleId == 1) ? Patient::find($userId) : Doctors::find($userId);
                if ($user) {
                    $walletService = app(WalletService::class);
                    $walletService->logTransaction(
                        $user,
                        (float)$amount,
                        'credit',
                        'failed',
                        "EcoCash Top-up Failed: " . ($responseCode ?: 'Cancelled by user'),
                        $reference
                    );
                    Log::warning("EcoCash transaction $reference marked failed. Status: $status, Reason: $responseCode");
                }
            }
        }

        return response()->json(['status' => 'OK']);
    }


    public function checkStatus(Request $request)
    {
        $reference = $request->input('reference');
        if (!$reference) {
            return response()->json(['status' => 'pending', 'message' => 'Reference required']);
        }

        $transaction = WalletTransaction::where('reference', $reference)->latest()->first();

        if (!$transaction) {
            return response()->json(['status' => 'pending', 'message' => 'Waiting for EcoCash USSD prompt response...']);
        }

        return response()->json([
            'status' => $transaction->status,
            'amount' => $transaction->amount,
            'description' => $transaction->description,
        ]);
    }
}
