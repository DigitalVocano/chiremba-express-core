<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChildVaccineMilestone extends Model
{
    use HasFactory;

    protected $table = 'child_vaccine_milestones';

    protected $fillable = [
        'child_profile_id',
        'vaccine_code',
        'vaccine_name',
        'target_age_weeks',
        'due_date',
        'administered_date',
        'status',
        'weight_kg',
        'height_cm',
        'muac_cm',
        'nutrition_checkin_notes',
        'reminder_sent_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'administered_date' => 'date',
        'reminder_sent_at' => 'datetime',
    ];

    public function childProfile()
    {
        return $this->belongsTo(ChildProfile::class, 'child_profile_id');
    }
}

