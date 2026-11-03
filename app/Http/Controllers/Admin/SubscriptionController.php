<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SubscriptionFeature;
use App\Models\Subscription;
use App\Models\Setting;
use Illuminate\Support\Facades\Session;
use DataTables;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    /**
     * Display the subscription plans management page.
     */
    public function index()
    {
        if (Session::has('admin')) {
            $setting = Setting::find(1);
            // Corrected the view path from 'subscriptions.index' to 'subscription.index'
            return view("admin.subscription.index", compact('setting'));
        }
        return redirect("admin");
    }

    /**
     * Process DataTables ajax request.
     */
    public function table()
    {
        $subscriptions = Subscription::query();

        return DataTables::of($subscriptions)
            ->editColumn('price', function ($subscription) {
                $setting = Setting::first();
                return ($setting->currency ?? '$') . number_format($subscription->price, 2);
            })
            ->editColumn('duration', function ($subscription) {
                return $subscription->duration . ' ' . Str::plural($subscription->duration_unit, $subscription->duration);
            })
            ->editColumn('user_type', function ($subscription) {
                return ucfirst($subscription->user_type);
            })
            ->editColumn('ai_credits', function ($subscription) {
                return $subscription->ai_credits ?? 'N/A';
            })
            ->editColumn('is_active', function ($subscription) {
                if ($subscription->is_active) {
                    return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100">' . __("Active") . '</span>';
                }
                return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100">' . __("Inactive") . '</span>';
            })
            ->addColumn('action', function ($subscription) {
                $editButton = '<button type="button" @click=\'$dispatch("open-modal", ' . json_encode($subscription) . ')\' class="p-2 rounded-full text-blue-600 hover:bg-blue-100 dark:hover:bg-gray-700 text-xl" title="Edit"><i class="uil uil-pen"></i></button>';
                // We will add the "Manage Features" button in a later step.
                $featuresButton = '<a href="' . route('admin.subscriptions.features.index', $subscription->id) . '" class="p-2 rounded-full text-green-600 hover:bg-green-100 dark:hover:bg-gray-700 text-xl" title="Manage Features"><i class="uil uil-cog"></i></a>';
                $deleteUrl = route('admin.subscriptions.destroy', $subscription->id);
                $deleteButton = '<button type="button" data-url="' . $deleteUrl . '" class="delete-btn p-2 rounded-full text-red-600 hover:bg-red-100 dark:hover:bg-gray-700 text-xl" title="Delete"><i class="uil uil-trash-alt"></i></button>';
                return '<div class="flex items-center space-x-2">' . $editButton . $featuresButton . $deleteButton . '</div>';
            })
            ->rawColumns(['is_active', 'action', 'featured_status'])
            ->make(true);
    }

    /**
     * Store a newly created subscription plan in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'duration_unit' => ['required', Rule::in(['day', 'week', 'month', 'year'])],
            'ai_credits' => 'nullable|integer|min:0',
            'user_type' => ['required', Rule::in(['patient', 'doctor', 'pharmacy', 'corporate'])],
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        Subscription::create([
            'name' => $request->name,
            'price' => $request->price,
            'duration' => $request->duration,
            'duration_unit' => $request->duration_unit,
            'user_type' => $request->user_type,
            'ai_credits' => $request->ai_credits,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return response()->json(['success' => true, 'message' => __('Subscription plan created successfully.')]);
    }

    /**
     * Update the specified subscription plan in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:subscriptions,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'duration_unit' => ['required', Rule::in(['day', 'week', 'month', 'year'])],
            'ai_credits' => 'nullable|integer|min:0',
            'user_type' => ['required', Rule::in(['patient', 'doctor', 'pharmacy', 'corporate'])],
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $subscription = Subscription::findOrFail($request->id);
        
        $data = $request->except(['_token', 'id']);
        // Explicitly handle the checkbox value, as unchecked boxes are not submitted in the form.
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        $subscription->update($data);

        return response()->json(['success' => true, 'message' => __('Subscription plan updated successfully.')]);
    }

    /**
     * Remove the specified subscription plan from storage.
     */
    public function destroy(Subscription $subscription)
    {
        try {
            $subscription->delete();
            return response()->json(['success' => true, 'message' => __('Subscription plan deleted successfully.')]);
        } catch (\Exception $e) {
            \Log::error('Subscription deletion failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => __('Failed to delete the subscription plan. It might be in use.')], 500);
        }
    }

    /**
     * Show the form for managing features of a specific subscription plan.
     */
    public function manageFeatures(Subscription $subscription)
    {
        if (Session::has('admin')) {
            $features = $subscription->features;
            $setting = Setting::find(1);

            // Define all possible predefined features the system understands
            $allPredefinedFeatures = [
                'doctor' => [
                    ['code' => 'max_appointments', 'name' => 'Monthly Appointment Limit', 'description' => 'Limits the total number of appointments a doctor can receive per month.'],
                    ['code' => 'max_time_slots', 'name' => 'Weekly Time Slot Limit', 'description' => 'Limits the total number of time slots a doctor can create in their schedule.'],
                    ['code' => 'force_contact_visibility', 'name' => 'Force Contact Info Visibility', 'description' => 'Overrides global settings to show doctor contact info. Set value to "true".'],
                    ['code' => 'ai_prescription_assistant', 'name' => 'AI Prescription Assistant', 'description' => 'Enables AI-powered suggestions for prescriptions. The subscription\'s AI Credits will be used.'],
                ],
                'patient' => [
                    ['code' => 'limit_doctor_booking', 'name' => 'Monthly Booking Limit', 'description' => 'Limits how many appointments a patient can book per month.'],
                    ['code' => 'limit_medical_records', 'name' => 'Medical Record Limit', 'description' => 'Limits the total number of medical records a patient can upload.'],
                    ['code' => 'limit_health_data', 'name' => 'Health Data Entry Limit', 'description' => 'Limits the number of health data entries a patient can add.'],
                    ['code' => 'force_contact_visibility', 'name' => 'Force Contact Info Visibility', 'description' => 'Overrides global settings to show provider contact info. Set value to "true".'],
                ],
                'pharmacy' => [
                    ['code' => 'limit_medicines', 'name' => 'Medicine Inventory Limit', 'description' => 'Limits the number of unique medicines a pharmacy can list.'],
                ],
                'corporate' => [], // Placeholder for future corporate features
            ];

            // Get the features relevant to the current subscription's user type
            $predefinedFeatures = $allPredefinedFeatures[$subscription->user_type] ?? [];

            return view("admin.subscription.features.index", compact('subscription', 'features', 'setting', 'predefinedFeatures'));
        }
        return redirect("admin");
    }

    /**
     * Store a newly created subscription feature in storage.
     */
    public function storeFeature(Request $request, Subscription $subscription)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subscription_features,code,NULL,id,subscription_id,' . $subscription->id,
            'value' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            // You might want to add validation for 'sort_order' if you use it
        ]);

        $feature = new SubscriptionFeature($request->all());
        $feature->subscription_id = $subscription->id;
        $feature->save();

        return response()->json(['success' => true, 'message' => __('Feature added successfully.')]);
    }

    /**
     * Update the specified subscription feature in storage.
     */
    public function updateFeature(Request $request, Subscription $subscription, SubscriptionFeature $feature)
    {
        // Ensure the feature belongs to the subscription
        if ($feature->subscription_id !== $subscription->id) {
            abort(404, __('Feature not found for this subscription.'));
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('subscription_features')->ignore($feature->id)->where(function ($query) use ($subscription) {
                    return $query->where('subscription_id', $subscription->id);
                }),
            ],
            'value' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            // You might want to add validation for 'sort_order'
        ]);

        $feature->update($request->all());

        return response()->json(['success' => true, 'message' => __('Feature updated successfully.')]);
    }

    /**
     * Remove the specified subscription feature from storage.
     */
    public function destroyFeature(Subscription $subscription, SubscriptionFeature $feature)
    {
        // Ensure the feature belongs to the subscription
        if ($feature->subscription_id !== $subscription->id) {
            abort(404, __('Feature not found for this subscription.'));
        }

        try {
            $feature->delete();
            return response()->json(['success' => true, 'message' => __('Feature deleted successfully.')]);
        } catch (\Exception $e) {
            \Log::error('Feature deletion failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => __('Failed to delete the feature.')], 500);
        }
    }
}