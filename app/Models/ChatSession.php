<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'status',
    ];

    /**
     * Get the patient that owns the chat session.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the messages for the chat session.
     */
    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}