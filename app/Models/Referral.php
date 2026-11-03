<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;
    protected $fillable = ['referrer_id', 'referrer_type', 'referred_id', 'referred_type', 'points_awarded', 'status'];

    public function referrer()
    {
        return $this->morphTo();
    }

    public function referred()
    {
        return $this->morphTo();
    }
}
