<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthRecord extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'patient_id',
        'date',
        'blood_pressure',
        'weight',
        'temperature',
        'blood_glucose_level',
        'blood_pressure_systolic',
        'blood_pressure_diastolic',
        'heart_rate',
        'hrv',
        'blood_oxygen_saturation',
        'ecg_data',
        'sleep_data',
        'steps',
        'distance',
        'calories_burned',
        'fall_detected',
        'stress_level',
        'latitude',
        'longitude',
        'glucose_level',
        'gestational_weeks',
        'fetal_heart_rate',
        'fundal_height_cm',
        'recorded_by_role',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
