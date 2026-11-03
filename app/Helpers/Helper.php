<?php

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class Helper
{
    /**
     * Get the site-wide short date format from settings.
     *
     * @return string
     */
    public static function getShortFormat(): string
    {
        // Cache the setting to avoid hitting the database on every request.
        return Cache::rememberForever('site_date_format', function () {
            try {
                $setting = Setting::find(1);
                // Assumes the column in the settings table is 'date_format'.
                return $setting->date_format ?? 'd M, Y';
            } catch (\Exception $e) {
                return 'd M, Y'; // Fallback in case of DB error
            }
        });
    }

    /**
     * Generate initials from a name (e.g., 'Chipato T' -> 'CT', 'Dr. John Doe' -> 'JD')
     *
     * @param string|null $name
     * @param int $length
     * @return string
     */
    public static function getInitials(?string $name, int $length = 2): string
    {
        if (empty($name)) {
            return 'CE';
        }

        // Clean honorific prefixes like Dr., Dr, Mr., Mrs., Prof., etc. if multiple words exist
        $words = preg_split('/\s+/', trim($name));
        if (count($words) > 1 && in_array(strtolower(rtrim($words[0], '.')), ['dr', 'mr', 'mrs', 'ms', 'prof', 'doc', 'pharm'])) {
            array_shift($words);
        }

        $initials = '';
        foreach ($words as $w) {
            $clean = preg_replace('/[^a-zA-Z0-9]/', '', $w);
            if (!empty($clean)) {
                $initials .= strtoupper(substr($clean, 0, 1));
            }
        }

        if (empty($initials)) {
            $clean = preg_replace('/[^a-zA-Z0-9]/', '', $name);
            $initials = strtoupper(substr($clean, 0, $length));
        }

        return substr($initials, 0, $length) ?: 'CE';
    }
}