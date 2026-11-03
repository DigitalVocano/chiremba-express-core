<?php

namespace App\Services;

use Paynow\Payments\Paynow;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $paynow;

    public function __construct()
    {
        $id = env('PAYNOW_INTEGRATION_ID') ?: env('PAYNOW_INTERGRATION_ID');
        $key = env('PAYNOW_INTEGRATION_KEY') ?: env('PAYNOW_INTERGRATION_KEY');

        if (empty($id) || empty($key)) {
            $details = \App\Models\PaymentGatewayDetail::where('gateway_name', 'paynow')->pluck('value', 'key');
            $id = $id ?: ($details['integration_id'] ?? null);
            $key = $key ?: ($details['integration_key'] ?? null);
        }

        $this->paynow = new Paynow(
            $id ?? '19933',
            $key ?? '21a7f08a-6e44-4b5a-b310-d6c92a44ed20',
            route('wallet.topup.callback'), // Centralized callback route
            route('paynow.result')
        );
    }

    public function initiatePaynow(string $email, float $amount, string $reference)
    {
        $payment = $this->paynow->createPayment($reference, $email);
        $payment->add('Chiremba Express Payment', $amount);

        try {
            $response = $this->paynow->send($payment);
            
            if ($response->success()) {
                return [
                    'success' => true,
                    'redirectUrl' => $response->redirectUrl(),
                    'pollUrl' => $response->pollUrl()
                ];
            } else {
                Log::error("Paynow Error: " . print_r($response->errors(), true));
                return ['success' => false, 'message' => 'Payment gateway error: ' . json_encode($response->errors())];
            }
        } catch (\Exception $e) {
            Log::error("Paynow Exception: " . $e->getMessage());
            return ['success' => false, 'message' => 'Connection error: ' . $e->getMessage()];
        }
    }

    public function initiateMobilePayment(string $phone, float $amount, string $reference, string $method = 'ecocash', string $email = 'patient@chirembaexpress.com')
    {
        $payment = $this->paynow->createPayment($reference, $email);
        $payment->add("Chiremba Express Appointment #{$reference}", $amount);

        try {
            $response = $this->paynow->sendMobile($payment, $phone, $method);
            if ($response->success()) {
                return [
                    'success' => true,
                    'pollUrl' => $response->pollUrl(),
                    'instructions' => $response->instructions() ?? 'Please enter your EcoCash PIN on your phone to complete payment.'
                ];
            } else {
                Log::error("Paynow Mobile Error: " . print_r($response->errors(), true));
                return ['success' => false, 'message' => 'EcoCash Mobile Gateway Error: ' . json_encode($response->errors())];
            }
        } catch (\Exception $e) {
            Log::error("Paynow Mobile Exception: " . $e->getMessage());
            return ['success' => false, 'message' => 'EcoCash Gateway Connection Exception: ' . $e->getMessage()];
        }
    }

    public function verifyTransaction(string $pollUrl)
    {
        try {
            $status = $this->paynow->pollTransaction($pollUrl);
            return $status->paid();
        } catch (\Exception $e) {
            Log::error("Paynow Verification Exception: " . $e->getMessage());
            return false;
        }
    }

    public function checkTransactionStatus(string $pollUrl)
    {
        try {
            $status = $this->paynow->pollTransaction($pollUrl);
            if ($status->paid()) {
                return ['paid' => true, 'status' => 'completed'];
            }
            $rawStatus = strtolower($status->status() ?? 'pending');
            if (in_array($rawStatus, ['cancelled', 'failed', 'refunded'])) {
                return ['paid' => false, 'status' => 'failed'];
            }
            return ['paid' => false, 'status' => 'pending'];
        } catch (\Exception $e) {
            Log::error("Paynow Status Check Exception: " . $e->getMessage());
            return ['paid' => false, 'status' => 'pending'];
        }
    }
}
