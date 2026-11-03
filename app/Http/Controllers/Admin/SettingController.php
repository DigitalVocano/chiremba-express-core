<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting; // Ensure this is correctly pointing to your Setting model
use Illuminate\Support\Str; // For string operations, e.g., slug for file names

class SettingController extends Controller
{
    /**
     * Show the form for editing all admin settings.
     * Includes all general settings, doctor contact visibility, and Google Tag Manager ID.
     *
     * @return \Illuminate\View\View
     */
    public function showNewSettings()
    {
        // Fetch the single settings row. If it doesn't exist, create a new instance
        // but don't save it yet unless explicitly needed. firstOrNew() is good for this.
        $data = Setting::firstOrNew([]);

        // Get timezones for the dropdown
        $timezone = collect(\DateTimeZone::listIdentifiers(\DateTimeZone::ALL))
            ->mapWithKeys(function ($zone) {
                return [$zone => str_replace(['_', '/'], [' ', ' - '], $zone)];
            })->sort();

        // Pass the single settings object as 'data' and timezones to the view
        return view('admin.setting', compact('data', 'timezone'));
    }

    /**
     * Store or update all admin settings.
     * This method will now handle updates for all fields present in the settings form,
     * including doctor contact visibility, Google Tag Manager ID, and image uploads.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatesetting(Request $request)
    {
        // Find the single settings row, or create it if it doesn't exist
        $settings = Setting::firstOrNew([]); // Ensures a row exists to update

        // Define validation rules for all fields that can be updated from the settings page
        // You can adjust 'required' based on your specific needs for each field.
        $rules = [
            // From 'Basic Details' tab (originally updatesettingone)
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'app_url' => 'nullable|url|max:255',
            'playstore_url' => 'nullable|url|max:255',

            // From 'Upload Section' tab (originally updatesettingtwo)
            'title' => 'nullable|string|max:255',
            'main_banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,ico|max:2048',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'app_banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'icon1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'icon2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'icon3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            // From 'Admin Basic Details' tab (originally updatesettingfour)
            'commission' => 'nullable|numeric|min:0|max:100',
            'timezone' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:10',
            'pharmacy_tax' => 'nullable|numeric|min:0|max:100',
            'pharmacy_delivery_charge' => 'nullable|numeric|min:0',
            'doctor_approved' => 'boolean', // Checkbox should send '0' or '1'
            'is_wallet_withdrawal_enabled' => 'boolean', // New: Wallet withdrawal enable/disable
            'is_wallet_enabled' => 'boolean', // New: Wallet enable/disable
            'is_rtl' => 'boolean', // Checkbox should send '0' or '1'
            'is_doctor_subscription_enabled' => 'boolean',
            'is_patient_subscription_enabled' => 'boolean',
            'is_pharmacy_subscription_enabled' => 'boolean',
            'freemium_doctor_appointment_limit' => 'nullable|integer|min:0',
            'freemium_patient_consultation_limit' => 'nullable|integer|min:0',

            // New fields for doctor contact visibility and Google Tag Manager ID
            'doctor_contact_visibility' => 'required|in:0,1', // 0 for hide, 1 for show
            // LiveKit Telehealth Settings
            'livekit_url' => 'nullable|string',
            'livekit_api_key' => 'nullable|string',
            'livekit_api_secret' => 'nullable|string',
            'agora_app_id' => 'nullable|string|max:255',
            'agora_app_certificate' => 'nullable|string|max:255',

            // Zimflow & Delivery partner fields
            'zimflow_api_token' => 'nullable|string',
            'zimflow_api_endpoint' => 'nullable|url',
            'zimflow_auth_template' => 'nullable|string',
            'zimflow_template_language' => 'nullable|string',
            'is_whatsapp_enabled' => 'boolean',
            'default_delivery_partner' => 'nullable|string',
            'linkgrid_api_endpoint' => 'nullable|url',
            'linkgrid_api_token' => 'nullable|string',
            'pointlink_max_delivery_fee' => 'nullable|numeric|min:0',
        ];

        // Apply validation
        $request->validate($rules);

        // Update all text/numeric/checkbox fields
        $settings->phone = $request->input('phone');
        $settings->email = $request->input('email');
        $settings->address = $request->input('address');
        $settings->app_url = $request->input('app_url');
        $settings->playstore_url = $request->input('playstore_url');
        $settings->title = $request->input('title');
        $settings->commission = $request->input('commission');
        $settings->timezone = $request->input('timezone');
        $settings->currency = $request->input('currency');
        $settings->pharmacy_tax = $request->input('pharmacy_tax');
        $settings->pharmacy_delivery_charge = $request->input('pharmacy_delivery_charge');
        $settings->is_commission_required = $request->has('is_commission_required') ? '1' : '0';
        $settings->is_patient_wallet_enabled = $request->has('is_patient_wallet_enabled') ? '1' : '0';
        $settings->payment_mode = $request->input('payment_mode', 'platform_gateway');
        $settings->is_wallet_enabled = $request->has('is_wallet_enabled') ? '1' : '0'; // New: Wallet enable/disable
        $settings->is_wallet_withdrawal_enabled = $request->has('is_wallet_withdrawal_enabled') ? '1' : '0'; // New: Wallet withdrawal enable/disable
        $settings->doctor_approved = $request->has('doctor_approved') ? '1' : '0';
        $settings->is_rtl = $request->has('is_rtl') ? '1' : '0';
        $settings->is_doctor_subscription_enabled = $request->has('is_doctor_subscription_enabled') ? '1' : '0';
        $settings->is_patient_subscription_enabled = $request->has('is_patient_subscription_enabled') ? '1' : '0';
        $settings->is_pharmacy_subscription_enabled = $request->has('is_pharmacy_subscription_enabled') ? '1' : '0';
        $settings->freemium_doctor_appointment_limit = $request->input('freemium_doctor_appointment_limit', 0);
        $settings->freemium_patient_consultation_limit = $request->input('freemium_patient_consultation_limit', 0);
        $settings->doctor_contact_visibility = $request->input('doctor_contact_visibility');
        $settings->google_tag_manager_id = $request->input('google_tag_manager_id');

        // Safely assign optional telephony, zimflow & delivery fields only if columns exist in DB
        $optionalFields = [
            'livekit_url' => $request->input('livekit_url'),
            'livekit_api_key' => $request->input('livekit_api_key'),
            'livekit_api_secret' => $request->input('livekit_api_secret'),
            'agora_app_id' => $request->input('agora_app_id'),
            'agora_app_certificate' => $request->input('agora_app_certificate'),
            'zimflow_api_token' => $request->input('zimflow_api_token'),
            'zimflow_api_endpoint' => $request->input('zimflow_api_endpoint'),
            'zimflow_auth_template' => $request->input('zimflow_auth_template'),
            'zimflow_template_language' => $request->input('zimflow_template_language'),
            'is_whatsapp_enabled' => $request->has('is_whatsapp_enabled') ? '1' : '0',
            'default_delivery_partner' => $request->input('default_delivery_partner'),
            'linkgrid_api_endpoint' => $request->input('linkgrid_api_endpoint'),
            'linkgrid_api_token' => $request->input('linkgrid_api_token'),
            'pointlink_max_delivery_fee' => $request->input('pointlink_max_delivery_fee'),
        ];
        foreach ($optionalFields as $col => $val) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('setting', $col)) {
                $settings->{$col} = $val;
            }
        }


        // Handle file uploads for all image fields
        $imageFields = ['main_banner', 'favicon', 'logo', 'app_banner', 'icon1', 'icon2', 'icon3'];
        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                // Delete old image if it exists and a new one is being uploaded
                if ($settings->{$field} && file_exists(public_path('image_web/' . $settings->{$field}))) {
                    unlink(public_path('image_web/' . $settings->{$field}));
                }
                // Store new image
                $image = $request->file($field);
                // Generate a unique file name to prevent conflicts
                $fileName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                // Store the file in the 'image_web' directory
                $image->move(public_path('image_web'), $fileName);
                $settings->{$field} = $fileName;
            }
        }

        $settings->save(); // Save all changes to the database

        return redirect()->back()->with('success', 'All settings updated successfully!');
    }
}