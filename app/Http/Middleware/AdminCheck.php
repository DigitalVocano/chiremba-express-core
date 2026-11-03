<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AdminCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // First, check if a user is already authenticated via Laravel's default guard.
        if (Auth::guard('web')->check()) {
            return $next($request);
        }

        // If not, check for the session data that might have been set by another auth system (like Sentinel).
        if (Session::has('admin.id')) {
            $adminId = Session::get('admin.id');
            
            // Attempt to log the user in for this request using their ID.
            // This makes the user available via auth()->user() in subsequent middleware and controllers.
            if (Auth::guard('web')->loginUsingId($adminId)) {
                return $next($request);
            }
        }

        // Check for persistent remember cookie if session expired
        if (!Session::has('admin.id') && isset($_COOKIE['admin_remember_token']) && isset($_COOKIE['remember_me']) && $_COOKIE['remember_me'] == 1) {
            try {
                $tokenData = explode(':', base64_decode($_COOKIE['admin_remember_token']));
                if (count($tokenData) == 2) {
                    $userId = $tokenData[0];
                    $passHash = $tokenData[1];
                    $userModel = \App\Models\User::find($userId);
                    if ($userModel && sha1($userModel->password) === $passHash) {
                        Session::put('admin', [
                            'id' => $userModel->id,
                            'name' => $userModel->first_name,
                            'profile_pic' => $userModel->profile_pic
                        ]);
                        Auth::guard('web')->loginUsingId($userModel->id);
                        return $next($request);
                    }
                }
            } catch (\Exception $e) {}
        }

        // If no user is authenticated and no valid session is found, redirect to the admin login page.
        return redirect('admin');
    }
}
