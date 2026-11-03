<?php

namespace App\Providers;

use Illuminate\Support\Facades\View; // Import the View facade
use Illuminate\Support\ServiceProvider;
use App\Models\Setting; // Import your Setting model

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Share common settings with all views that use 'front.*' prefix
        // This assumes your main frontend views are within a 'front' directory.
        // Adjust 'front.*' if your frontend views are in a different location (e.g., '*' for all views, or 'layouts.app' for a specific layout).
        View::composer('front.*', function ($view) {
            // Fetch the single settings row.
            // Using firstOrNew() as a fallback if the table is completely empty,
            // though it's assumed there will be at least one row for existing settings.
            $settings = Setting::firstOrNew([]);

            $view->with('googleTagManagerId', $settings->google_tag_manager_id ?? null);
            $view->with('doctorContactVisibility', $settings->doctor_contact_visibility ?? '1'); // Default to '1' (show)
        });

        // If your admin panel also needs access to these settings in its views,
        // you can add another View Composer for 'admin.*' or specific admin layouts.
        // For example:
        // View::composer('admin.*', function ($view) {
        //     $settings = Setting::firstOrNew([]);
        //     $view->with('googleTagManagerId', $settings->google_tag_manager_id ?? null);
        //     $view->with('doctorContactVisibility', $settings->doctor_contact_visibility ?? '1');
        // });
    }
}