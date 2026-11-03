<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class WalletService
{
    /**
     * Credit a user's wallet.
     *
     * @param mixed $user The user model (Patient, Doctor, Pharmacy)
     * @param float $amount
     * @param string $description
     * @param string|null $reference
     * @return Wallet
     * @throws Exception
     */
    public function credit($user, float $amount, string $description, string $reference = null)
    {
        return DB::transaction(function () use ($user, $amount, $description, $reference) {
            // Ensure wallet exists
            $wallet = $user->wallet()->firstOrCreate(['balance' => 0]);

            // Check for duplicate transaction by reference if provided
            if ($reference && WalletTransaction::where('reference', $reference)->exists()) {
                Log::warning("Duplicate wallet credit attempt blocked. Reference: {$reference}");
                return $wallet;
            }

            $wallet->increment('balance', $amount);

            $txData = [
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'type' => 'credit',
                'description' => $description,
                'reference' => $reference,
            ];
            if ($this->hasStatusColumn()) {
                $txData['status'] = 'completed';
            }
            WalletTransaction::create($txData);

            return $wallet;
        });
    }

    /**
     * Debit a user's wallet.
     *
     * @param mixed $user
     * @param float $amount
     * @param string $description
     * @param string|null $reference
     * @return Wallet
     * @throws Exception
     */
    public function debit($user, float $amount, string $description, string $reference = null)
    {
        return DB::transaction(function () use ($user, $amount, $description, $reference) {
            $wallet = $user->wallet()->firstOrCreate(['balance' => 0]);

            if ($wallet->balance < $amount) {
                throw new Exception("Insufficient wallet balance.");
            }

            $wallet->decrement('balance', $amount);

            $txData = [
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'type' => 'debit',
                'description' => $description,
                'reference' => $reference,
            ];
            if ($this->hasStatusColumn()) {
                $txData['status'] = 'completed';
            }
            WalletTransaction::create($txData);

            return $wallet;
        });
    }

    /**
     * Log a wallet transaction record with a specific status (completed, failed, pending).
     *
     * @param mixed $user
     * @param float $amount
     * @param string $type ('credit', 'debit')
     * @param string $status ('completed', 'failed', 'pending')
     * @param string $description
     * @param string|null $reference
     * @return WalletTransaction
     */
    public function logTransaction($user, float $amount, string $type, string $status, string $description, string $reference = null)
    {
        $wallet = $user->wallet()->firstOrCreate(['balance' => 0]);

        $txData = [
            'wallet_id' => $wallet->id,
            'amount' => $amount,
            'type' => $type,
            'description' => $description,
            'reference' => $reference,
        ];
        if ($this->hasStatusColumn()) {
            $txData['status'] = $status;
        }

        return WalletTransaction::create($txData);
    }

    protected function hasStatusColumn(): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn('wallet_transactions', 'status');
        } catch (\Throwable $e) {
            return false;
        }
    }
}
