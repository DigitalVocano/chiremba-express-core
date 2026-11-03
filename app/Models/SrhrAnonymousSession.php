<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SrhrAnonymousSession extends Model
{
    use HasFactory;

    protected $table = 'srhr_anonymous_sessions';

    protected $fillable = [
        'session_token',
        'alias_code',
        'age_bracket',
        'preferred_language',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function inquiries()
    {
        return $this->hasMany(SrhrInquiry::class, 'anonymous_session_token', 'session_token');
    }
}

