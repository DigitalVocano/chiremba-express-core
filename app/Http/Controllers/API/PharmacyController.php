<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Doctors;
use App\Models\TokenData;
use App\Models\PharmacyProduct;
use App\Models\PharmacyOrder;
use App\Models\PharmacyOrderData;
use Illuminate\Support\Facades\DB;
use App\Models\Medicines; // Ensure Medicines model is imported
use App\Models\Category;
use App\Models\Setting;

class PharmacyController extends Controller
{
    public function pharmacyregister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:doctors',
            'password' => 'required',
            'phone' => 'required',
            'token' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => "0", 'register' => $validator->errors()->first()], 200);
        }

        $store = new Doctors();
        $store->name = $request->get("name");
        $store->email = $request->get("email");
        $store->password = Hash::make($request->get("password"));
        $store->phoneno = $request->get("phone");
        $store->profile_type = '2'; // 2 for Pharmacy
        $store->is_approve = '1';
        $store->save();

        $tokenVal = $request->get("token"); if ($tokenVal && !in_array($tokenVal, ['no_fcm_token', 'no_token_provided', 'null', 'undefined'])) { $td = TokenData::where("token", $tokenVal)->first() ?: new TokenData(); $td->token = $tokenVal; $td->doctor_id = $store->id; $td->type = '3'; $td->save(); }

        return response()->json([
            'success' => "1",
            'register' => [
                "user_id" => $store->id,
                "name" => $store->name,
                "phone" => $store->phoneno,
                "email" => $store->email
            ]
        ], 200);
    }

    public function pharmacylogin(Request $request)
    {
        $rules = ['token' => 'required'];
        if ($request->get('auth_type') == 'otp' || $request->get('login_type') == 'otp') {
            $rules['otp'] = 'required';
            $rules['email'] = 'required'; // email or phone
        } else {
            $rules['email'] = 'required';
            $rules['password'] = 'required';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['success' => "0", 'register' => $validator->errors()->first()], 200);
        }

        $loginField = $request->get("email");
        $cleanField = preg_replace('/[^0-9]/', '', $loginField);
        $isDemo = str_ends_with($cleanField, '771234567') || str_ends_with($cleanField, '771234568') || str_ends_with($cleanField, '771234569');

        if (filter_var($loginField, FILTER_VALIDATE_EMAIL)) {
            $user = Doctors::where("email", $loginField)->where("profile_type", '2')->first();
        } else {
            $user = Doctors::where(function($q) use ($loginField, $cleanField) {
                $q->where("phoneno", $loginField)->orWhere("phoneno", $cleanField);
            })->where("profile_type", '2')->first();
        }

        $isValid = false;
        if ($user) {
            if ($request->get('auth_type') == 'otp' || $request->get('login_type') == 'otp') {
                $otp = $request->get('otp');
                $cachedOtp = \Illuminate\Support\Facades\Cache::get('otp_' . $loginField);
                if ($otp && (($cachedOtp && $otp == $cachedOtp) || ($isDemo && $otp == '123456'))) {
                    $isValid = true;
                    \Illuminate\Support\Facades\Cache::forget('otp_' . $loginField);
                }
            } else {
                $isValid = Hash::check($request->get("password"), $user->password);
            }
        }

        if ($isValid) {
            $tokenData = TokenData::where('token', $request->get("token"))->first();
            if (!$tokenData) {
                $tokenData = new TokenData();
                $tokenData->token = $request->get("token");
            }
            $tokenData->doctor_id = $user->id; $tokenData->type = '3';
            $tokenData->save();
            
            return response()->json([
                'success' => "1",
                'register' => [
                    "user_id" => $user->id,
                    "name" => $user->name,
                    "phone" => $user->phoneno,
                    "email" => $user->email,
                    "image" => asset('upload/doctors') . '/' . ($user->image ?? 'default.png'),
                    "role" => "pharmacy"
                ]
            ], 200);
        }

        return response()->json(['success' => "0", 'register' => ($request->get('auth_type') == 'otp' || $request->get('login_type') == 'otp') ? "Invalid OTP Code" : "Invalid Credentials"], 200);
    }

    public function pharmacy_medicines(Request $request)
    {
        $pharmacyId = $request->get('pharmacy_id', $request->get('id'));

        $query = PharmacyProduct::with(['category', 'pharmacy']);

        if (!empty($pharmacyId)) {
            $query->where(function($q) use ($pharmacyId) {
                $q->where('pharmacy_id', $pharmacyId)
                  ->orWhere('pharmacy_id', (int)$pharmacyId);
            });
        }

        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('keyword') && !empty($request->keyword)) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        if ($request->has('gateway') && !empty($request->gateway)) {
            $gw = strtolower($request->gateway);
            if ($gw !== 'all') {
                $query->whereHas('pharmacy', function ($q) use ($gw) {
                    if ($gw === 'cod') {
                        if (\Illuminate\Support\Facades\Schema::hasColumn('doctors', 'accept_cod')) {
                            $q->where('accept_cod', 1);
                        }
                    } else if ($gw === 'ecocash' || $gw === 'paynow' || $gw === 'digital') {
                        if (\Illuminate\Support\Facades\Schema::hasColumn('doctors', 'accept_digital_payments')) {
                            $q->where('accept_digital_payments', 1);
                        }
                    } else if ($gw === 'medical_aid') {
                        if (\Illuminate\Support\Facades\Schema::hasColumn('doctors', 'accept_medical_aid')) {
                            $q->where('accept_medical_aid', 1);
                        }
                    }
                });
            }
        }
        
        $data = $query->orderBy('id', 'DESC')->paginate(10);

        // Ensure products are strictly scoped to the selected pharmacy if pharmacyId is provided
        if ($data->isEmpty() && !empty($pharmacyId)) {
            // Check master medicines catalog as fallback if pharmacy has no custom products
            $masterMeds = DB::table('medicines');
            if ($request->has('keyword') && !empty($request->keyword)) {
                $masterMeds->where('name', 'like', '%' . $request->keyword . '%');
            }
            // Keep empty or return master catalog formatted for this pharmacy
        }

        foreach ($data->items() as $k) {
            if (strpos($k->image, 'http') !== 0) {
                $k->image = asset('upload/pharmacymedicine') . '/' . $k->image;
            }
            $k->pharmacy_name = $k->pharmacy ? $k->pharmacy->name : 'Unknown Pharmacy';
        }

        $setting = DB::table('setting')->first();
        $pharmacy = !empty($pharmacyId) ? DB::table('doctors')->where('id', $pharmacyId)->first() : null;

        return response()->json([
            'success' => 1,
            'status' => 1,
            'pharmacy_tax' => $setting ? (int)$setting->pharmacy_tax : 0,
            'pharmacy_delivery_charge' => ($pharmacy && $pharmacy->delivery_mode === 'self_delivery') ? (float)$pharmacy->self_delivery_fee : ($setting ? (int)$setting->pharmacy_delivery_charge : 0),
            'pharmacy_name' => $pharmacy ? $pharmacy->name : 'All Pharmacies',
            'accept_cod' => $pharmacy ? (int)($pharmacy->accept_cod ?? 1) : 1,
            'accept_digital_payments' => $pharmacy ? (int)($pharmacy->accept_digital_payments ?? 1) : 1,
            'has_own_bikes' => $pharmacy ? (int)($pharmacy->has_own_bikes ?? 0) : 0,
            'accept_pointlink_deliveries' => $pharmacy ? (int)($pharmacy->accept_pointlink_deliveries ?? 1) : 1,
            'delivery_mode' => $pharmacy ? ($pharmacy->delivery_mode ?? 'pointlink') : 'pointlink',
            'self_delivery_fee' => $pharmacy ? (float)($pharmacy->self_delivery_fee ?? 0) : 0,
            'data' => $data
        ], 200);
    }

    public function add_pharmacy_medicine(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pharmacy_id' => 'required',
            'name' => 'required', // Name is always required, even if manual
            'price' => 'required',
            'cost_price' => 'required', // For profit/loss
            'stock' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'is_prescription_required' => 'required|in:0,1',
            'image' => 'required'
        ]);

        if ($validator->fails()) return response()->json(['success' => "0", 'message' => $validator->errors()->first()], 200);



        $setting = Setting::first();
        if ($setting && $setting->is_pharmacy_subscription_enabled) {
            $pharmacy = Doctors::find($request->pharmacy_id);
            if ($pharmacy && !$pharmacy->isSubscribedOrGlobalDisabled()) {
                return response()->json([
                    'success' => "0",
                    'message' => __("An active pharmacy subscription is required to add medicines. Please subscribe to a plan.")
                ], 200);
            }
            if ($pharmacy && $pharmacy->hasFeature('limit_medicines')) {
                $limit = (int)$pharmacy->getFeatureValue('limit_medicines', 0);
                $currentCount = PharmacyProduct::where('pharmacy_id', $pharmacy->id)->count();
                if ($limit > 0 && $currentCount >= $limit) {
                    return response()->json([
                        'success' => "0",
                        'message' => __("You have reached the maximum number of medicines ({$limit}) allowed by your subscription plan.")
                    ], 200);
                }
            }
        }

        $store = new PharmacyProduct();
        $store->pharmacy_id = $request->pharmacy_id;
        // medicine_id is optional (nullable)
        $store->medicine_id = ($request->has('medicine_id') && $request->medicine_id != 0) ? $request->medicine_id : null;
        $store->category_id = $request->category_id;
        $store->name = $request->name; // Use manual name or name from UI

        $store->price = $request->price;
        $store->cost_price = $request->cost_price;
        $store->stock = $request->stock;
        $store->is_prescription_required = $request->is_prescription_required;
        $store->dosage_rules = $request->dosage_rules; // Optional custom rules
        $store->description = $request->description;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/pharmacymedicine/'), $filename);
            $store->image = $filename;
        }
        $store->save();

        return response()->json(['success' => "1", 'message' => "Medicine Added Successfully"], 200);
    }

    public function medicine_order(Request $request)
    {
        // Direct order logic (Cart checkout)
        $rawOrderData = $request->get("order_data") ?: $request->get("product_json");
        if (is_array($rawOrderData)) {
            $data = $rawOrderData;
        } else {
            $data = json_decode($rawOrderData, true) ?? [];
        }
        
        $pharmacyId = $request->get("pharmacy_id") ?: 0;
        $pharmacy = $pharmacyId ? Doctors::find($pharmacyId) : null;

        $order = new PharmacyOrder();
        $order->user_id = $request->get("user_id");
        $order->Pharmacy_id = $pharmacyId;
        $order->total = $request->get("total");
        $order->address = $request->get("address");
        $order->phone = $request->get("phone") ?: $request->get("phoneno");
        $order->payment_type = $request->get("payment_type"); // 1=COD, 2=Online/Wallet
        $order->order_type = 2; // Direct Order
        $order->status = 0; // Pending

        $order->recipient_name = $request->get("recipient_name") ?: ($request->get("contact_name") ?: null);
        $order->recipient_phone = $request->get("recipient_phone") ?: ($request->get("contact_phone") ?: $request->get("phone"));
        $order->delivery_mode = $request->get("delivery_mode", $pharmacy ? ($pharmacy->delivery_mode ?? 'pointlink') : 'pointlink');
        
        if ($request->has("medical_aid_society_id")) $order->medical_aid_society_id = $request->get("medical_aid_society_id");
        if ($request->has("medical_aid_name")) $order->medical_aid_name = $request->get("medical_aid_name");
        if ($request->has("medical_aid_number")) $order->medical_aid_number = $request->get("medical_aid_number");
        if ($request->has("medical_aid_suffix")) $order->medical_aid_suffix = $request->get("medical_aid_suffix");
        if ($request->has("medical_aid_scheme")) $order->medical_aid_scheme = $request->get("medical_aid_scheme");
        if ($request->has("medical_aid_member_name")) $order->medical_aid_member_name = $request->get("medical_aid_member_name");
        
        if ($request->hasFile('medical_aid_card_photo')) {
            $file = $request->file('medical_aid_card_photo');
            $filename = time() . '_card.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/medical_aid_cards/'), $filename);
            $order->medical_aid_card_photo = $filename;
        }

        // Handle Prescription Upload for POS
        if ($request->hasFile('prescription')) {
            $file = $request->file('prescription');
            $filename = time() . '_pos.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/orderprescription/'), $filename);
            $order->prescription = $filename;
        }
        
        $order->save();

        $settings = Setting::find(1);

        // Check Pharmacy Delivery Mode: Self Delivery vs PointLink Network
        if ($pharmacy && $pharmacy->delivery_mode === 'self_delivery') {
            $order->delivery_charge = $pharmacy->self_delivery_fee ?? 0;
            $order->save();
        } elseif ($settings && $settings->default_delivery_partner === 'linkgrid') {
            $deliveryService = new \App\Services\LinkGridDeliveryService();
            $distanceKm = $request->get('distance_km', 5.0); 
            $estimatedFee = $deliveryService->estimateFare($distanceKm);
            
            $bookingParams = [
                'pickup_location' => $pharmacy ? $pharmacy->address : 'Pharmacy',
                'dropoff_location' => $order->address,
                'pickup_lat' => $pharmacy ? $pharmacy->lat : null,
                'pickup_lng' => $pharmacy ? $pharmacy->lon : null,
                'dropoff_lat' => $request->get('lat') ?: null,
                'dropoff_lng' => $request->get('lon') ?: null,
                'contact_name' => $order->recipient_name ?: 'Patient Recipient',
                'contact_phone' => $order->recipient_phone ?: $order->phone,
                'item_price' => (float)$order->total,
                'distance_km' => $distanceKm,
                'offered_fare' => $estimatedFee,
                'payment_method' => $order->payment_type == 1 ? 'cash' : 'wallet',
                'specialized_details' => [
                    'order_type' => 'medicine_delivery',
                    'pharmacy_id' => $pharmacy ? $pharmacy->id : null,
                    'pharmacy_name' => $pharmacy ? $pharmacy->name : 'Pharmacy',
                    'order_id' => $order->id,
                ]
            ];
            
            $bookingResponse = $deliveryService->bookDelivery($bookingParams);
            if ($bookingResponse['success']) {
                $order->delivery_charge = $estimatedFee;
                $order->pointlink_ride_id = $bookingResponse['ride']['id'] ?? null;
                $order->transaction_id = $bookingResponse['ride']['id'] ?? null;
                $order->save();
            }
        }

        // Record COD Commission Debt if Cash Payment
        // Record COD Commission Debt if Cash Payment, or Credit Pharmacy Net Earnings if Wallet/Digital Payment
        $commissionRequired = !$settings || !isset($settings->is_commission_required) || (bool)$settings->is_commission_required;
        $commissionPct = $commissionRequired ? (float)($settings->commission ?? 10) : 0.0;
        $commissionAmt = ((float)$order->total) * ($commissionPct / 100);

        if ($order->payment_type == 1 && $pharmacy) {
            if ($commissionAmt > 0) {
                $wallet = $pharmacy->wallet()->firstOrCreate(['balance' => 0]);
                $wallet->balance = $wallet->balance - $commissionAmt;
                $wallet->save();

                \App\Models\WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'amount' => -$commissionAmt,
                    'type' => 'debit',
                    'description' => "Platform Commission Debt for COD Order #{$order->id} ({$commissionPct}%)",
                    'reference' => 'cod_comm_' . $order->id,
                ]);
            }
        } elseif ($order->payment_type == 2 && $pharmacy) {
            $netPharmacyAmount = max(0, ((float)$order->total) - $commissionAmt);
            if ($netPharmacyAmount > 0) {
                $wallet = $pharmacy->wallet()->firstOrCreate(['balance' => 0]);
                $wallet->balance = $wallet->balance + $netPharmacyAmount;
                $wallet->save();

                \App\Models\WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'amount' => $netPharmacyAmount,
                    'type' => 'credit',
                    'description' => "Pharmacy Order #{$order->id} Net Earnings (after {$commissionPct}% commission)",
                    'reference' => 'pharm_order_net_' . $order->id,
                ]);
            }
        }
        if (false && $order->payment_type == 1 && $pharmacy) {
            $commissionPct = (float)($settings->commission ?? 10);
            $commissionAmt = ((float)$order->total) * ($commissionPct / 100);
            
            if ($commissionAmt > 0) {
                $wallet = $pharmacy->wallet()->firstOrCreate(['balance' => 0]);
                $wallet->balance = $wallet->balance - $commissionAmt;
                $wallet->save();

                \App\Models\WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'amount' => -$commissionAmt,
                    'type' => 'debit',
                    'description' => "Platform Commission Debt for COD Order #{$order->id} ({$commissionPct}%)",
                    'reference' => 'cod_comm_' . $order->id,
                ]);
            }
        }

        foreach ($data as $key => $item) {
            $productId = $item['product_id'] ?? ($item['id'] ?? $key);
            $product = is_numeric($productId) ? PharmacyProduct::find($productId) : null;
            
            $details = new PharmacyOrderData();
            $details->order_id = $order->id;
            $details->service_id = is_numeric($productId) ? $productId : 0;
            $details->name = $product ? $product->name : ($item['title'] ?? 'Unknown Product');
            $details->price = $item['price'] ?? 0;
            $details->qty = $item['quantity'] ?? 1;
            $details->save();
            
            // Deduct Stock
            if ($product) {
                $product->stock = max(0, $product->stock - ($item['quantity'] ?? 1));
                $product->save();
            }
        }

        // WhatsApp Notification via Zimflow
        try {
            app(\App\Services\ZimflowService::class)->notifyPharmacyOrderPlaced($order);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Zimflow pharmacy order dispatch error: " . $e->getMessage());
        }

        return response()->json(['success' => "1", 'message' => "Order Placed Successfully", 'order_id' => $order->id], 200);
    }

    public function prescription_order(Request $request)
    {
        $file = $request->file('image') ?: $request->file('prescription_file');
        
        if (!$file && !$request->has('image') && !$request->has('prescription_file')) {
            return response()->json(['success' => "0", 'message' => "Prescription image file is required"], 200);
        }

        $order = new PharmacyOrder();
        $order->user_id = $request->get("user_id");
        $order->Pharmacy_id = $request->get("pharmacy_id") ?: $request->get("pid");
        $order->address = $request->get("address");
        $order->phone = $request->get("phone") ?: $request->get("phoneno");
        $order->message = $request->get("message");
        $order->order_type = 1; // Prescription
        $order->status = 0;

        if ($file) {
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/orderprescription/'), $filename);
            $order->prescription = $filename;
        }
        $order->save();

        return response()->json(['success' => "1", 'message' => "Prescription Uploaded Successfully", 'order_id' => $order->id], 200);
    }

    public function get_pharmacy_order_list(Request $request)
    {
        $orders = PharmacyOrder::with('user')->where('Pharmacy_id', $request->pharmacy_id)->orderBy('id', 'DESC')->get();
        foreach($orders as $order){
            if ($order->prescription) {
                $pUrl = asset('upload/orderprescription') . '/' . $order->prescription;
                $order->prescription = str_replace('/public/upload/', '/upload/', $pUrl);
            }
        }
        return response()->json(['success' => "1", 'data' => $orders], 200);
    }

    public function get_user_order_list(Request $request)
    {
        $validator = Validator::make($request->all(), ['user_id' => 'required']);
        if ($validator->fails()) return response()->json(['status' => 0, 'success' => "0", 'message' => $validator->errors()->first()], 200);

        $userId = $request->get('user_id');
        $orders = PharmacyOrder::with(['pharmacy', 'dataList'])
            ->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->get();

        foreach ($orders as $order) {
            $pharmacyName = $order->pharmacy ? $order->pharmacy->name : 'Pharmacy';
            $pharmacyAddress = $order->pharmacy ? ($order->pharmacy->address ?? '') : '';
            $rawImg = $order->pharmacy ? ($order->pharmacy->image ?? 'default.png') : 'default.png';
            $pharmacyImage = str_replace('/public/upload/', '/upload/', asset('upload/doctors/' . $rawImg));
            $pharmacyPhone = $order->pharmacy ? ($order->pharmacy->phoneno ?? '') : '';
            $pharmacyEmail = $order->pharmacy ? ($order->pharmacy->email ?? '') : '';

            $order->pharmacy_name = $pharmacyName;
            $order->Pharmacy_name = $pharmacyName;

            $order->pharmacy_address = $pharmacyAddress;
            $order->Pharmacy_address = $pharmacyAddress;

            $order->pharmacy_image = $pharmacyImage;
            $order->Pharmacy_image = $pharmacyImage;

            $order->pharmacy_phoneno = $pharmacyPhone;
            $order->Pharmacy_phoneno = $pharmacyPhone;

            $order->pharmacy_email = $pharmacyEmail;
            $order->Pharmacy_email = $pharmacyEmail;

            $order->medicine = $order->dataList;
            $order->data_list = $order->dataList;

            if ($order->prescription) {
                $pUrl = asset('upload/orderprescription') . '/' . $order->prescription;
                $order->prescription = str_replace('/public/upload/', '/upload/', $pUrl);
            }
        }

        return response()->json([
            'status' => 1,
            'success' => "1",
            'data' => $orders
        ], 200);
    }

    public function pharmacy_dashboard(Request $request)
    {
        $validator = Validator::make($request->all(), ['pharmacy_id' => 'required']);
        if ($validator->fails()) return response()->json(['success' => "0", 'message' => $validator->errors()->first()], 200);



        $pharmacy_id = $request->pharmacy_id;
        $total_orders = PharmacyOrder::where('Pharmacy_id', $pharmacy_id)->count();
        $pending_orders = PharmacyOrder::where('Pharmacy_id', $pharmacy_id)->where('status', 0)->count();
        $completed_orders = PharmacyOrder::where('Pharmacy_id', $pharmacy_id)->where('status', 3)->count();
        $total_medicines = PharmacyProduct::where('pharmacy_id', $pharmacy_id)->count();
        $low_stock_medicines = PharmacyProduct::where('pharmacy_id', $pharmacy_id)->where('stock', '<=', 10)->limit(5)->get();
        
        $pharmacy = Doctors::find($pharmacy_id);
        $isSubscribed = $pharmacy ? $pharmacy->isSubscribedOrGlobalDisabled() : true;
        $subscriptionRequired = $pharmacy ? $pharmacy->isSubscriptionRequired() : false;

        return response()->json([
            'success' => "1",
            'data' => [
                'total_orders' => $total_orders,
                'pending_orders' => $pending_orders,
                'completed_orders' => $completed_orders,
                'total_medicines' => $total_medicines,
                'low_stock_medicines' => $low_stock_medicines,
                'is_subscribed' => $isSubscribed,
                'subscription_required' => $subscriptionRequired,
            ]
        ], 200);
    }

    public function most_used_medicine()
    {
        // Return random medicines or top medicines as "Most Used"
        $data = PharmacyProduct::with('pharmacy')->inRandomOrder()->limit(10)->get();
        foreach ($data as $k) {
            if (strpos($k->image, 'http') !== 0) {
                $k->image = asset('upload/pharmacymedicine') . '/' . $k->image;
            }
            $k->pharmacy_name = $k->pharmacy ? $k->pharmacy->name : 'Unknown Pharmacy';
        }
        return response()->json(['success' => "1", 'data' => $data], 200);
    }

    public function search_master_medicine(Request $request)
    {
        $term = $request->get('term');
        // Search in the central Medicines table
        $data = Medicines::where('name', 'like', '%' . $term . '%')->take(20)->get();
        return response()->json(['success' => "1", 'data' => $data], 200);
    }

    public function get_categories()
    {
        $data = Category::where('is_active', 1)->get();
        return response()->json(['success' => "1", 'data' => $data], 200);
    }
}
