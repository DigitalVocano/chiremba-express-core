<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    protected $table = 'services';
    protected $primaryKey = 'id';
    
    public function doctors() {  // Add this relationship!
    return $this->hasMany(Doctors::class, 'department_id');
}  }
?>