<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenData extends Model
{
    protected $table = 'savetoken';
    protected $primaryKey = 'id';
    protected $fillable = [
        'token',
        'type',
        'user_id',
        'doctor_id',
        'platform',
        'apns_token',
        'device_model',
        'app_version',
        'permission_status',
    ];
}
