<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Subscriber;
use App\Models\Setting;
use App\Models\Patient;
use DateTime;
use Illuminate\Support\Str;

class CheckPatientSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // First, check if the global subscription system for patients is enabled
        $setting = Setting::first();
        if (!$setting || !$setting->is_patient_subscription_enabled) {
            return $next($request); // If disabled, bypass the check
        }

        // Check if a patient is logged in
        if (!Session::has('user_id') || Session::get('role_id') != '1') {
            return $next($request); // Not a patient, bypass
        }

        $patientId = Session::get('user_id');

        // Find the latest subscription for the patient
        $subscription = Subscriber::where('subscriber_id', $patientId)
            ->where('subscriber_type', Patient::class)
            ->where('status', '2') // Active status
            ->latest('id')
            ->first();

        // The 'date' column now stores the expiry date. Check if it's in the future.
        if ($subscription && (new DateTime($subscription->date) > new DateTime())) {
            return $next($request); // Subscription is active, proceed.
        }

        // If no active subscription is found, redirect to the patient subscription plans page.
        return redirect()->route('patient.subscription_plan')->with('message', __('Your subscription has expired. Please choose a plan to continue.'))->with('alert-class', 'alert-danger');
    }
}