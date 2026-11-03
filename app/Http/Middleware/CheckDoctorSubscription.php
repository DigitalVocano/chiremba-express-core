<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Subscriber;
use App\Models\Setting;
use App\Models\Doctors;
use DateTime;
use Illuminate\Support\Str;

class CheckDoctorSubscription
{
    public function handle(Request $request, Closure $next)
    {
        $allowedRoutes = [
            'doctor.subscription_plan',
            'doctor.wallet',
            'doctor.subscribe.wallet',
            'doctor.my_subscription',
            'doctor.buy_credits',
            'doctor.purchase_credits.wallet',
            'wallet.topup.initiate',
            'doctor.logout',
        ];

        if ($request->route() && in_array($request->route()->getName(), $allowedRoutes)) {
            return $next($request);
        }

        $setting = Setting::first();
        if (!$setting || !$setting->is_doctor_subscription_enabled) {
            return $next($request);
        }

        if (!Session::has('user_id') || Session::get('role_id') != '2') {
            return $next($request);
        }

        $doctorId = Session::get('user_id');

        $subscription = Subscriber::with('subscription')
            ->where('subscriber_id', $doctorId)
            ->where(function ($q) {
                $q->where('subscriber_type', Doctors::class)
                  ->orWhereNull('subscriber_type');
            })
            ->where('status', '2')
            ->latest('id')
            ->first();

        if ($subscription && !empty($subscription->date)) {
            $subDate = new DateTime($subscription->date);
            $now = new DateTime();
            if ($subDate > $now) {
                return $next($request);
            }
            if (isset($subscription->subscription) && is_numeric($subscription->subscription->duration)) {
                $expiryDate = (clone $subDate)->modify('+' . $subscription->subscription->duration . ' ' . Str::plural($subscription->subscription->duration_unit));
                if ($expiryDate > $now) {
                    return $next($request);
                }
            }
        }

        return redirect()->route('doctor.subscription_plan')
            ->with('message', __('Your subscription has expired. Please choose a plan to continue.'))
            ->with('alert-class', 'alert-danger');
    }
}
