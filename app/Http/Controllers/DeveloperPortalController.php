<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeveloperKey;
use App\Models\Doctors;
use App\Models\Department;
use App\Models\Services;
use Illuminate\Support\Facades\Validator;

class DeveloperPortalController extends Controller
{
    /**
     * Show Public Developer API Documentation Page
     */
    public function docs()
    {
        $setting = \App\Models\Setting::find(1);
        $isLoggedIn = auth()->check() 
            || \Illuminate\Support\Facades\Session::has('user_id') 
            || \Illuminate\Support\Facades\Session::has('admin') 
            || (class_exists('\Cartalyst\Sentinel\Laravel\Facades\Sentinel') && \Cartalyst\Sentinel\Laravel\Facades\Sentinel::check());

        return view('developers.api_docs', compact('setting', 'isLoggedIn'));
    }

    /**
     * Handle Developer Self-Registration to get API Keys
     */
    public function register(Request $request)
    {
        $isLoggedIn = auth()->check() 
            || \Illuminate\Support\Facades\Session::has('user_id') 
            || \Illuminate\Support\Facades\Session::has('admin') 
            || (class_exists('\Cartalyst\Sentinel\Laravel\Facades\Sentinel') && \Cartalyst\Sentinel\Laravel\Facades\Sentinel::check());

        if (!$isLoggedIn) {
            return response()->json([
                'success' => false,
                'require_auth' => true,
                'message' => 'Please sign in or create an account before generating developer API credentials.'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'app_name' => 'required|string|max:150',
            'developer_name' => 'required|string|max:100',
            'developer_email' => 'required|email|max:150',
            'allowed_domains' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $devKey = DeveloperKey::generateCredentials(
            $request->app_name,
            $request->developer_name,
            $request->developer_email,
            $request->allowed_domains
        );

        return response()->json([
            'success' => true,
            'message' => 'API Credentials generated successfully! Please store your secret key securely.',
            'credentials' => [
                'app_name' => $devKey->app_name,
                'developer_email' => $devKey->developer_email,
                'api_key' => $devKey->api_key,
                'secret_key' => $devKey->secret_key,
                'rate_limit' => $devKey->rate_limit_per_minute . ' req/min',
                'created_at' => $devKey->created_at->toIso8601String()
            ]
        ], 201);
    }

    /**
     * Public V1 API: List Doctors
     */
    public function getDoctors(Request $request)
    {
        $query = Doctors::with(['department'])
            ->where('status', '1')
            ->where(function ($q) {
                $q->whereNull('role_id')->orWhere('role_id', '!=', 2); // Exclude pharmacies
            });

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->has('city')) {
            $query->where('city', 'LIKE', '%' . $request->city . '%');
        }

        $doctors = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $doctors->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'name' => $doc->name,
                    'email' => $doc->email,
                    'phone' => $doc->phone_no,
                    'specialty' => $doc->department->name ?? 'General Practitioner',
                    'consultation_fee' => (float)$doc->consultation_fees,
                    'address' => $doc->address,
                    'city' => $doc->city,
                    'latitude' => (float)$doc->lat,
                    'longitude' => (float)$doc->lon,
                    'profile_image' => $doc->image ? url('upload/doctors/' . $doc->image) : null,
                    'services' => $doc->services,
                    'healthcare_provider' => $doc->healthcare,
                ];
            }),
            'pagination' => [
                'total' => $doctors->total(),
                'current_page' => $doctors->currentPage(),
                'last_page' => $doctors->lastPage(),
                'per_page' => $doctors->perPage(),
            ]
        ]);
    }

    /**
     * Public V1 API: List Pharmacies
     */
    public function getPharmacies(Request $request)
    {
        $query = Doctors::where('role_id', 2)
            ->where('status', '1');

        if ($request->has('city')) {
            $query->where('city', 'LIKE', '%' . $request->city . '%');
        }

        $pharmacies = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $pharmacies->map(function ($pharm) {
                return [
                    'id' => $pharm->id,
                    'pharmacy_name' => $pharm->name,
                    'phone' => $pharm->phone_no,
                    'address' => $pharm->address,
                    'city' => $pharm->city,
                    'latitude' => (float)$pharm->lat,
                    'longitude' => (float)$pharm->lon,
                    'delivery_mode' => $pharm->delivery_mode ?? 'pointlink',
                    'profile_image' => $pharm->image ? url('upload/doctors/' . $pharm->image) : null,
                ];
            }),
            'pagination' => [
                'total' => $pharmacies->total(),
                'current_page' => $pharmacies->currentPage(),
                'last_page' => $pharmacies->lastPage(),
                'per_page' => $pharmacies->perPage(),
            ]
        ]);
    }

    /**
     * Public V1 API: List Medical Departments & Specialties
     */
    public function getDepartments(Request $request)
    {
        $departments = Department::where('status', '1')->get();

        return response()->json([
            'success' => true,
            'data' => $departments->map(function ($dept) {
                return [
                    'id' => $dept->id,
                    'name' => $dept->name,
                    'description' => $dept->description ?? '',
                    'icon' => $dept->image ? url('upload/department/' . $dept->image) : null,
                ];
            })
        ]);
    }
}
