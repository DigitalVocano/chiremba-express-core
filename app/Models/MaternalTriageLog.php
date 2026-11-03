<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaternalTriageLog extends Model
{
    use HasFactory;

    protected $table = 'maternal_triage_logs';

    protected $fillable = [
        'patient_id',
        'maternal_profile_id',
        'recorded_by_chw_id',
        'anonymous_session_token',
        'gestational_weeks',
        'symptoms',
        'systolic_bp',
        'diastolic_bp',
        'temperature',
        'weight_kg',
        'blood_glucose_mmol',
        'risk_level',
        'flagged_conditions',
        'clinical_guidance',
        'urgent_referral_triggered',
        'referral_appointment_id',
        'channel',
        'locale',
    ];

    protected $casts = [
        'symptoms' => 'array',
        'flagged_conditions' => 'array',
        'urgent_referral_triggered' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function maternalProfile()
    {
        return $this->belongsTo(MaternalProfile::class, 'maternal_profile_id');
    }

    public function referralAppointment()
    {
        return $this->belongsTo(BookAppointment::class, 'referral_appointment_id');
    }
}

