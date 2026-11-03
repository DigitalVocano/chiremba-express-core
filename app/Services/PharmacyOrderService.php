<?php

namespace App\Services;

use App\Models\PharmacyOrder;
use App\Models\PharmacyOrderData;
use App\Models\Setting;
use App\Models\Doctors;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use Exception;

class PharmacyOrderService
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Process a pharmacy order from the user's cart.
     *
     * @param mixed $user The patient user model.
     * @param mixed $cartItems The items from the cart.
     * @param array $addressData Array containing 'phone' and 'address'.
     * @return array The created orders.
     * @throws Exception
     */
    public function placeOrder($user, $cartItems, $addressData = [])
    {
        $setting = Setting::first();
        $subTotal = Cart::session($user->id)->getTotal();
        $taxAmount = $subTotal * (($setting->pharmacy_tax ?? 0) / 100);
        $deliveryCharge = $setting->pharmacy_delivery_charge ?? 0;
        $grandTotal = $subTotal + $taxAmount + $deliveryCharge;

        // Check Balance
        if (!$user->wallet || $user->wallet->balance < $grandTotal) {
            throw new Exception("Insufficient wallet balance.");
        }

        return DB::transaction(function () use ($user, $cartItems, $grandTotal, $addressData) {
            // 1. Debit Patient Wallet
            $this->walletService->debit(
                $user,
                $grandTotal,
                'Payment for pharmacy order',
                'pharm_ord_' . Uuid::uuid4()->toString()
            );

            // 2. Group items by pharmacy
            $groupedCart = $cartItems->groupBy('attributes.pharmacy_id');
            $orders = [];

            foreach ($groupedCart as $pharmacyId => $items) {
                $pharmacy = Doctors::with('wallet')->find($pharmacyId);
                if (!$pharmacy) continue;

                $pharmacySubTotal = $items->sum(function ($item) {
                    return $item->price * $item->quantity;
                });

                // Create Order
                $order = new PharmacyOrder();
                $order->Pharmacy_id = $pharmacyId;
                $order->user_id = $user->id;
                $order->phone = $addressData['phone'] ?? $user->phone;
                $order->address = $addressData['address'] ?? $user->address;
                $order->total = $pharmacySubTotal;
                $order->payment_type = 'Wallet';
                $order->is_completed = '1';
                $order->order_type = 2; // Direct Order
                $order->status = 0; // Pending
                if (!empty($addressData['is_discreet_packaging'])) {
                    $order->is_discreet_packaging = true;
                    $order->discreet_verification_pin = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
                    $order->anonymous_session_token = $addressData['anonymous_session_token'] ?? null;
                    $order->srhr_category = $addressData['srhr_category'] ?? 'srhr_confidential';
                    $order->message = '[DISCREET UNMARKED PACKAGING REQUIRED - PIN VERIFIED DROP-OFF]';
                }
                $order->save();

                // Integrate Generic Delivery Partner (e.g. LinkGrid)
                $settings = Setting::find(1);
                if ($settings && $settings->default_delivery_partner === 'linkgrid') {
                    $deliveryService = new \App\Services\LinkGridDeliveryService();
                    $distanceKm = 5.0; // Default estimate
                    $estimatedFee = $deliveryService->estimateFare($distanceKm);
                    
                    $bookingParams = [
                        'pickup_location' => $pharmacy->address ?? 'Pharmacy',
                        'dropoff_location' => $order->address,
                        'pickup_lat' => $pharmacy->lat ?? null,
                        'pickup_lng' => $pharmacy->lon ?? null,
                        'dropoff_lat' => $addressData['lat'] ?? null,
                        'dropoff_lng' => $addressData['lng'] ?? null,
                        'distance_km' => $distanceKm,
                        'offered_fare' => $estimatedFee,
                        'payment_method' => 'wallet', // Already paid digitally in web checkout
                    ];
                    
                    $bookingResponse = $deliveryService->bookDelivery($bookingParams);
                    if ($bookingResponse['success']) {
                        $order->delivery_charge = $estimatedFee;
                        $order->transaction_id = $bookingResponse['ride']['id'] ?? null;
                        $order->save();
                    }
                }

                // Add Items
                foreach ($items as $item) {
                    PharmacyOrderData::create([
                        'order_id' => $order->id,
                        'service_id' => $item->id,
                        'qty' => $item->quantity,
                        'name' => $item->name,
                        'price' => $item->price,
                    ]);
                }

                // Credit Pharmacy Wallet (with commission deducted)
                $settings = Setting::first();
                $commissionPercent = $settings && isset($settings->commission) ? (float)$settings->commission : 0.0;
                $commissionAmount = $pharmacySubTotal * ($commissionPercent / 100.0);
                $pharmacyShare = $pharmacySubTotal - $commissionAmount;

                $this->walletService->credit(
                    $pharmacy,
                    $pharmacyShare,
                    'Payment received for order #' . $order->id . ' (Commission ' . $commissionPercent . '% deducted)',
                    'order_payout_' . $order->id
                );

                // WhatsApp Notification via Zimflow
                try {
                    app(\App\Services\ZimflowService::class)->notifyPharmacyOrderPlaced($order);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Zimflow web pharmacy order error: " . $e->getMessage());
                }

                $orders[] = $order;
            }

            // Clear Cart
            Cart::session($user->id)->clear();

            return $orders;
        });
    }
}
