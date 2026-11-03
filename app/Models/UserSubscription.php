<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'user_type',
        'subscription_id',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Get the parent user model (Patient, Doctor, etc.).
     */
    public function user()
    {
        return $this->morphTo();
    }

    /**
     * Get the subscription plan.
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}