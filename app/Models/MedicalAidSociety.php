<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalAidSociety extends Model
{
    use HasFactory;

    protected $table = 'medical_aid_societies';

    protected $fillable = [
        'name',
        'code',
        'logo',
        'description',
        'is_active'
    ];

    public function doctors()
    {
        return $table = $this->belongsToMany(Doctors::class, 'doctor_medical_aid', 'medical_aid_society_id', 'doctor_id');
    }

    public function pharmacies()
    {
        return $this->belongsToMany(Doctors::class, 'pharmacy_medical_aid', 'medical_aid_society_id', 'pharmacy_id');
    }
}
