<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'estimated_min_value',
        'estimated_max_value',
    ];

    public function submissions()
    {
        return $this->hasMany(AssetSubmission::class);
    }
}