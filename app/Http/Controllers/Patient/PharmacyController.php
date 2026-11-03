<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Doctors;
use App\Models\Setting;
use App\Models\PharmacyProduct;
use App\Models\PaymentGatewayDetail;
use App\Models\Review;
use App\Models\FavoriteDoc;
use App\Models\Services;
use Illuminate\Support\Facades\Session;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use App\Services\PharmacyOrderService;
use App\Models\Patient;
use App\Models\PharmacyOrder;
use App\Models\PharmacyOrderData;
use Illuminate\Support\Facades\Log;

class PharmacyController extends Controller
{
    public function searchpharmacy(Request $request)
    {
        $setting = Setting::find(1);
        $services = Services::all();
        $term = $request->get("term");
        $type = $request->get("type");
        
        $query = Doctors::with('department')->where('profile_type', 2);
        
        if (!empty($term)) {
            $query->where('name', 'like', '%' . $term . '%');
        }
        if (!empty($type)) {
            $query->where("department_id", $type);
        }
        
        $approvedQuery = clone $query;
        $approvedQuery->where("is_approve", "1");
        $doctorslist = $approvedQuery->paginate(12);

        if ($doctorslist->isEmpty()) {
            $doctorslist = $query->paginate(12);
        }
        $doctorslistmap = $query->get();

        foreach ($doctorslist as $k) {
            $k->avgratting = Review::where('doc_id', $k->id)->avg('rating');
            $k->totalreview = Review::where('doc_id', $k->id)->count();
            $k->is_fav = 0;
            if (!empty(Session::get("user_id")) && Session::get('role_id') == '1') {
                $lsfav = FavoriteDoc::where("doctor_id", $k->id)->where("user_id", Session::get("user_id"))->first();
                if ($lsfav) $k->is_fav = 1;
            }
        }

        return view("user.allpharmacy", compact('services', 'setting', 'doctorslist', 'term', 'type', 'doctorslistmap'));
    }

    public function viewpharmacy($slug)
    {
        $pharmacy = Doctors::where('slug', $slug)->where('profile_type', 2)->firstOrFail();
        $pharmacy->reviewslist = Review::with('patientls')->where("doc_id", $pharmacy->id)->get();
        $pharmacy->avgratting = Review::where("doc_id", $pharmacy->id)->avg('rating');
        $pharmacy->totalreview = Review::where("doc_id", $pharmacy->id)->count();
        
        $pharmacy->is_fav = 0;
        if (!empty(Session::get("user_id")) && Session::get('role_id') == '1') {
            $lsfav = FavoriteDoc::where("doctor_id", $pharmacy->id)->where("user_id", Session::get("user_id"))->first();
            if ($lsfav) $pharmacy->is_fav = 1;
        }

        $setting = Setting::find(1);
        $paymentdetail = PaymentGatewayDetail::all()->pluck('value', 'gateway_name_key')->toArray(); // Simplified
        $medicine = PharmacyProduct::where('pharmacy_id', $pharmacy->id)->get();

        return view("user.viewpharmacy", [
            'data' => $pharmacy,
            'setting' => $setting,
            'paymentdetail' => $paymentdetail,
            'medicine' => $medicine,
        ]);
    }

    public function addToCart($id)
    {
        $setting = Setting::first();
        if ($setting && $setting->is_wallet_enabled == '0') {
            return redirect()->back()->with(['message' => __('Wallet feature is currently disabled.'), 'alert-class' => 'alert-danger']);
        }

        if (!Session::has("user_id") || Session::get("role_id") != '1') {
            return redirect()->back()->with(['message' => __("Please login to add items to cart."), 'alert-class' => 'alert-danger']);
        }

        $product = PharmacyProduct::find($id);
        if (!$product) return redirect()->back()->with(['message' => __("Product not found."), 'alert-class' => 'alert-danger']);

        $pharmacy = Doctors::find($product->pharmacy_id);
        if (!$pharmacy) return redirect()->back()->with(['message' => __("Associated pharmacy not found."), 'alert-class' => 'alert-danger']);

        Cart::session(Session::get("user_id"))->add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => 1,
            'attributes' => [
                'pharmacy_id' => $product->pharmacy_id,
                'pharmacy_slug' => $pharmacy->slug,
                'image' => $product->image,
            ],
        ]);

        Session::flash('message', __("Medicine Successfully Added"));
        Session::flash('alert-class', 'alert-success');
        Session::flash('show_cart_dropdown', true);
        return redirect()->back();
    }

    public function removeCartItem($id)
    {
        Cart::session(Session::get("user_id"))->remove($id);
        return redirect()->back()->with(['message' => __("Medicine Deleted Successfully"), 'alert-class' => 'alert-success']);
    }

    public function viewCart()
    {
        if (!Session::has("user_id") || Session::get("role_id") != '1') {
            return redirect()->route('login')->with('message', 'Please log in to view your cart.');
        }

        $userId = Session::get("user_id");
        $cartItems = Cart::session($userId)->getContent()->sortBy('attributes.pharmacy_id');
        $cartTotal = Cart::session($userId)->getTotal();
        $setting = Setting::find(1);
        $groupedCart = $cartItems->groupBy('attributes.pharmacy_id');
        $pharmacies = Doctors::whereIn('id', $groupedCart->keys())->get()->keyBy('id');
        $patient = \App\Models\Patient::with('wallet')->find($userId);
        $walletBalance = $patient && $patient->wallet ? $patient->wallet->balance : 0;

        return view('user.cart', compact('cartItems', 'cartTotal', 'setting', 'groupedCart', 'pharmacies', 'walletBalance'));
    }

    public function processCartCheckout(Request $request, PharmacyOrderService $orderService)
    {
        if (!Session::has("user_id") || Session::get("role_id") != '1') {
            return redirect()->route('login')->with('message', 'Please log in to checkout.');
        }

        $userId = Session::get("user_id");
        $patient = Patient::with('wallet')->find($userId);
        $cartItems = Cart::session($userId)->getContent();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.view')->with([
                'message' => 'Your cart is empty.',
                'alert-class' => 'alert-danger'
            ]);
        }

        try {
            $addressData = [
                'is_discreet_packaging' => $request->boolean('is_discreet_packaging'),
                'anonymous_session_token' => $request->input('anonymous_session_token') ?: Session::get('srhr_anonymous_token'),
                'srhr_category' => $request->input('srhr_category'),
            ];
            $orders = $orderService->placeOrder($patient, $cartItems, $addressData);

            return redirect('usermedicineorder')->with([
                'message' => 'Your order has been placed successfully!',
                'alert-class' => 'alert-success'
            ]);

        } catch (\Exception $e) {
            if ($e->getMessage() == "Insufficient wallet balance.") {
                return redirect()->route('cart.view')->with([
                    'message' => 'Insufficient wallet balance. Please top up your wallet.',
                    'alert-class' => 'alert-danger'
                ]);
            }
            
            Log::error("Pharmacy checkout failed: " . $e->getMessage());
            return redirect()->route('cart.view')->with([
                'message' => 'An error occurred while processing your order. Please try again.',
                'alert-class' => 'alert-danger'
            ]);
        }
    }

    public function upload_pre_order(Request $request)
    {
        if ($request->hasFile('prescription')) {
            $file = $request->file('prescription');
            $extension = $file->getClientOriginalExtension() ?: 'png';
            $picture = time() . '.' . $extension;
            $request->file('prescription')->move(public_path('/upload/orderprescription/'), $picture);
        }

        $data = new PharmacyOrder();
        $data->Pharmacy_id = $request->Pharmacy_id;
        $data->prescription = $picture ?? null;
        $data->phone = $request->phone_no;
        $data->message = $request->message;
        $data->address = $request->address;
        $data->tax = $request->tax;
        $data->delivery_charge = $request->delivery_charge;
        $data->order_type = 1;
        $data->is_completed = '1';
        $data->status = 0;
        $data->user_id = Session::get('user_id');
        $data->save();

        Session::flash('message', __('Prescription upload successfully'));
        Session::flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    public function usermedicineorder(Request $request)
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '1') {
            $setting = Setting::find(1);
            $orderdata = PharmacyOrder::with(['pharmacy', 'user'])
                ->where('user_id', Session::get("user_id"))
                ->orderBy('id', 'desc')
                ->paginate(10);

            $complet = PharmacyOrder::where('user_id', Session::get("user_id"))->where('status', '3')->count();
            $pending = PharmacyOrder::where('user_id', Session::get("user_id"))->where('status', '0')->count();
            $userdata = Patient::find(Session::get("user_id"));
            
            return view("user.patient.medicineorder", compact("orderdata", "setting", "userdata", "complet", "pending"));
        } else {
            return redirect("/");
        }
    }

    public function get_orderdata($id)
    {
        $setting = Setting::find(1);
        $currency = explode("-", $setting->currency);
        $orderdata = PharmacyOrder::with(['pharmacy', 'user'])->find($id);

        if (!$orderdata) {
            return response()->json(['error' => 'Order not found'], 404);
        }
        
        $orderItems = PharmacyOrderData::where('order_id', $id)->get();

        return response()->json([
            'orderItems' => $orderItems,
            'orderDetails' => $orderdata,
            'currency' => $currency,
        ]);
    }
}