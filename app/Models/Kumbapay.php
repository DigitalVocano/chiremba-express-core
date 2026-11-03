<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kumbapay extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'owner_type',
        'identity',
        'prescription',
        'fullname',
        'phone',
        'address',
        'professionaljob',
        'loanamount',
        'status', // Assuming status is part of fillable
    ];

    public function owner()
    {
        return $this->morphTo();
    }
}