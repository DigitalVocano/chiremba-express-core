<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiCreditPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'credits',
        'price',
        'description',
        'is_active',
    ];
}