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
use Illuminate\Support\Facades\Session;
use Exception;

class DashboardController extends Controller
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
            return view("user.pharmacy.dashboard", compact('setting', 'doctordata', 'orderdata', 'totalreview', 'today'));
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
            $data = PharmacyProduct::where('pharmacy_id', Session::get("user_id"))->get();
            $doctordata = Doctors::with('department')->find(Session::get("user_id"));
            return view("user.pharmacy.addmedicine", compact('setting', 'doctordata', 'data'));
        }
        return redirect()->route('doctor.login');
    }

    public function updatemedicinefront(Request $request)
    {
        if ($request->medicine_id == 0) {
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
            $img_url = "";
        } else {
            $data = PharmacyProduct::find($request->medicine_id);
            $msg = __("Successfull Medicine Update");
            $img_url = $data->image;
        }

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
        $data->price = $request->price;
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
}