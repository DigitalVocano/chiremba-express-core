<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Doctors;
use App\Models\Setting;
use App\Models\Review;
use App\Models\PharmacyOrder;
use App\Models\PharmacyProduct;
use App\Models\Patient;
use App\Models\WalletTransaction;
use App\Models\PaymentGatewayDetail;
use App\Models\Subscriber;
use App\Models\Category;
use PDF;
use Illuminate\Support\Facades\Session;
use Exception;

class PharmacyPanelController extends Controller
{
    public function pharmacydashboard(Request $request)
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '3') {
            $setting = Setting::find(1);
            $totalreview = count(Review::where("doc_id", Session::get("user_id"))->get());
            $today = PharmacyOrder::where('Pharmacy_id', Session::get("user_id"))->whereDate('created_at', now()->format('Y-m-d'))->count();
            $orderdata = PharmacyOrder::where('Pharmacy_id', Session::get("user_id"))->get();
            foreach ($orderdata as $order) {
                $u = Patient::find($order->user_id);
                $order->user_id = $u->name;
            }
            $doctordata = Doctors::with('department')->find(Session::get("user_id"));

            // Subscription check
            $is_subscribed = false;
            $subscription = Subscriber::where('subscriber_id', Session::get("user_id"))
                ->where('subscriber_type', Doctors::class) // Pharmacies are stored in Doctors table
                ->where('status', 2) // Active
                ->latest('id')
                ->first();

            if ($subscription && isset($subscription->subscription)) {
                $startDate = new \DateTime($subscription->date);
                $duration = $subscription->subscription->duration;
                $unit = $subscription->subscription->duration_unit;
                if (is_numeric($duration) && !empty($unit)) {
                    $expiryDate = (clone $startDate)->modify('+' . $duration . ' ' . \Str::plural($unit, $duration));
                    if ($expiryDate > new \DateTime("now", $startDate->getTimezone())) {
                        $is_subscribed = true;
                    }
                }
            }

            return view("user.pharmacy.dashboard", compact('setting', 'doctordata', 'orderdata', 'totalreview', 'today', 'is_subscribed'));
        }
        return redirect()->route('doctor.login');
    }

    public function pharmacyeditprofile()
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '3') {
            $setting = Setting::find(1);
            $doctordata = Doctors::with('department')->find(Session::get("user_id"));
            return view("user.pharmacy.editprofile", compact('setting', 'doctordata'));
        }
        return redirect()->route('doctor.login');
    }

    public function updatepharmacysideprofile(Request $request)
    {
        $store = Doctors::find(Session::get("user_id"));
        $img_url = $store->image;
        if ($request->hasFile('upload_image')) {
            $file = $request->file('upload_image');
            $picture = time() . '.' . $file->getClientOriginalExtension();
            $request->file('upload_image')->move(public_path('/upload/doctors/'), $picture);
            $img_url = $picture;
        }
        $email = $request->get("email");
        if (empty($email)) {
            $email = $store->phoneno ? $store->phoneno . '@placeholder.chiremba.com' : 'provider_' . $store->id . '@placeholder.chiremba.com';
        }

        $store->name = $request->get("name");
        $store->phoneno = $request->get("phoneno");
        $store->aboutus = $request->get("aboutus");
        $store->services = $request->get("services");
        $store->address = $request->get("address");
        $store->lat = $request->get("lat");
        $store->lon = $request->get("lon");
        $store->email = $email;
        $store->working_time = $request->get("working_time");
        $store->accept_cod = $request->has('accept_cod') ? '1' : '0';
        $store->accept_digital_payments = $request->has('accept_digital_payments') ? '1' : '0';
        $store->has_own_bikes = $request->has('has_own_bikes') ? 1 : 0;
        $store->accept_pointlink_deliveries = $request->has('accept_pointlink_deliveries') ? 1 : 0;
        $store->delivery_mode = $request->get('delivery_mode', 'pointlink');
        $store->self_delivery_fee = $request->get('self_delivery_fee', 0);
        $store->ecocash_number = $request->get('ecocash_number');
        $store->ecocash_name = $request->get('ecocash_name');
        $store->bank_name = $request->get('bank_name');
        $store->bank_account_number = $request->get('bank_account_number');
        $store->bank_account_name = $request->get('bank_account_name');
        $store->innbucks_number = $request->get('innbucks_number');
        $store->image = $img_url;
        $store->save();
        Session::flash('message', __("Doctor Update Successfully"));
        Session::flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    public function pharmacymedicine(Request $request)
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '3') {
            $setting = Setting::find(1);
            $data = PharmacyProduct::where('pharmacy_id', Session::get("user_id"))->orderBy('id', 'DESC')->get();
            $doctordata = Doctors::with('department')->find(Session::get("user_id"));
            $categories = Category::where('is_active', 1)->get();
            return view("user.pharmacy.addmedicine", compact('setting', 'doctordata', 'data', 'categories'));
        }
        return redirect()->route('doctor.login');
    }

    public function showPharmacyWallet()
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '3') {
            $setting = Setting::find(1);
            $pharmacydata = Doctors::find(Session::get("user_id"));
            $wallet = $pharmacydata->wallet()->firstOrCreate(['balance' => 0]);
            $transactions = WalletTransaction::where('wallet_id', $wallet->id)->orderBy('created_at', 'desc')->paginate(10);
            $paymentGateways = PaymentGatewayDetail::where('key', 'is_active')
                ->where('value', '1')
                ->select('gateway_name')
                ->distinct()
                ->get();
            return view("user.pharmacy.pharmacywallet", compact('setting', 'pharmacydata', 'wallet', 'transactions', 'paymentGateways'));
        }
        return redirect()->route('doctor.login');
    }

    public function updatemedicinefront(Request $request)
    {
        if ($request->medicine_id == 0) {
            $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric',
                'cost_price' => 'required|numeric',
                'stock' => 'required|integer',
                'category_id' => 'required|exists:categories,id',
                'is_prescription_required' => 'required|boolean',
                'upload_image' => 'required|image'
            ]);

            $setting = Setting::first();
            if ($setting && $setting->is_pharmacy_subscription_enabled) {
                $pharmacy = Doctors::find($request->pharmacy_id);
                if ($pharmacy && $pharmacy->hasFeature('limit_medicines')) {
                    $limit = (int) $pharmacy->getFeatureValue('limit_medicines', 0);
                    $currentCount = PharmacyProduct::where('pharmacy_id', $pharmacy->id)->count();
                    if ($limit > 0 && $currentCount >= $limit) {
                        return redirect()->back()->with('message', __('You have reached the maximum number of medicines allowed.'))->with('alert-class', 'alert-danger');
                    }
                }
            }
            $data = new PharmacyProduct();
            $msg = __("Successfull Medicine Added");
        } else {
            $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric',
                'cost_price' => 'required|numeric',
                'stock' => 'required|integer',
                'category_id' => 'required|exists:categories,id',
                'is_prescription_required' => 'required|boolean',
                'upload_image' => 'nullable|image'
            ]);
            $data = PharmacyProduct::find($request->medicine_id);
            $msg = __("Successfull Medicine Update");
        }

        $img_url = $data->image;
        if ($request->hasFile('upload_image')) {
            $file = $request->file('upload_image');
            $picture = time() . '.' . $file->getClientOriginalExtension();
            $request->file('upload_image')->move(public_path('/upload/pharmacymedicine/'), $picture);
            $img_url = $picture;
        }

        $data->image = $img_url;
        $data->pharmacy_id = $request->pharmacy_id;
        $data->name = $request->name;
        $data->description = $request->description;
        $data->dosage_rules = $request->dosage_rules;
        $data->price = $request->price;
        $data->cost_price = $request->cost_price;
        $data->stock = $request->stock;
        $data->category_id = $request->category_id;
        $data->is_prescription_required = $request->is_prescription_required;
        $data->save();

        Session::flash('message', $msg);
        Session::flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    public function medicinedeletefront($id)
    {
        PharmacyProduct::find($id)->delete();
        return redirect()->back();
    }

    public function pharmacyreview()
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '3') {
            $setting = Setting::find(1);
            $doctordata = Doctors::find(Session::get("user_id"));
            $reviewdata = Review::with('patientls')->where("doc_id", Session::get("user_id"))->get();
            return view("user.pharmacy.review", compact('setting', 'doctordata', 'reviewdata'));
        }
        return redirect()->route('doctor.login');
    }

    public function pharmacychangepassword()
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '3') {
            $setting = Setting::find(1);
            $doctordata = Doctors::with('department')->find(Session::get("user_id"));
            return view("user.pharmacy.changepassword", compact('setting', 'doctordata'));
        }
        return redirect()->route('doctor.login');
    }

    public function downloadInvoice($id)
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '3') {
            $order = PharmacyOrder::with('user', 'items.product')->where('id', $id)
                ->where('Pharmacy_id', Session::get("user_id"))
                ->firstOrFail();

            $setting = Setting::first();
            $pdf = PDF::loadView('user.pharmacy.invoice_template', compact('order', 'setting'));
            return $pdf->download('invoice-'.$order->id.'.pdf');
        }
        return redirect()->route('doctor.login');
    }

    public function showPos()
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '3') {
            $setting = Setting::find(1);
            $medicines = PharmacyProduct::where('pharmacy_id', Session::get("user_id"))->get();
            return view("user.pharmacy.pos", compact('setting', 'medicines'));
        }
        return redirect()->route('doctor.login');
    }

    public function showProfitLoss(Request $request)
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '3') {
            $setting = Setting::find(1);
            $pharmacydata = Doctors::find(Session::get("user_id"));
            $pharmacyId = $pharmacydata->id;

            // Fetch completed orders
            $orders = PharmacyOrder::with('items')
                ->where('Pharmacy_id', $pharmacyId)
                ->where('status', 3)
                ->get();

            $totalRevenue = 0;
            $totalCogs = 0;
            $orderMargins = [];

            foreach ($orders as $order) {
                $orderCogs = 0;
                foreach ($order->items as $item) {
                    $product = PharmacyProduct::find($item->service_id);
                    $costPrice = $product ? (float)$product->cost_price : 0.0;
                    
                    // Fallback to default COGS of 70% if none specified
                    if ($costPrice <= 0) {
                        $costPrice = (float)$item->price * 0.7;
                    }
                    
                    $itemCogs = $costPrice * (int)$item->qty;
                    $orderCogs += $itemCogs;
                }
                
                $orderRevenue = (float)$order->total - (float)$order->tax - (float)$order->delivery_charge;
                $orderProfit = $orderRevenue - $orderCogs;
                
                $totalRevenue += $orderRevenue;
                $totalCogs += $orderCogs;
                
                $orderMargins[] = [
                    'id' => $order->id,
                    'date' => $order->created_at->format('Y-m-d H:i'),
                    'revenue' => $orderRevenue,
                    'cogs' => $orderCogs,
                    'profit' => $orderProfit,
                    'margin' => $orderRevenue > 0 ? ($orderProfit / $orderRevenue) * 100 : 0
                ];
            }

            $grossProfit = $totalRevenue - $totalCogs;
            $profitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

            // Monthly breakdown for Chart
            $monthlyStats = PharmacyOrder::select(
                \DB::raw('YEAR(created_at) as year'),
                \DB::raw('MONTH(created_at) as month'),
                \DB::raw('SUM(total) as revenue')
            )
            ->where('Pharmacy_id', $pharmacyId)
            ->where('status', 3)
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

            return view("user.pharmacy.profit_loss", compact(
                'setting',
                'pharmacydata',
                'totalRevenue',
                'totalCogs',
                'grossProfit',
                'profitMargin',
                'orderMargins',
                'monthlyStats'
            ));
        }
        return redirect()->route('doctor.login');
    }
}