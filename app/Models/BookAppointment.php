<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookAppointment extends Model
{
    protected $table = 'book_appointment';
    protected $primaryKey = 'id';

    /**
     * Get the doctor that the appointment belongs to.
     */
    public function doctorls()
    {
        return $this->belongsTo(Doctors::class, 'doctor_id');
    }

    /**
     * Get the patient that the appointment belongs to.
     */
    public function patientls()
    {
        return $this->belongsTo(Patient::class, 'user_id');
    }

    /**
     * Accessor to return patient real name if appointment name field is empty or generic.
     */
    public function getNameAttribute($value)
    {
        if (empty($value) || strtolower(trim($value)) == 'patient') {
            return $this->patientls->name ?? 'Patient';
        }
        return $value;
    }

    /**
     * Get the pivot records for the medicines associated with this appointment.
     */
    public function appointmentMedicines()
    {
        return $this->hasMany(AppointmentMedicines::class, 'appointment_id');
     }

    /**
     * Get all of the medicines for the appointment through the pivot table.
     */
    public function medicines()
    {
        return $this->belongsToMany(Medicines::class, 'appointment_medicines', 'appointment_id', 'medicine_id');
    }
}