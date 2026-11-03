<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileDiagnosticLog extends Model
{
    protected $table = 'mobile_diagnostic_logs';
    protected $guarded = [];

    protected $casts = [
        'is_resolved' => 'boolean',
    ];
}

