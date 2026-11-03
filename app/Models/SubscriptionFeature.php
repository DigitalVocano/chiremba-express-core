<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionFeature extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'subscription_id',
        'name',
        'code',
        'value',
        'description',
        'sort_order',
    ];

    /**
     * Get the subscription plan that owns the feature.
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}