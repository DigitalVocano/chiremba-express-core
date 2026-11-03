<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientSubscriber extends Model
{
    protected $table = 'patient_subscriber';
    protected $primaryKey = 'id';

        
    public function Patient(){
        return $this->hasone("App\Models\Patient",'id','patient_id');
    }

    public function patient_subscription(){
        return $this->hasone("App\Models\PatientSubscription",'id','patient_subscription_id');
    }

}
?>