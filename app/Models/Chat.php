<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'appointment_id',
        'message',
        'msg_type', // Add this line for file support
    ];

    // Relationship to the BookAppointment model
    public function appointment()
    {
        return $this->belongsTo(BookAppointment::class, 'appointment_id');
    }

    // Relationship to the User model (sender)
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Relationship to the User model (receiver)
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}