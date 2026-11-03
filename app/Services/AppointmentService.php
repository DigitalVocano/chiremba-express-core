<?php

namespace App\Services;

use App\Models\BookAppointment;
use App\Models\Settlement;
use App\Models\Doctors;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class AppointmentService
{
    protected $walletService;
    protected $notificationService;

    public function __construct(WalletService $walletService, NotificationService $notificationService)
    {
        $this->walletService = $walletService;
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the booking process.
     */
    public function bookAppointment(Patient $patient, array $data)
    {
        return DB::transaction(function () use ($patient, $data) {
            $consultationFees = (float)($data['consultation_fees'] ?? 0);
            $doctorId = $data['doctor_id'];
            $date = date("Y-m-d", strtotime($data['date']));
            $paymentMode = $data['payment_mode'] ?? ($data['payment_type'] ?? 'Wallet');

            $setting = \App\Models\Setting::first();
            $doctor = Doctors::find($doctorId);

            // 0. Check Doctor Subscription & Monthly Appointment Package Limits
            if ($doctor && $setting && $setting->is_doctor_subscription_enabled) {
                $monthlyCount = BookAppointment::where('doctor_id', $doctorId)
                    ->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month)
                    ->whereNotIn('status', ['5', '6'])
                    ->count();

                if ($doctor->isSubscribedOrGlobalDisabled()) {
                    if ($doctor->hasFeature('max_appointments')) {
                        $maxAppts = (int)$doctor->getFeatureValue('max_appointments', 0);
                        if ($maxAppts > 0 && $monthlyCount >= $maxAppts) {
                            throw new Exception(__("This doctor has reached their monthly appointment limit ({$maxAppts}) for their current subscription plan."));
                        }
                    }
                } else {
                    $freemiumLimit = (int)($setting->freemium_doctor_appointment_limit ?? 0);
                    if ($freemiumLimit > 0 && $monthlyCount >= $freemiumLimit) {
                        throw new Exception(__("This provider's subscription is inactive and they have reached the freemium monthly booking limit."));
                    }
                }
            }

            // Enforce Patient Monthly Booking Limit if Patient Subscription is enabled
            if ($setting && $setting->is_patient_subscription_enabled) {
                $patientSub = \App\Models\Subscriber::with('subscription.features')
                    ->where('subscriber_id', $patient->id)
                    ->where('subscriber_type', Patient::class)
                    ->where('status', '2')
                    ->latest('id')
                    ->first();
                $patMonthlyBookings = BookAppointment::where('user_id', $patient->id)
                    ->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month)
                    ->whereNotIn('status', ['5', '6'])
                    ->count();
                if ($patientSub && $patientSub->subscription) {
                    $limitFeature = $patientSub->subscription->features->firstWhere('code', 'limit_doctor_booking');
                    $patLimit = $limitFeature ? (int)$limitFeature->value : 0;
                    if ($patLimit > 0 && $patMonthlyBookings >= $patLimit) {
                        throw new Exception(__("You have reached your monthly appointment booking limit ({$patLimit}) for your subscription plan."));
                    }
                } else {
                    $freePatLimit = (int)($setting->freemium_patient_consultation_limit ?? 0);
                    if ($freePatLimit > 0 && $patMonthlyBookings >= $freePatLimit) {
                        throw new Exception(__("You have reached your free consultation limit. Please subscribe to a patient plan to book more appointments."));
                    }
                }
            }

            // 1. Check for duplicate booking slot
            $slotId = isset($data['slot_id']) ? $data['slot_id'] : (isset($data['slot']) ? explode("#", $data['slot'])[0] : null);
            $slotName = $data['slot_name'] ?? ($data['slot'] ?? ($data['preferred_session'] ?? 'General Consultation'));

            if ($slotId && $slotId > 0 && !in_array($slotId, [101, 102, 103])) {
                $exists = BookAppointment::where("date", $date)
                    ->where("slot_id", $slotId)
                    ->where('status', '!=', '5') // Not rejected
                    ->where('status', '!=', '6') // Not cancelled
                    ->exists();

                if ($exists) {
                    throw new Exception(__('Slot Already Booked'));
                }
            }

            // 2. Deduct from Wallet ONLY if payment_mode is Wallet
            if (strtolower($paymentMode) === 'wallet') {
                $this->walletService->debit(
                    $patient,
                    $consultationFees,
                    "Appointment booking with Doctor ID: {$doctorId}",
                    "appt_book_{$doctorId}_{$date}_" . time()
                );
            }

            // 3. Create Appointment
            $appointment = new BookAppointment();
            $appointment->user_id = $patient->id;
            $appointment->doctor_id = $doctorId;
            $appointment->slot_id = $slotId ?? 0;
            $appointment->slot_name = $slotName;
            $appointment->date = $date;
            $appointment->phone = $data['phone'] ?? ($data['phone_no'] ?? $patient->phone);
            $appointment->user_description = $data['user_description'] ?? ($data['message'] ?? '');
            $appointment->payment_mode = $paymentMode;
            $appointment->consultation_fees = $consultationFees;
            $appointment->is_completed = in_array(strtolower($paymentMode), ['wallet', 'online']) ? 1 : 0;
            $appointment->status = 1; // Received

            if (isset($data['preferred_session'])) {
                $appointment->preferred_session = $data['preferred_session'];
            }
            if (isset($data['proof_of_payment'])) {
                $appointment->proof_of_payment = $data['proof_of_payment'];
            }
            if (isset($data['medical_aid_society_id'])) {
                $appointment->medical_aid_society_id = $data['medical_aid_society_id'];
            }
            if (isset($data['medical_aid_name'])) {
                $appointment->medical_aid_name = $data['medical_aid_name'];
            }
            if (isset($data['medical_aid_number'])) {
                $appointment->medical_aid_number = $data['medical_aid_number'];
            }
            if (isset($data['medical_aid_suffix'])) {
                $appointment->medical_aid_suffix = $data['medical_aid_suffix'];
            }
            if (isset($data['medical_aid_scheme'])) {
                $appointment->medical_aid_scheme = $data['medical_aid_scheme'];
            }
            if (isset($data['medical_aid_member_name'])) {
                $appointment->medical_aid_member_name = $data['medical_aid_member_name'];
            }
            if (isset($data['medical_aid_card_photo'])) {
                $appointment->medical_aid_card_photo = $data['medical_aid_card_photo'];
            }
            if (isset($data['medical_aid_status'])) {
                $appointment->medical_aid_status = $data['medical_aid_status'];
            }
            $appointment->save();

            // 4. Commission & Settlement Accounting
            $commissionRequired = !$setting || !isset($setting->is_commission_required) || (bool)$setting->is_commission_required;
            $commissionPercent = ($commissionRequired && $setting && isset($setting->commission)) ? (float)$setting->commission : 0.0;
            $commissionAmount = $consultationFees * ($commissionPercent / 100.0);
            $doctorSettlementAmount = max(0, $consultationFees - $commissionAmount);

            $modeLower = strtolower($paymentMode);
            if (in_array($modeLower, ['cod', 'cash'])) {
                // Patient pays cash directly to doctor; debit platform commission from doctor's wallet if commission is enabled
                if ($doctor && $commissionAmount > 0) {
                    $docWallet = $doctor->wallet()->firstOrCreate(['balance' => 0]);
                    $docWallet->balance = $docWallet->balance - $commissionAmount;
                    $docWallet->save();

                    \App\Models\WalletTransaction::create([
                        'wallet_id' => $docWallet->id,
                        'amount' => -$commissionAmount,
                        'type' => 'debit',
                        'description' => "Platform Commission Debt for Cash Appointment #{$appointment->id} ({$commissionPercent}%)",
                        'reference' => 'cod_appt_comm_' . $appointment->id,
                    ]);
                }
            } elseif (!in_array($modeLower, ['medical_aid', 'medical aid'])) {
                // Platform collected money via Wallet or Online Gateway; create Settlement owed to Doctor
                $settlement = new Settlement();
                $settlement->book_id = $appointment->id;
                $settlement->status = '0'; // Pending settlement to doctor
                $settlement->payment_date = now()->toDateString();
                $settlement->doctor_id = $doctorId;
                $settlement->amount = $doctorSettlementAmount;
                $settlement->save();
            }

            // 5. Send Notification
            $msg = __("You have a new upcoming appointment");
            $this->notificationService->sendNotification($msg, $doctorId, "doctor_id", $appointment->id, ['type' => 'doctor_id', 'receiver_type' => 'doctor_id', 'order_id' => (string)$appointment->id]);

            // 6. Send WhatsApp Notification via Zimflow
            try {
                app(\App\Services\ZimflowService::class)->notifyAppointmentBooked($appointment);
            } catch (\Exception $e) {
                Log::error("Zimflow appointment booking dispatch error: " . $e->getMessage());
            }

            return $appointment;
        });
    }

    /**
     * Update appointment status (e.g., Doctor accepts/rejects).
     */
    public function updateStatus($appointmentId, $status)
    {
        $appointment = BookAppointment::with('doctorls', 'patientls')->find($appointmentId);
        if (!$appointment) {
            throw new Exception(__('Appointment Not Found'));
        }

        $appointment->status = $status;
        $appointment->save();

        // Handle specific status logic
        $msg = "";
        if ($status == '3' || $status == '2') { // In Process / Accepted / Approved
            $msg = __('order.inprocess1') . ' ' . $appointment->doctorls->name . ' ' . __('order.inprocess2') . ' ' . $appointment->date . ' ' . $appointment->slot_name;
        } elseif ($status == '5') { // Rejected
            $msg = __('order.rejectorder') . ' ' . $appointment->doctorls->name;
            Settlement::where("book_id", $appointmentId)->delete();

            // Only refund the patient's wallet if they actually paid via Wallet or Online
            $modeLower = strtolower($appointment->payment_mode ?? 'wallet');
            if (in_array($modeLower, ['wallet', 'online']) && $appointment->patientls && $appointment->consultation_fees > 0) {
                $this->walletService->credit(
                    $appointment->patientls,
                    (float)$appointment->consultation_fees,
                    "Refund for rejected appointment #{$appointment->id}",
                    "refund_appt_{$appointment->id}"
                );
            }
        } elseif ($status == '4') { // Completed
            $msg = __('order.complete1') . ' ' . $appointment->doctorls->name . " " . __('order.is completed');
        }

        if ($msg) {
            $this->notificationService->sendNotification($msg, $appointment->user_id, "user_id", $appointment->id, ['type' => 'user_id', 'receiver_type' => 'user_id', 'order_id' => (string)$appointment->id]);
        }

        // WhatsApp notification on status change
        try {
            app(\App\Services\ZimflowService::class)->notifyAppointmentStatus($appointment, $status);
        } catch (\Exception $e) {
            Log::error("Zimflow appointment status dispatch error: " . $e->getMessage());
        }

        return $appointment;
    }
}
