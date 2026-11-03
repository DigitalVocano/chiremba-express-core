<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChildProfile extends Model
{
    use HasFactory;

    protected $table = 'child_profiles';

    protected $fillable = [
        'mother_patient_id',
        'registered_by_chw_id',
        'child_name',
        'date_of_birth',
        'sex',
        'birth_weight_kg',
        'birth_length_cm',
        'feeding_mode',
        'guardian_phone',
        'preferred_language',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function mother()
    {
        return $this->belongsTo(Patient::class, 'mother_patient_id');
    }

    public function vaccineMilestones()
    {
        return $this->hasMany(ChildVaccineMilestone::class, 'child_profile_id')->orderBy('target_age_weeks');
    }
}

