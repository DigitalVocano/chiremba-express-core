<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class ZimflowService
{
    /**
     * Normalize phone number to international digits (without '+') for Zimflow API.
     */
    protected function normalizePhone($phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', (string) $phone);
        if (str_starts_with($clean, '00')) {
            $clean = substr($clean, 2);
        } elseif (str_starts_with($clean, '0') && strlen($clean) === 10) {
            // Zimbabwe local mobile format e.g. 0771234567 -> 263771234567
            $clean = '263' . substr($clean, 1);
        } elseif (strlen($clean) === 9 && str_starts_with($clean, '7')) {
            // Zimbabwe national format e.g. 771234567 -> 263771234567
            $clean = '263' . $clean;
        }
        return $clean;
    }

    /**
     * Send an Auth OTP code via WhatsApp using a WhatsApp Template.
     */
    public function sendAuthOtp($phone, $otp)
    {
        $token = Setting::getValue('zimflow_api_token');
        $templateName = Setting::getValue('zimflow_auth_template', 'pointlink_otp');
        $baseUrl = Setting::getValue('zimflow_api_endpoint', 'https://zimflow.co.zw/public-api/wpbox/');
        $language = Setting::getValue('zimflow_template_language', 'en');

        Log::info("Zimflow: Attempting Auth OTP for $phone using template $templateName");

        if (!$token) {
            Log::error('ZimflowService: API token is missing in settings.');
            return false;
        }

        $phone = $this->normalizePhone($phone);

        $payload = [
            'token' => $token,
            'phone' => $phone,
            'template_name' => $templateName,
            'template_language' => $language,
            'components' => [
                [
                    'type' => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => (string) $otp],
                    ]
                ],
                [
                    'type' => 'button',
                    'sub_type' => 'url',
                    'index' => '0',
                    'parameters' => [
                        ['type' => 'text', 'text' => (string) $otp],
                    ]
                ]
            ]
        ];

        try {
            $url = rtrim($baseUrl, '/') . "/send-template-message";
            Log::debug("ZimflowService: Dispatching Auth OTP to {$url}", [
                'payload' => array_merge($payload, ['token' => '***HIDDEN***'])
            ]);

            $response = Http::timeout(10)->post($url, $payload);
            
            $body = $response->body();
            $responseData = json_decode($body, true);

            $isActuallySuccess = $response->successful() && (
                (isset($responseData['status']) && $responseData['status'] === 'success') ||
                (isset($responseData['success']) && $responseData['success'] === true) ||
                (isset($responseData['message_id']))
            );

            if ($isActuallySuccess) {
                Log::info("ZimflowService: Auth OTP Template ({$templateName} / {$language}) sent to {$phone}", ['response' => $body]);
                return true;
            }
            Log::warning("ZimflowService: Auth OTP Template ({$templateName} / {$language}) failed, trying alternate language or session/SMS fallback", ['status' => $response->status(), 'response' => $body]);

            // Try alternate template language (en vs en_US) in case WABA uses the other translation code
            $altLang = ($language === 'en_US') ? 'en' : 'en_US';
            $payload['template_language'] = $altLang;
            $retryResp = Http::timeout(8)->post($url, $payload);
            $retryBody = $retryResp->body();
            $retryData = json_decode($retryBody, true);
            $isRetrySuccess = $retryResp->successful() && (
                (isset($retryData['status']) && $retryData['status'] === 'success') ||
                (isset($retryData['success']) && $retryData['success'] === true) ||
                (isset($retryData['message_id']))
            );
            if ($isRetrySuccess) {
                Log::info("ZimflowService: Auth OTP Template succeeded on language retry ({$altLang}) for {$phone}");
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error("ZimflowService: Auth OTP Template exception", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send an OTP code via SMS.
     */
    public function sendSmsOtp($phone, $otp)
    {
        $token = Setting::getValue('zimflow_api_token');
        $baseUrl = Setting::getValue('zimflow_api_endpoint', 'https://zimflow.co.zw/public-api/wpbox/');

        Log::info("Zimflow: Attempting SMS OTP for $phone");

        if (!$token) {
            Log::error('ZimflowService (SMS): API token is missing.');
            return false;
        }

        $phone = $this->normalizePhone($phone);
        
        $message = "Your Chiremba verification code is: {$otp}. Please do not share this code.";

        $payload = [
            'token' => $token,
            'phone' => $phone,
            'message' => $message,
            'channel' => 'sms'
        ];

        try {
            $url = rtrim($baseUrl, '/') . "/send-message";
            Log::debug("ZimflowService: Dispatching SMS OTP to {$url}", [
                'payload' => array_merge($payload, ['token' => '***HIDDEN***'])
            ]);

            $response = Http::post($url, $payload);
            
            $body = $response->body();
            $responseData = json_decode($body, true);

            $isActuallySuccess = $response->successful() && (
                (isset($responseData['status']) && $responseData['status'] === 'success') ||
                (isset($responseData['success']) && $responseData['success'] === true) ||
                (isset($responseData['message_id']))
            );

            if ($isActuallySuccess) {
                Log::info("ZimflowService: SMS OTP successfully sent to {$phone}", ['response' => $body]);
                return true;
            }

            Log::error("ZimflowService: SMS OTP failed", ['response' => $response->body()]);
            return false;
        } catch (\Exception $e) {
            Log::error("ZimflowService: SMS OTP exception", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send a standard, free-form WhatsApp message within the active 24h session.
     */
    public function sendSessionMessage($phone, $message)
    {
        $token = Setting::getValue('zimflow_api_token');
        $baseUrl = Setting::getValue('zimflow_api_endpoint', 'https://zimflow.co.zw/public-api/wpbox/');

        if (!$token) {
            Log::error('ZimflowService: API token is missing in settings.');
            return false;
        }

        $phone = $this->normalizePhone($phone);

        $payload = [
            'token' => $token,
            'phone' => $phone,
            'message' => $message,
        ];

        try {
            $response = Http::post(rtrim($baseUrl, '/') . "/send-message", $payload);
            
            $body = $response->body();
            $responseData = json_decode($body, true);
            $isActuallySuccess = $response->successful() && (
                (isset($responseData['status']) && $responseData['status'] === 'success') ||
                (isset($responseData['success']) && $responseData['success'] === true)
            );

            if ($isActuallySuccess) {
                Log::info("ZimflowService: Session message successfully sent to {$phone}");
                return true;
            }
            Log::error("ZimflowService: Session message failed", ['status' => $response->status(), 'response' => $body, 'payload' => $payload]);
            return false;
        } catch (\Exception $e) {
            Log::error("ZimflowService: Session message exception", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Dispatch WhatsApp notification when an appointment is booked.
     */
    public function notifyAppointmentBooked($appointment)
    {
        try {
            $appointment->loadMissing('doctorls', 'patientls');
            $doctor = $appointment->doctorls;
            $patient = $appointment->patientls;

            // Notify Doctor
            $docPhone = $doctor ? ($doctor->phoneno ?? $doctor->phone_no ?? null) : null;
            if (!empty($docPhone)) {
                $docMsg = "🩺 *New Appointment Alert - Chiremba Telehealth*\n\n"
                    . "Hello Dr. " . ($doctor->name ?? '') . ",\n"
                    . "You have received a new consultation booking from " . ($patient->name ?? 'a patient') . ".\n\n"
                    . "📅 *Date:* " . $appointment->date . "\n"
                    . "⏰ *Slot:* " . ($appointment->slot_name ?? 'General Consultation') . "\n"
                    . ($appointment->user_description ? "📝 *Notes:* " . $appointment->user_description . "\n" : "")
                    . "\nPlease open the Chiremba Doctor App to review and accept.";
                $this->sendSessionMessage($docPhone, $docMsg);
            }

            // Notify Patient
            $patientPhone = $appointment->phone ?: ($patient->phone ?? null);
            if (!empty($patientPhone)) {
                $patMsg = "🩺 *Appointment Booked - Chiremba Telehealth*\n\n"
                    . "Hello " . ($patient->name ?? 'Valued Patient') . ",\n"
                    . "Your appointment with Dr. " . ($doctor->name ?? 'Doctor') . " has been submitted successfully.\n\n"
                    . "📅 *Date:* " . $appointment->date . "\n"
                    . "⏰ *Slot:* " . ($appointment->slot_name ?? 'General Consultation') . "\n\n"
                    . "We will notify you immediately when Dr. " . ($doctor->name ?? '') . " accepts the appointment.";
                $this->sendSessionMessage($patientPhone, $patMsg);
            }
        } catch (\Exception $e) {
            Log::error("ZimflowService: Error in notifyAppointmentBooked: " . $e->getMessage());
        }
    }

    /**
     * Dispatch WhatsApp notification on appointment status update (accept/complete/cancel).
     */
    public function notifyAppointmentStatus($appointment, $status)
    {
        try {
            $appointment->loadMissing('doctorls', 'patientls');
            $doctor = $appointment->doctorls;
            $patient = $appointment->patientls;
            $patientPhone = $appointment->phone ?: ($patient->phone ?? null);

            if (empty($patientPhone)) return;

            if ($status == '3' || $status == '2') { // Accepted / In Progress / Approved
                $msg = "✅ *Appointment Confirmed - Chiremba Telehealth*\n\n"
                    . "Hello " . ($patient->name ?? 'Valued Patient') . ",\n"
                    . "Dr. " . ($doctor->name ?? 'your doctor') . " has accepted your consultation appointment for " . $appointment->date . " (" . ($appointment->slot_name ?? '') . ").\n\n"
                    . "Please keep your Chiremba App open for the consultation call.";
                $this->sendSessionMessage($patientPhone, $msg);
            } elseif ($status == '4') { // Completed
                $msg = "🎉 *Consultation Completed - Chiremba Telehealth*\n\n"
                    . "Hello " . ($patient->name ?? 'Valued Patient') . ",\n"
                    . "Your consultation with Dr. " . ($doctor->name ?? '') . " is marked completed.\n"
                    . "Your prescription and medical records are available in the Chiremba App.";
                $this->sendSessionMessage($patientPhone, $msg);
            } elseif ($status == '5') { // Cancelled / Rejected
                $msg = "ℹ️ *Appointment Notice - Chiremba Telehealth*\n\n"
                    . "Hello " . ($patient->name ?? 'Valued Patient') . ",\n"
                    . "Your appointment with Dr. " . ($doctor->name ?? '') . " for " . $appointment->date . " was cancelled/declined. Your consultation fee has been refunded to your wallet.";
                $this->sendSessionMessage($patientPhone, $msg);
            }
        } catch (\Exception $e) {
            Log::error("ZimflowService: Error in notifyAppointmentStatus: " . $e->getMessage());
        }
    }

    /**
     * Dispatch WhatsApp notification when a pharmacy order is placed.
     */
    public function notifyPharmacyOrderPlaced($order)
    {
        try {
            $order->loadMissing('pharmacy', 'user', 'dataList');
            $pharmacy = $order->pharmacy;
            $patient = $order->user;

            // Notify Pharmacy
            $pharmPhone = $pharmacy ? ($pharmacy->phoneno ?? $pharmacy->phone_no ?? null) : null;
            if (!empty($pharmPhone)) {
                $itemsList = "";
                if ($order->dataList) {
                    foreach ($order->dataList as $it) {
                        $itemsList .= "• " . $it->name . " x" . $it->qty . " ($" . number_format($it->price, 2) . ")\n";
                    }
                }
                $pharmMsg = "💊 *New Pharmacy Order #{$order->id} - Chiremba Pharmacy*\n\n"
                    . "Hello " . ($pharmacy->name ?? 'Pharmacy') . ",\n"
                    . "You have received a new medicine order from " . ($patient->name ?? 'Customer') . ".\n\n"
                    . ($itemsList ? "📋 *Items:*\n" . $itemsList . "\n" : "")
                    . "💰 *Total:* $" . number_format($order->total, 2) . "\n"
                    . "📍 *Delivery Address:* " . ($order->address ?? 'N/A') . "\n"
                    . "📞 *Recipient Phone:* " . ($order->phone ?? $order->recipient_phone ?? 'N/A') . "\n\n"
                    . "Please process this order in your Chiremba Pharmacy portal.";
                $this->sendSessionMessage($pharmPhone, $pharmMsg);
            }

            // Notify Patient
            $patientPhone = $order->phone ?: ($patient->phone ?? null);
            if (!empty($patientPhone)) {
                $patMsg = "💊 *Order Placed Successfully #{$order->id} - Chiremba Pharmacy*\n\n"
                    . "Hello " . ($patient->name ?? 'Valued Customer') . ",\n"
                    . "Your order with " . ($pharmacy->name ?? 'Pharmacy') . " has been placed successfully.\n\n"
                    . "💰 *Total Amount:* $" . number_format($order->total, 2) . "\n"
                    . "📍 *Delivery To:* " . ($order->address ?? 'N/A') . "\n\n"
                    . "We will notify you once your order is packed and dispatched for delivery.";
                $this->sendSessionMessage($patientPhone, $patMsg);
            }
        } catch (\Exception $e) {
            Log::error("ZimflowService: Error in notifyPharmacyOrderPlaced: " . $e->getMessage());
        }
    }

    /**
     * Dispatch WhatsApp notification when pharmacy order status updates.
     */
    public function notifyPharmacyOrderStatus($order, $status)
    {
        try {
            $order->loadMissing('pharmacy', 'user');
            $patientPhone = $order->phone ?: ($order->user->phone ?? null);
            if (empty($patientPhone)) return;

            $statusName = match((int)$status) {
                1 => "Accepted & Being Prepared",
                2 => "Dispatched / Out for Delivery",
                3 => "Delivered",
                4 => "Cancelled",
                default => "Updated"
            };

            $msg = "💊 *Order Update #{$order->id} - Chiremba Pharmacy*\n\n"
                . "Hello " . ($order->user->name ?? 'Valued Customer') . ",\n"
                . "Your order from " . ($order->pharmacy->name ?? 'Pharmacy') . " is now: *{$statusName}*.\n\n"
                . "Track real-time delivery status inside your Chiremba App.";
            $this->sendSessionMessage($patientPhone, $msg);
        } catch (\Exception $e) {
            Log::error("ZimflowService: Error in notifyPharmacyOrderStatus: " . $e->getMessage());
        }
    }
}
