<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RedeemedReward extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'user_type', 'referral_reward_id', 'points_used', 'status', 'admin_notes'];

    public function user()
    {
        return $this->morphTo();
    }

    public function reward()
    {
        return $this->belongsTo(ReferralReward::class, 'referral_reward_id');
    }
}
