<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SrhrInquiry extends Model
{
    use HasFactory;

    protected $table = 'srhr_inquiries';

    protected $fillable = [
        'anonymous_session_token',
        'patient_id',
        'topic_category',
        'question',
        'guidance_response',
        'who_guideline_ref',
        'recommended_supplies',
        'locale',
    ];

    protected $casts = [
        'recommended_supplies' => 'array',
    ];
}

