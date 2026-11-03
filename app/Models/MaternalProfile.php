<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaternalProfile extends Model
{
    use HasFactory;

    protected $table = 'maternal_profiles';

    protected $fillable = [
        'patient_id',
        'anonymous_session_token',
        'registered_by_chw_id',
        'lmp_date',
        'edd_date',
        'gestational_weeks',
        'gravida',
        'para',
        'blood_group',
        'high_risk_flag',
        'risk_factors',
        'preferred_language',
        'sms_reminders_enabled',
    ];

    protected $casts = [
        'lmp_date' => 'date',
        'edd_date' => 'date',
        'high_risk_flag' => 'boolean',
        'risk_factors' => 'array',
        'sms_reminders_enabled' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function triageLogs()
    {
        return $this->hasMany(MaternalTriageLog::class, 'maternal_profile_id');
    }
}

