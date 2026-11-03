<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'owner_type',
        'asset_type_id',
        'serial_number',
        'description',
        'image_paths',
        'status',
        'approved_value',
        'admin_notes',
        'submitted_at',
        'processed_at',
    ];

    protected $casts = [
        'image_paths' => 'array',
        'submitted_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->morphTo();
    }

    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }
}