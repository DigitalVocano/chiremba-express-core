<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class LinkGridDeliveryService
{
    protected $apiEndpoint;
    protected $apiToken;
    protected $defaultDeliveryPartner;

    public function __construct()
    {
        $setting = Setting::first();
        $this->apiEndpoint = $setting->linkgrid_api_endpoint ?? 'https://linkgrid.co.zw/api/v1/';
        $this->apiToken = $setting->linkgrid_api_token ?? '';
        $this->defaultDeliveryPartner = $setting->default_delivery_partner ?? '';
    }

    /**
     * Fetch active logistics services from LinkGrid to get current pricing.
     */
    public function getLogisticsService()
    {
        try {
            $url = rtrim($this->apiEndpoint, '/') . '/services?flat_list=true';
            $response = Http::get($url);

            if ($response->successful()) {
                $services = $response->json();
                // Search for first service category in logistics vertical
                foreach ($services as $service) {
                    if (isset($service['vertical']) && $service['vertical'] === 'logistics') {
                        return $service;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('LinkGrid: Failed to fetch services: ' . $e->getMessage());
        }

        // Fallback default pricing values for Zimbabwe Courier bikes
        return [
            'id' => 1, // Fallback service category ID
            'base_fare' => 3.00, // $3.00 base fare
            'per_km_fare' => 0.50 // $0.50 per km
        ];
    }

    /**
     * Estimate the delivery fare based on distance (km), capped by pointlink_max_delivery_fee.
     */
    public function estimateFare($distanceKm)
    {
        $setting = Setting::first();
        $maxFee = (float)($setting->pointlink_max_delivery_fee ?? 25.00);

        $service = $this->getLogisticsService();
        $baseFare = (float)($service['base_fare'] ?? 3.00);
        $perKmFare = (float)($service['per_km_fare'] ?? 0.50);

        $fare = $baseFare + ($perKmFare * (float)$distanceKm);

        return min($fare, $maxFee);
    }

    /**
     * Dispatch/book a courier delivery request on LinkGrid.
     */
    public function bookDelivery($params)
    {
        if (empty($this->apiToken)) {
            Log::warning('LinkGrid: Booking skipped. API Token not configured.');
            return [
                'success' => false,
                'message' => 'LinkGrid API Token not configured in Admin settings.'
            ];
        }

        $service = $this->getLogisticsService();
        
        $payload = [
            'pickup_location' => $params['pickup_location'] ?? 'Pharmacy',
            'dropoff_location' => $params['dropoff_location'] ?? 'Patient Address',
            'pickup_lat' => $params['pickup_lat'] ?? null,
            'pickup_lng' => $params['pickup_lng'] ?? null,
            'dropoff_lat' => $params['dropoff_lat'] ?? null,
            'dropoff_lng' => $params['dropoff_lng'] ?? null,
            'stops' => $params['stops'] ?? [], // Multi-pharmacy pickup stops
            'contact_name' => $params['contact_name'] ?? ($params['recipient_name'] ?? 'Recipient'),
            'contact_phone' => $params['contact_phone'] ?? ($params['recipient_phone'] ?? ''),
            'offered_fare' => $params['offered_fare'] ?? $this->estimateFare($params['distance_km'] ?? 1),
            'item_price' => $params['item_price'] ?? 0.0,
            'distance_km' => $params['distance_km'] ?? 1,
            'payment_method' => $params['payment_method'] ?? 'cash', // Cash/COD or wallet/digital
            'service_category_id' => $service['id'] ?? 1,
            'specialized_details' => $params['specialized_details'] ?? ['order_type' => 'medicine_delivery'],
        ];

        try {
            $url = rtrim($this->apiEndpoint, '/') . '/rides';
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept' => 'application/json',
            ])->post($url, $payload);

            if ($response->successful()) {
                $rideData = $response->json();
                Log::info('LinkGrid: Ride booked successfully: ' . ($rideData['id'] ?? ''));
                return [
                    'success' => true,
                    'ride' => $rideData
                ];
            } else {
                Log::error('LinkGrid: Booking request failed: ' . $response->body());
                return [
                    'success' => false,
                    'message' => 'LinkGrid error: ' . $response->status()
                ];
            }
        } catch (\Exception $e) {
            Log::error('LinkGrid: Exception during booking: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
