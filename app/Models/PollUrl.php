<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PollUrl extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'poll_url',
        'amount',
        'user_id',
        'role_id',
        'status',
        'meta_data',
    ];
}
