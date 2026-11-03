<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyProduct extends Model
{
    use HasFactory;
    protected $table = 'pharmacy_product';
    protected $primaryKey = 'id';

    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }

    public function master_medicine()
    {
        return $this->hasOne(Medicines::class, 'id', 'medicine_id');
    }

    public function pharmacy()
    {
        return $this->belongsTo(Doctors::class, 'pharmacy_id', 'id');
    }
}
