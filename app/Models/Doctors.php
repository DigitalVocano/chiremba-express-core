<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\PharmacyProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
class Doctors extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'doctors';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'google_id', 'phoneno', 'department_id',
        'aboutus', 'services', 'healthcare', 'address', 'lat', 'lon',
        'consultation_fees', 'working_time', 'image', 'facebook_url', 'twitter_url',
        'is_approve', 'profile_type', 'slug', 'connectycube_user_id', 'login_id', 'connectycube_password',
        'referral_code', 'referred_by_code', 'referral_points', 'featured', 'featured_until',
        'accept_cod', 'accept_digital_payments', 'delivery_mode', 'self_delivery_fee',
        'has_own_bikes', 'accept_pointlink_deliveries'
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
    public static function boot()
    {
        parent::boot();

        static::creating(function ($doctor) {
            // Generate unique slug on creation
            if (empty($doctor->slug)) {
                $slug = Str::slug($doctor->name);
                $originalSlug = $slug;
                $i = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $i++;
                }
                $doctor->slug = $slug;
            }

            // Generate referral code on creation
            if (empty($doctor->referral_code)) {
                $doctor->referral_code = self::generateReferralCode();
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
        } while (self::where('referral_code', $code)->exists() || \App\Models\Patient::where('referral_code', $code)->exists());

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

    /**
     * Get the department (service) for the doctor.
     */
    public function department()
    {
        return $this->belongsTo(\App\Models\Services::class, 'department_id');
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function wallet()
    {
        return $this->morphOne(Wallet::class, 'owner');
    }

    /**
     * Get the reviews for the doctor.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'doc_id');
    }

    /**
     * Get the appointments for the doctor.
     */
    public function appointments()
    {
        return $this->hasMany(BookAppointment::class, 'doctor_id');
    }

    /**
     * Get the referrals made by this user.
     */
    public function referrals()
    {
        return $this->morphMany(Referral::class, 'referrer');
    }

    /**
     * Get all of the subscriptions for the user.
     */
    public function subscriptions()
    {
        return $this->morphMany(\App\Models\Subscriber::class, 'subscriber');
    }

    /**
     * Get the user's current active subscription.
     * The result is cached for 10 minutes to improve performance.
     *
     * @return \App\Models\Subscriber|null
     */
    public function activeSubscription()
    {
        return Cache::remember('active_subscription_for_doctor_' . $this->id, 600, function () {
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
     * Check if subscription is required for this doctor or pharmacy based on admin settings.
     */
    public function isSubscriptionRequired(): bool
    {
        $setting = Setting::first();
        if ($this->profile_type == '2') {
            return $setting ? (bool)($setting->is_pharmacy_subscription_enabled ?? true) : true;
        }
        return $setting ? (bool)($setting->is_doctor_subscription_enabled ?? true) : true;
    }

    /**
     * Check if doctor or pharmacy has active subscription or if global subscription requirement is disabled.
     */
    public function isSubscribedOrGlobalDisabled(): bool
    {
        if (!$this->isSubscriptionRequired()) {
            return true;
        }

        $activeSub = \App\Models\Subscriber::where('subscriber_id', $this->id)->where(function($q) { $q->where('subscriber_type', self::class)->orWhereNull('subscriber_type'); })
            ->where('status', '2')
            ->latest('id')
            ->first();

        if ($activeSub && isset($activeSub->subscription) && is_numeric($activeSub->subscription->duration)) {
            $expiryDate = (new \DateTime($activeSub->date)) > new \DateTime() ? new \DateTime($activeSub->date) : (new \DateTime($activeSub->date))->modify('+' . $activeSub->subscription->duration . ' ' . \Illuminate\Support\Str::plural($activeSub->subscription->duration_unit));
            if ($expiryDate > new \DateTime()) {
                return true;
            }
        }

        return false;
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

        if (!$activeSubscription || !$activeSubscription->subscription) {
            return false;
        }

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

        if (!$activeSubscription || !$activeSubscription->subscription) {
            return $default;
        }

        $feature = $activeSubscription->subscription->features->firstWhere('code', $featureCode);
        return $feature ? $feature->value : $default;
    }

    /**
     * Get the products for the pharmacy.
     */
    public function pharmacyProducts()
    {
        return $this->hasMany(PharmacyProduct::class, 'pharmacy_id');
    }

    /**
     * Get the schedules for the doctor.
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'doctor_id');
    }

    /**
     * Get the holidays for the doctor.
     */
    public function holidays()
    {
        return $this->hasMany(\App\Models\Doctor_Hoilday::class, 'doctor_id');
    }

    /**
     * Get accepted medical aid societies for doctor or pharmacy.
     */
    public function medicalAids()
    {
        $pivotTable = $this->profile_type == '2' ? 'pharmacy_medical_aid' : 'doctor_medical_aid';
        $foreignKey = $this->profile_type == '2' ? 'pharmacy_id' : 'doctor_id';
        return $this->belongsToMany(MedicalAidSociety::class, $pivotTable, $foreignKey, 'medical_aid_society_id');
    }
}