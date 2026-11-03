<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctors;
use App\Models\PharmacyProduct;
use App\Models\PharmacyOrder;
use App\Models\Setting;
use App\Models\PaymentGatewayDetail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use DataTables;
use Exception;
use Carbon\Carbon;

class PharmacyController extends Controller
{
    public function pharmacy()
    {
        return view('admin.pharmacy.pharmacy');
    }

    public function pharmacytable(Request $request)
    {
        $pharmacies = Doctors::where('profile_type', '2');

        if ($request->has('search_term') && !empty($request->search_term)) {
            $searchTerm = $request->search_term;
            $pharmacies->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('email', 'like', '%' . $searchTerm . '%')
                      ->orWhere('address', 'like', '%' . $searchTerm . '%');
            });
        }

        $sortBy = $request->get('sort_by', 'newest');
        switch ($sortBy) {
            case 'oldest': $pharmacies->orderBy('created_at', 'asc'); break;
            case 'name_asc': $pharmacies->orderBy('name', 'asc'); break;
            case 'name_desc': $pharmacies->orderBy('name', 'desc'); break;
            case 'newest': default: $pharmacies->orderBy('created_at', 'desc'); break;
        }

        return DataTables::of($pharmacies)
            ->editColumn('image', function ($pharmacy) {
                $imagePath = $pharmacy->image ? asset('upload/doctors/' . $pharmacy->image) : asset('upload/doctors/defaultpharmacy.png');
                return '<img src="' . $imagePath . '" class="h-16 w-16 object-cover rounded-md" alt="Pharmacy Image">';
            })
            ->addColumn('recommendable', function ($pharmacy) {
                $checked = $pharmacy->recommendable ? 'checked' : '';
                $route = route('admin.pharmacy.toggle_recommendable', $pharmacy->id);
                $csrf = csrf_token();
                return '<label class="relative inline-flex items-center cursor-pointer">
                          <input type="checkbox" value="" class="sr-only peer" onchange="toggleRecommendable(\''.$route.'\', \''.$csrf.'\')" '.$checked.'>
                          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[\'\'] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>';
            })
            ->editColumn('action', function ($pharmacy) {
                $editUrl = route('addpharmacy', $pharmacy->id);
                $deleteUrl = route('deletepharmacy', $pharmacy->id);
                $medicineUrl = route('medicine', $pharmacy->id);
                $aiBtn = '<button type="button" onclick="quickAiEnhancePharmacy(' . $pharmacy->id . ', this)" title="Auto-enhance Bio with AI" class="inline-flex items-center px-2 py-1 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white text-xs font-medium rounded shadow-sm transition-all"><i class="fa-solid fa-wand-magic-sparkles mr-1"></i> AI Bio</button>';
                $editBtn = '<a href="' . $editUrl . '" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-200" title="Edit"><i class="uil uil-pen text-lg"></i></a>';
                $deleteBtn = '<a href="' . $deleteUrl . '" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200" title="Delete"><i class="uil uil-trash-alt text-lg"></i></a>';
                $medicineBtn = '<a href="' . $medicineUrl . '" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">Medicine</a>';
                return '<div class="flex items-center space-x-2">' . $aiBtn . $editBtn . $deleteBtn . $medicineBtn . '</div>';
            })
            ->rawColumns(['image', 'action', 'recommendable'])
            ->make(true);
    }

    public function addpharmacy($id)
    {
        $data = Doctors::find($id);
        return view("admin.pharmacy.savepharmacy")->with("id", $id)->with("data", $data);
    }

    public function updatepharmacy(Request $request)
    {
        if ($request->get("id") == 0) {
            $store = new Doctors();
            $data = Doctors::where("email", $request->get("email"))->first();
            if ($data) {
                Session::flash('message', __("Email Already Existe"));
                Session::flash('alert-class', 'alert-danger');
                return redirect()->back();
            }
            $msg = __("Pharmacy Add Successfully");
            $img_url = "profile.png";
            $rel_url = "";
        } else {
            $store = Doctors::find($request->get("id"));
            $msg = __("Pharmacy Update Successfully");
            $img_url = $store->image;
            $rel_url = $store->image;
        }
        if ($request->hasFile('upload_image')) {
            $file = $request->file('upload_image');
            $extension = $file->getClientOriginalExtension() ?: 'png';
            $picture = time() . '.' . $extension;
            $request->file('upload_image')->move(public_path('/upload/doctors/'), $picture);
            $img_url = $picture;
            if (file_exists(public_path("/upload/doctors/" . $rel_url)) && $rel_url != "") {
                try { unlink(public_path("/upload/doctors/" . $rel_url)); } catch (Exception $e) {}
            }
        }
        $store->name = $request->get("name");
        $store->password = $request->get("password");
        $store->phoneno = $request->get("phoneno");
        $store->aboutus = $request->get("aboutus");
        $store->services = $request->get("services");
        $store->address = $request->get("address");
        $store->lat = $request->get("lat");
        $store->lon = $request->get("lon");
        $store->email = $request->get("email");
        $store->working_time = $request->get("working_time");
        $store->image = $img_url;
        $store->is_approve = '1';
        $store->profile_type = '2';
        $store->save();

        Session::flash('message', $msg);
        Session::flash('alert-class', 'alert-success');
        return redirect("admin/pharmacy");
    }

    public function deletepharmacy($id)
    {
        $data = Doctors::find($id);
        $data->delete();
        return redirect()->back();
    }

    public function toggleRecommendable(Doctors $pharmacy)
    {
        $pharmacy->recommendable = !$pharmacy->recommendable;
        $pharmacy->save();
        return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
    }

    public function medicine($pharmacy_id)
    {
        $data = PharmacyProduct::where('pharmacy_id', $pharmacy_id)->get();
        return view('admin.pharmacy.pharmacymedicine', compact('pharmacy_id', 'data'));
    }

    public function addmedicine($pharmacy_id, $medicine_id)
    {
        $data = PharmacyProduct::find($medicine_id);
        return view('admin.pharmacy.savemedicine', compact('pharmacy_id', 'data', 'medicine_id'));
    }

    public function updatemedicine(Request $request)
    {
        $is_creating = $request->medicine_id == 0;
        $request->validate([
            'pharmacy_id' => 'required|exists:doctors,id',
            'name' => $is_creating ? 'required|string|max:255' : 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => $is_creating ? 'required|numeric|min:0' : 'nullable|numeric|min:0',
            'upload_image' => $is_creating ? 'required|image|mimes:jpeg,png,jpg,gif|max:2048' : 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($is_creating) {
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
            if (!$is_creating && $data->image && file_exists(public_path('/upload/pharmacymedicine/' . $data->image))) {
                unlink(public_path('/upload/pharmacymedicine/' . $data->image));
            }
            $img_url = $picture;
        }

        $data->image = $img_url;
        $data->pharmacy_id = $request->pharmacy_id;
        if ($request->filled('name')) $data->name = $request->name;
        if ($request->filled('description')) $data->description = $request->description;
        if ($request->filled('price')) $data->price = $request->price;
        $data->save();

        Session::flash('message', $msg);
        Session::flash('alert-class', 'alert-success');
        return redirect()->route('medicine', $request->pharmacy_id);
    }

    public function medicinedelete($id)
    {
        $data = PharmacyProduct::find($id);
        $data->delete();
        return redirect()->back();
    }

    public function pharmacyorder()
    {
        return view('admin.pharmacy.pharmacyorder');
    }

    public function pharmacyOrderTable()
    {
        $orderdata = PharmacyOrder::with(['pharmacy', 'user'])->get();
        return DataTables::of($orderdata)
            ->editColumn('pharmacy_name', fn($order) => $order->pharmacy->name ?? 'N/A')
            ->editColumn('user_name', fn($order) => $order->user->name ?? 'N/A')
            ->editColumn('total', fn($order) => (Setting::first()->currency ?? '$') . number_format($order->total, 2))
            ->editColumn('order_type', fn($order) => $order->order_type == 1 ? __('Prescription') : __('Direct Order'))
            ->editColumn('status', function ($order) {
                $statuses = [0 => __('Pending'), 1 => __('Accepted'), 2 => __('Rejected'), 3 => __('Completed'), 5 => __('Price Added')];
                return $statuses[$order->status] ?? '';
            })
            ->editColumn('action', function ($order) {
                $btns = '';
                if ($order->status == 0) $btns .= '<a href="' . url('admin/pharmacyorderchangestatus/' . $order->id . '/1') . '" class="btn btn-sm btn-success">' . __('Accept') . '</a> <a href="' . url('admin/pharmacyorderchangestatus/' . $order->id . '/2') . '" class="btn btn-sm btn-danger">' . __('Reject') . '</a> <button onclick="addprice(' . $order->id . ')" class="btn btn-sm btn-primary">' . __('Add Price') . '</button>';
                elseif ($order->status == 1 || $order->status == 5) $btns .= '<a href="' . url('admin/pharmacyorderchangestatus/' . $order->id . '/3') . '" class="btn btn-sm btn-purple">' . __('Complete') . '</a> <a href="' . url('admin/pharmacyorderchangestatus/' . $order->id . '/2') . '" class="btn btn-sm btn-danger">' . __('Reject') . '</a>';
                return $btns;
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}