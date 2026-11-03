<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralReward extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'points_required', 'type', 'value', 'is_active'];
}
