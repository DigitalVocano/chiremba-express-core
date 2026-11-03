<?php

namespace App\Services;

use App\Models\Subscriber;
use App\Models\Subscription;
use App\Models\Patient;
use App\Models\Doctors;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class SubscriptionService
{
    /**
     * Create a new subscription for a user.
     *
     * @param mixed $user The user model (Patient or Doctors).
     * @param Subscription $plan The subscription plan model.
     * @param int $paymentType 0 for Wallet, etc.
     * @param string|null $transactionId
     * @return Subscriber
     */
    public function subscribe($user, Subscription $plan, $paymentType = 0, $transactionId = null)
    {
        // Mark any previous active subscriptions for this user as expired (status = 3)
        Subscriber::where('subscriber_id', $user->id)
            ->where('subscriber_type', get_class($user))
            ->where('status', '2')
            ->update(['status' => '3']);

        $subscriber = new Subscriber();
        $subscriber->subscriber_id = $user->id;
        $subscriber->subscriber_type = get_class($user);
        if ($user instanceof Doctors) {
            $subscriber->doctor_id = $user->id;
        }
        $subscriber->subscription_id = $plan->id;
        $subscriber->payment_type = $paymentType;
        $subscriber->amount = $plan->price;

        // Calculate expiry date
        $expiryDate = now()->add(Str::plural($plan->duration_unit), $plan->duration);
        $subscriber->date = $expiryDate;

        $subscriber->transaction_id = $transactionId ?? 'wallet_' . Uuid::uuid4()->toString();
        $subscriber->is_complet = 1;
        $subscriber->status = "2"; // Active

        // Handle AI Credits Logic (Different for Patients vs Doctors/Pharmacies)
        if ($user instanceof Patient) {
            if (isset($plan->ai_credits) && $plan->ai_credits > 0) {
                $wallet = $user->wallet()->firstOrCreate(['balance' => 0]);
                $wallet->increment('ai_credits', $plan->ai_credits);
            }
        } elseif ($user instanceof Doctors) {
            $aiFeature = $plan->features()->where('code', 'ai_prescription_assistant')->first();
            $creditsToSet = ($aiFeature && is_numeric($aiFeature->value))
                ? (int)$aiFeature->value
                : (int)($plan->ai_credits ?? 0);
            $subscriber->remaining_ai_credits = $creditsToSet;
        }

        $subscriber->save();

        if ($user instanceof Doctors) {
            Cache::forget('active_subscription_for_doctor_' . $user->id);
        }

        return $subscriber;
    }
}
