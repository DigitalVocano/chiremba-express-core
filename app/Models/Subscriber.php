<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    protected $table = 'subscriber';
    protected $primaryKey = 'id';

    protected $guarded = [];

    public function subscriber()
    {
        return $this->morphTo();
    }

    public function doctors()
    {
        return $this->hasOne("App\Models\Doctors", 'id', 'doctor_id');
    }

    public function provider()
    {
        return $this->belongsTo("App\Models\Doctors", 'subscriber_id', 'id');
    }

    public function subscription()
    {
        return $this->hasOne("App\Models\Subscription", 'id', 'subscription_id');
    }
}
