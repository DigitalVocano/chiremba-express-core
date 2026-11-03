<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalAidSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'owner_type',
        'medical_aid_provider',
        'policy_number',
        'amount_claimed',
        'proof_document_path',
        'admin_notes',
        'status',
        'approved_amount',
        'submitted_at',
        'processed_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->morphTo();
    }
}