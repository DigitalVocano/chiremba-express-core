<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientSubscription extends Model
{
    protected $table = 'patient_subscription';
    protected $primaryKey = 'id';

    public function getNameAttribute()
    {
        switch ($this->id) {
            case 1:
                return 'Free Plan';
            case 3:
                return 'Mbada Plan';
            case 4:
                return 'Utano plan';
            case 5:
                return 'Travellers Patient plan';
            default:
                return 'Unknown Plan'; // Or handle the default case as needed
        }
    }
}?>