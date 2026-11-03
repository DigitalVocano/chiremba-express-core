<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EpdsScreening extends Model
{
    use HasFactory;

    protected $table = 'epds_screenings';

    protected $fillable = [
        'patient_id',
        'anonymous_session_token',
        'recorded_by_chw_id',
        'perinatal_stage',
        'responses',
        'total_score',
        'self_harm_item_score',
        'risk_severity',
        'counselor_referral_surfaced',
        'support_hotlines',
        'locale',
    ];

    protected $casts = [
        'responses' => 'array',
        'counselor_referral_surfaced' => 'boolean',
        'support_hotlines' => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}

