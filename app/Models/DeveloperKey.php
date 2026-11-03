<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DeveloperKey extends Model
{
    use HasFactory;

    protected $table = 'developer_keys';

    protected $fillable = [
        'app_name',
        'developer_name',
        'developer_email',
        'api_key',
        'secret_key',
        'allowed_domains',
        'rate_limit_per_minute',
        'is_active',
        'last_used_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime'
    ];

    /**
     * Generate credentials for a new developer
     */
    public static function generateCredentials($appName, $developerName, $developerEmail, $domains = null)
    {
        return self::create([
            'app_name' => $appName,
            'developer_name' => $developerName,
            'developer_email' => $developerEmail,
            'api_key' => 'ch_live_' . Str::random(32),
            'secret_key' => 'ch_sec_' . Str::random(48),
            'allowed_domains' => $domains,
            'rate_limit_per_minute' => 60,
            'is_active' => true
        ]);
    }
}
