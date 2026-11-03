<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalAidProvider extends Model
{
    use HasFactory;

    protected $table = 'medical_aid_providers';

    protected $fillable = [
        'name',
        'logo',
        'description',
    ];
}