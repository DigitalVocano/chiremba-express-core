<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    protected $fillable = ['patient_id','visibility','record_type','document_name', 'document_path'];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function appointment()
{
    return $this->belongsTo(BookAppointment::class, 'appointment_id');
}
public function appointmentMedicines()
{
    return $this->belongsTo(AppointmentMedicines::class, 'appointment_id'); // Assuming 'appointment_id' is the foreign key in the medical_records table
}
}

