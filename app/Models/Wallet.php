<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'owner_type',
        'balance',
    ];

    /**
     * Get the owning model (Patient, Doctors).
     */
    public function owner()
    {
        return $this->morphTo();
    }

    /**
     * Get the wallet transactions for the wallet.
     */
    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Credits the wallet with a given amount and records a transaction.
     *
     * @param float $amount
     * @param string $description
     * @param string|null $reference
     * @return WalletTransaction
     */
    public function credit(float $amount, string $description, ?string $reference = null): WalletTransaction
    {
        $this->increment('balance', $amount);
        $this->save();

        return $this->transactions()->create(['amount' => $amount, 'type' => 'credit', 'description' => $description, 'reference' => $reference]);
    }
}
