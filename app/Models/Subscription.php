<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;
    protected $table = 'subscriptions';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'price',
        'duration',
        'duration_unit',
        'user_type',
        'description',
        'is_active',
    ];

    /**
     * Get the features for the subscription plan.
     */
    public function features()
    {
        return $this->hasMany(SubscriptionFeature::class);
    }

    /**
     * Get the user subscriptions for the plan.
     */
    public function userSubscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }
}?>