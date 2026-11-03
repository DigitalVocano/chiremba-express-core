<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use App\Models\Subscription;
use App\Models\Subscriber;
use App\Models\Doctors;
use App\Models\Patient;
use App\Models\Setting;
use DataTables;

class SubscriberController extends Controller
{
    public function show_subscription()
    {
        return view("admin.subscription.subscription");
    }

    public function subscriber_doc()
    {
        return view("admin.subscription.subscriber_doc");
    }

    public function show_subscriptiontable()
    {
        $subscriptions = Subscription::query();
        return DataTables::of($subscriptions)->make(true);
    }

    public function show_subscribetable()
    {
        $subscriptions = Subscriber::with(['doctors', 'provider', 'subscription'])
            ->where(function ($q) {
                $q->whereNotNull('doctor_id')
                  ->orWhere('subscriber_type', Doctors::class)
                  ->orWhereNotNull('subscriber_id');
            })
            ->orderBy('id', 'desc');

        return DataTables::of($subscriptions)
            ->editColumn('id', function ($subscription) {
                return $subscription->id;
            })
            ->editColumn('doctor_id', function ($subscription) {
                $doc = $subscription->doctors ?: $subscription->provider;
                if ($doc && ($subscription->subscriber_type === Doctors::class || !empty($subscription->doctor_id))) {
                    $roleBadge = ($doc->profile_type == '2') ? ' [Pharmacy]' : ' [Doctor]';
                    $planName = $subscription->subscription ? ' — ' . $subscription->subscription->name : '';
                    return $doc->name . $roleBadge . $planName;
                }
                if ($subscription->subscriber_type === Patient::class && $subscription->subscriber_id) {
                    $pat = Patient::find($subscription->subscriber_id);
                    $planName = $subscription->subscription ? ' — ' . $subscription->subscription->name : '';
                    return ($pat ? $pat->name : 'Patient #' . $subscription->subscriber_id) . ' [Patient]' . $planName;
                }
                return $doc->name ?? ('Subscriber #' . ($subscription->subscriber_id ?: $subscription->doctor_id));
            })
            ->editColumn('payment_type', function ($subscription) {
                if ($subscription->payment_type == 1) return __("Braintree");
                if ($subscription->payment_type == 2) return __("Bank Deposit");
                if ($subscription->payment_type == 3) return __("Razorpay");
                if ($subscription->payment_type == 4) return __("Paystack");
                if ($subscription->payment_type == 5) return __("Stripe");
                return __("Wallet");
            })
            ->editColumn('amount', function ($subscription) {
                $setting = Setting::find(1);
                $currency = '$';
                if ($setting && !empty($setting->currency)) {
                    $parts = explode("-", $setting->currency);
                    $currency = isset($parts[1]) ? $parts[1] : $parts[0];
                }
                return $currency . number_format((float)$subscription->amount, 2);
            })
            ->editColumn('status', function ($subscription) {
                if ($subscription->status == 1) return __("Not Active");
                if ($subscription->status == 2) return __("Active") . ($subscription->date ? ' (Exp: ' . substr($subscription->date, 0, 10) . ')' : '');
                if ($subscription->status == 3) return __("Expired");
                if ($subscription->status == 4) return "Reject";
                if ($subscription->status == 6) return "Disable";
                return "";
            })
            ->editColumn('deposit_image', function ($subscription) {
                if ($subscription->deposit_image != "" && $subscription->deposit_image != null) {
                    return asset("upload/bank_receipt") . '/' . $subscription->deposit_image;
                }
                return "";
            })
            ->editColumn('action', function ($subscription) {
                $view = __("View");
                $payurl = url('admin/view_subscription_price', array('id' => $subscription->id));
                $active = "";
                $disable = "";
                if ($subscription->status == 2) {
                    $disable = '<a onclick="disable_order(' . $subscription->id . ',6)" rel="tooltip" class="btn btn-sm btn-warning">Disable</a>';
                }
                if ($subscription->status == 6 || $subscription->status == 1 || $subscription->status == 3) {
                    $active = '<a onclick="active_order(' . $subscription->id . ',2)" rel="tooltip" class="btn btn-sm btn-success">Activate</a>';
                }
                return '<a href="' . $payurl . '" class="btn btn-sm btn-success" style="color:white !important">' . $view . '</a> ' . $disable . $active;
            })
            ->make(true);
    }

    public function disable_order(Request $request)
    {
        $data = Subscriber::find($request->get("record_id"));
        if ($data) {
            $data->status = $request->get("status");
            $data->save();
            $docId = $data->doctor_id ?: $data->subscriber_id;
            if ($docId) {
                Cache::forget('active_subscription_for_doctor_' . $docId);
            }
        }
    }

    public function active_order(Request $request)
    {
        $data = Subscriber::find($request->get("record_id"));
        if ($data) {
            $data->status = $request->get("status");
            $data->save();
            $docId = $data->doctor_id ?: $data->subscriber_id;
            if ($docId) {
                Cache::forget('active_subscription_for_doctor_' . $docId);
            }
        }
    }

    public function view_subscription_price($id)
    {
        $data = Subscriber::with(['doctors', 'provider', 'subscription'])->find($id);
        if ($data) {
            $doc = $data->doctors ?: $data->provider;
            $data->doctor_name = $doc->name ?? ('Subscriber #' . ($data->subscriber_id ?: $data->doctor_id));
            $data->subscription_name = $data->subscription->name ?? '';
        }
        return view("admin.subscription.view_subscriber", compact("data", "id"));
    }

    public function edit_subscription_price($id)
    {
        return redirect()->route("Subscription");
    }

    public function update_subscriptio_price(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:subscriptions,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'duration_unit' => 'required|in:day,week,month,year',
        ]);

        $store = Subscription::find($request->get("id"));
        if ($store) {
            $store->name = $request->get("name");
            $store->price = $request->get("price");
            $store->duration = $request->get("duration");
            $store->duration_unit = $request->get("duration_unit");
            $store->save();
            Session::flash('message', __("Subscription Plan Updated Successfully"));
            Session::flash('alert-class', 'alert-success');
            return redirect()->route("Subscription");
        } else {
            Session::flash('message', __("something getting wrong"));
            Session::flash('alert-class', 'alert-danger');
            return redirect()->route("Subscription");
        }
    }
}
