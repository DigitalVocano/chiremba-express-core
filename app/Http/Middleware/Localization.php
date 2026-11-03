<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

class Localization
{
    /**
     * Supported locales including Zimbabwean indigenous languages (Shona & Ndebele).
     */
    protected array $supportedLocales = ['en', 'sn', 'nr', 'fr', 'es', 'pt', 'ar'];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $requestedLocale = $request->query('lang')
            ?: $request->header('X-Locale')
            ?: Session::get('locale')
            ?: Session::get('lacale');

        if ($requestedLocale && in_array($requestedLocale, $this->supportedLocales, true)) {
            App::setLocale($requestedLocale);
            Session::put('locale', $requestedLocale);
            Session::put('lacale', $requestedLocale);
            return $next($request);
        }

        try {
            if (Schema::hasTable('setting')) {
                $setting = Setting::find(1);
                if ($setting && !empty($setting->lang_code)) {
                    App::setLocale($setting->lang_code);
                    Session::put('locale', $setting->lang_code);
                    Session::put('lacale', $setting->lang_code);
                    return $next($request);
                }
            }
        } catch (\Throwable $e) {
            // Fallback to English if database is not yet initialized
        }

        App::setLocale('en');
        Session::put('locale', 'en');
        Session::put('lacale', 'en');

        return $next($request);
    }
}
