<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChwProxyIntake extends Model
{
    use HasFactory;

    protected $table = 'chw_proxy_intakes';

    protected $fillable = [
        'offline_uuid',
        'chw_user_id',
        'chw_name',
        'chw_location_ward',
        'patient_id',
        'patient_full_name',
        'patient_phone',
        'patient_age',
        'is_pregnant',
        'gestational_weeks',
        'vital_signs',
        'symptoms',
        'triage_risk_level',
        'triage_summary',
        'appointment_id',
        'synced_from_offline',
        'client_recorded_at',
    ];

    protected $casts = [
        'is_pregnant' => 'boolean',
        'vital_signs' => 'array',
        'symptoms' => 'array',
        'synced_from_offline' => 'boolean',
        'client_recorded_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function appointment()
    {
        return $this->belongsTo(BookAppointment::class, 'appointment_id');
    }
}

