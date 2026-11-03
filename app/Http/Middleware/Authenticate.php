<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            $path = $request->path(); // Get the full path (e.g., 'doctordashboard', 'admin/dashboard')

            // Redirect to admin login if the first segment is 'admin'
            if (Str::startsWith($path, 'admin')) {
                return route('admin.login');
            }

            // Redirect to doctor login if the path starts with 'doctor' or 'doctordashboard'
            if (Str::startsWith($path, 'doctor') || Str::startsWith($path, 'doctordashboard')) {
                return route('doctor.login');
            }

            // Redirect to pharmacy login if the path starts with 'pharmacy' or 'pharmacydashboard'
            if (Str::startsWith($path, 'pharmacy') || Str::startsWith($path, 'pharmacydashboard')) {
                return route('doctor.login'); // Assuming pharmacy login also uses the doctor.login route
            }

            // Default to patient login, which we will name 'login'
            return route('login');
        }
    }
}
