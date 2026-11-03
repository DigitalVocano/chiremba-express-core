<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\PaymentGatewayDetail;
use App\Models\Setting;

class EcocashService
{
    protected $username;
    protected $password;
    protected $merchantCode;
    protected $merchantPin;
    protected $merchantNumber;
    protected $merchantName;
    protected $superMerchantName;
    protected $terminalId;
    protected $location;
    protected $endpoint;

    public function __construct()
    {
        $settings = PaymentGatewayDetail::where('gateway_name', 'ecocash')->pluck('value', 'key');
        $this->username = env('ECOCASH_USERNAME') ?: ($settings['username'] ?? 'VOCANO');
        $this->password = env('ECOCASH_PASSWORD') ?: ($settings['password'] ?? '#@cJf0_NAlIxi-,?');
        $this->merchantCode = env('ECOCASH_MERCHANT_CODE') ?: ($settings['merchant_code'] ?? '063331');
        $this->merchantPin = env('ECOCASH_MERCHANT_PIN') ?: ($settings['merchant_pin'] ?? '1212');
        $this->merchantNumber = env('ECOCASH_MERCHANT_NUMBER') ?: ($settings['merchant_number'] ?? '0788371000');
        $this->merchantName = env('ECOCASH_MERCHANT_NAME') ?: ($settings['merchant_name'] ?? 'Chiremba Express');
        $this->superMerchantName = env('ECOCASH_SUPER_MERCHANT_NAME') ?: ($settings['super_merchant_name'] ?? 'Digital Vocano');
        $this->terminalId = env('ECOCASH_TERMINAL_ID') ?: ($settings['terminal_id'] ?? 'TERM123456');
        $this->location = env('ECOCASH_LOCATION') ?: ($settings['location'] ?? 'Digital Vocano');
        $this->endpoint = env('ECOCASH_API_URL') ?: ($settings['endpoint'] ?? 'https://payonline.econet.co.zw/ecocashGateway/payment/v1/transactions/amount');
    }

    public function initiateTransaction($amount, $phone, $reference)
    {
        if (empty($this->username) || empty($this->password) || empty($this->merchantCode)) {
            return [
                'success' => false,
                'message' => 'EcoCash API credentials are not configured. Please configure EcoCash credentials in Admin Payment Settings or use Paynow.'
            ];
        }
        // Sanitize phone number
        $phone = preg_replace('/[^0-9]/', '', $phone);
        // Ensure it's in the format required (e.g., 77xxxxxxx)
        if (strlen($phone) > 9 && substr($phone, 0, 3) == '263') {
            $phone = substr($phone, 3);
        }
        if (strlen($phone) == 10 && substr($phone, 0, 1) == '0') {
            $phone = substr($phone, 1);
        }

        // Determine currency from settings or default to USD
        $setting = Setting::find(1);
        $currencyCode = 'USD'; // Default
        if ($setting && str_contains(strtoupper($setting->currency), 'ZWG')) {
            $currencyCode = 'ZWG';
        }

        $cleanRef = substr($reference, 0, 30);

        $body = [
            "clientCorrelator" => $cleanRef,
            "notifyUrl" => route('payment.ecocash.notify'),
            "referenceCode" => $cleanRef,
            "tranType" => "MER",
            "endUserId" => $phone,
            "remarks" => "Chiremba Express Payment",
            "transactionOperationStatus" => "Charged",
            "paymentAmount" => [
                "charginginformation" => [
                    "amount" => (float)$amount,
                    "currency" => $currencyCode,
                    "description" => "Chiremba Express Payment"
                ],
                "chargeMetaData" => [
                    "channel" => "WEB",
                    "purchaseCategoryCode" => "Online Payment",
                    "onBeHalfOf" => "Chiremba Express Payment"
                ]
            ],
            "merchantCode" => $this->merchantCode,
            "merchantPin" => $this->merchantPin,
            "merchantNumber" => $this->merchantNumber,
            "currencyCode" => $currencyCode,
            "countryCode" => "ZW",
            "terminalID" => $this->terminalId,
            "location" => $this->location,
            "superMerchantName" => $this->superMerchantName,
            "merchantName" => $this->merchantName
        ];


        Log::info("EcoCash Request: " . json_encode($body));

        try {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->post($this->endpoint, $body);

            Log::info("EcoCash Response: " . $response->body());

            if ($response->successful()) {
                $data = $response->json();
                // Check for successful initiation status
                if (isset($data['transactionOperationStatus']) && 
                   ($data['transactionOperationStatus'] == 'PENDING SUBSCRIBER VALIDATION' || $data['transactionOperationStatus'] == 'CHARGED')) {
                    return ['success' => true, 'message' => 'Payment initiated. Please check your phone to confirm the transaction.'];
                } else {
                     return ['success' => false, 'message' => $data['responseCode'] ?? 'Unknown error from EcoCash gateway'];
                }
            } else {
                $status = $response->status();
                if ($status == 401) {
                    return ['success' => false, 'message' => 'EcoCash Gateway Authorization Failed (401). Please verify EcoCash API credentials or try Paynow.'];
                }
                return ['success' => false, 'message' => "EcoCash Payment Failed (Status {$status}). Please try Paynow or contact support."];
            }
        } catch (\Exception $e) {
            Log::error("EcoCash Exception: " . $e->getMessage());
            return ['success' => false, 'message' => 'Payment error: ' . $e->getMessage()];
        }
    }
}
