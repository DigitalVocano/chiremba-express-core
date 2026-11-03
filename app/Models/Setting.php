<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'setting';
    protected $primaryKey = 'id';

    public static function getValue($key, $default = null)
    {
        $settings = self::first();
        if ($settings && isset($settings->{$key})) {
            return $settings->{$key};
        }
        return $default;
    }
}
?>