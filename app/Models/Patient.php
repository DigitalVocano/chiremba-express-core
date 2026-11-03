<?php
namespace App\Models; 

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Patient extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'patient';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'google_id', 'phone', 'profile_pic', 'connectycube_user_id', 'login_id', 'connectycube_password', 'is_patient_subscription',
        'referral_code', 'referred_by_code', 'referral_points'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->referral_code)) {
                $model->referral_code = self::generateReferralCode();
            }
        });
    }

    /**
     * Generate a unique referral code.
     *
     * @return string
     */
    public static function generateReferralCode()
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('referral_code', $code)->exists() || \App\Models\Doctors::where('referral_code', $code)->exists());

        return $code;
    }

    /**
     * Set the password attribute, always hashing it.
     *
     * @param  string  $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
        }
    }

    public function wallet()
    {
        return $this->morphOne(Wallet::class, 'owner');
    }

    public function appointments()
    {
        return $this->hasMany(BookAppointment::class, 'user_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'patient_id');
    }

    public function referrals()
    {
        return $this->morphMany(Referral::class, 'referrer');
    }

    /**
     * Get all of the subscriptions for the user.
     * This defines the polymorphic relationship to the new subscription system.
     */
    public function subscriptions()
    {
        return $this->morphMany(Subscriber::class, 'subscriber');
    }

    /**
     * Get the user's current active subscription.
     * The result is cached for 10 minutes to improve performance.
     *
     * @return \App\Models\Subscriber|null
     */
    public function activeSubscription()
    {
        return Cache::remember('active_subscription_for_patient_' . $this->id, 600, function () {
            // Use the new polymorphic relationship to get the active subscription
            // and eager load the subscription plan with its features.
            return $this->subscriptions()
                ->with('subscription.features')
                ->where('status', '2') // Active
                ->latest('id')
                ->first();
        });
    }

    /**
     * Check if the user has a specific feature via their active subscription.
     *
     * @param string $featureCode The code of the feature to check for.
     * @return bool
     */
    public function hasFeature(string $featureCode): bool
    {
        $activeSubscription = $this->activeSubscription();

        // Check if there is an active subscription and if the related subscription plan exists.
        if (!$activeSubscription || !$activeSubscription->subscription) {
            return false;
        }

        // Check if the subscription's features collection contains the feature code.
        return $activeSubscription->subscription->features->contains('code', $featureCode);
    }

    /**
     * Get the value of a specific feature from the user's active subscription.
     *
     * @param string $featureCode The code of the feature.
     * @param mixed $default The default value to return if the feature is not found.
     * @return mixed
     */
    public function getFeatureValue(string $featureCode, $default = null)
    {
        $activeSubscription = $this->activeSubscription();

        // Check if there is an active subscription and if the related subscription plan exists.
        if (!$activeSubscription || !$activeSubscription->subscription) {
            return $default;
        }

        // Find the specific feature in the collection.
        $feature = $activeSubscription->subscription->features->firstWhere('code', $featureCode);
        return $feature ? $feature->value : $default;
    }
}
