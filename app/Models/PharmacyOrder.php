<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyOrder extends Model
{
    use HasFactory;
    protected $table = 'pharmacy_order';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'Pharmacy_id',
        'pharmacy_id',
        'user_id',
        'total',
        'payment_type',
        'is_completed',
        'order_type',
        'status',
        'prescription',
        'phone',
        'recipient_name',
        'recipient_phone',
        'delivery_mode',
        'pointlink_ride_id',
        'message',
        'address',
        'tax',
        'delivery_charge',
        'transaction_id',
        'is_discreet_packaging',
        'discreet_verification_pin',
        'anonymous_session_token',
        'srhr_category',
    ];

    protected $casts = [
        'is_discreet_packaging' => 'boolean',
    ];

    /**
     * Get the pharmacy that owns the order.
     */
    public function pharmacy()
    {
        return $this->belongsTo(Doctors::class, 'Pharmacy_id');
    }

    /**
     * Get the patient (user) that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(Patient::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(PharmacyOrderData::class, 'order_id');
    }

    public function dataList()
    {
        return $this->hasMany(PharmacyOrderData::class, 'order_id');
    }

    public function medicine()
    {
        return $this->hasMany(PharmacyOrderData::class, 'order_id');
    }
}
