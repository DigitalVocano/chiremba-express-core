<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentMedicines extends Model
{
    use HasFactory;
    public function bookAppointment() {
    return $this->belongsTo(BookAppointment::class, 'appointment_id');
}
}
