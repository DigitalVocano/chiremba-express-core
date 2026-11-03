<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderProfileView extends Model
{
    use HasFactory;

    protected $table = 'provider_profile_views';

    protected $fillable = [
        'doctor_id',
        'viewer_id',
        'viewer_ip',
        'platform',
        'user_agent',
        'view_date'
    ];

    /**
     * Relationship to Doctor / Provider.
     */
    public function doctor()
    {
        return $this->belongsTo(Doctors::class, 'doctor_id');
    }

    /**
     * Relationship to Patient / Viewer.
     */
    public function viewer()
    {
        return $this->belongsTo(Patient::class, 'viewer_id');
    }
}
