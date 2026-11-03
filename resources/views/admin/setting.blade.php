@extends('admin.layout')
@section('title')
{{("Setting")}} | {{("Admin")}}
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
   <div class="page-content p-6">
      <div class="container-fluid mx-auto">
         <!-- Page Title -->
         <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{("Setting")}}</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">
               {{("Setting")}}
            </div>
         </div>

         <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            @if(Session::has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 rounded-lg bg-green-100 dark:bg-green-200 p-4 text-sm text-green-700 dark:text-green-800 flex justify-between items-center" role="alert">
               <span>{{ Session::get('message') }}</span>
               <button @click="show = false" class="text-green-900 dark:text-green-800 font-bold">&times;</button>
            </div>
            @endif

            <h4 class="text-xl font-semibold text-gray-800 dark:text-white mb-6">{{("Setting")}}</h4>

            <div x-data="{ activeTab: 'admin' }" class="flex flex-col lg:flex-row gap-8">
               <!-- Vertical Nav -->
               <div class="w-full lg:w-1/4">
                  <ul class="space-y-1">
                     <li>
                        <a @click.prevent="activeTab = 'admin'" href="#" :class="{ 'bg-primary-500 text-white': activeTab === 'admin', 'hover:bg-gray-100 dark:hover:bg-gray-700': activeTab !== 'admin' }" class="flex items-center px-4 py-3 rounded-md text-gray-700 dark:text-gray-300 transition-colors duration-200">
                           <span class="font-bold w-8 text-center">01</span>
                           <span>{{("Admin")}} {{("Basic Details")}}</span>
                        </a>
                     </li>
                     <?php if(env('IS_FORNT')=="1") { ?>
                     <li>
                        <a @click.prevent="activeTab = 'basic'" href="#" :class="{ 'bg-primary-500 text-white': activeTab === 'basic', 'hover:bg-gray-100 dark:hover:bg-gray-700': activeTab !== 'basic' }" class="flex items-center px-4 py-3 rounded-md text-gray-700 dark:text-gray-300 transition-colors duration-200">
                           <span class="font-bold w-8 text-center">02</span>
                           <span>{{("Basic Details")}}</span>
                        </a>
                     </li>
                     <li>
                        <a @click.prevent="activeTab = 'upload'" href="#" :class="{ 'bg-primary-500 text-white': activeTab === 'upload', 'hover:bg-gray-100 dark:hover:bg-gray-700': activeTab !== 'upload' }" class="flex items-center px-4 py-3 rounded-md text-gray-700 dark:text-gray-300 transition-colors duration-200">
                           <span class="font-bold w-8 text-center">03</span>
                           <span>{{("Upload Section")}}</span>
                        </a>
                     </li>
                     <?php } ?>
                     <li>
                        <a @click.prevent="activeTab = 'whatsapp_delivery'" href="#" :class="{ 'bg-primary-500 text-white': activeTab === 'whatsapp_delivery', 'hover:bg-gray-100 dark:hover:bg-gray-700': activeTab !== 'whatsapp_delivery' }" class="flex items-center px-4 py-3 rounded-md text-gray-700 dark:text-gray-300 transition-colors duration-200">
                           <span class="font-bold w-8 text-center">04</span>
                           <span>{{__("WhatsApp & Delivery")}}</span>
                        </a>
                     </li>
                  </ul>
               </div>

               <!-- Tab Content -->
               <div class="w-full lg:w-3/4">
                  <form action="{{ url('admin/updatesetting') }}" method="post" enctype="multipart/form-data">
                     {{csrf_field()}}
                  <!-- Admin Details Tab -->
                  <div x-show="activeTab === 'admin'" style="display: none;">
                     <div class="space-y-6">
                        <div>
                           <label for="commission_input" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{("Commission")}} (%)</label>
                           <input type="number" required name="commission" value="{{$data->commission ?? ''}}" id="commission_input" min="1" step="0.01" max="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        </div>

                        <div>
                           <label for="timezone_select" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{('timezone')}} <span class="text-red-500">*</span></label>
                           <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" name="timezone" id="timezone_select" required>
                              <option value="">{{('select_timezone')}}</option>
                              @foreach($timezone as $tz=>$value)
                              <option value="{{$tz}}" <?=isset($data->timezone) && $data->timezone ==$tz ? ' selected="selected"' : '';?>>{{$value}}</option>
                              @endforeach
                           </select>
                        </div>

                        <div>
                           <label for="currency_select" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{('currency')}} <span class="text-red-500">*</span></label>
                           <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" name="currency" id="currency_select" required>
                              <option value="{{$data->currency ?? ''}}" selected>{{$data->currency ?? ''}}</option>
                              @include('admin.currency')
                           </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                           <div>
                              <label for="pharmacy_tax_input" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{("Pharmacy Tax")}}(%)</label>
                              <input type="number" required name="pharmacy_tax" value="{{$data->pharmacy_tax ?? ''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="pharmacy_tax_input">
                           </div>
                           <div>
                              <label for="pharmacy_delivery_charge_input" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{("Pharmacy Delivery Charge")}}</label>
                              <input type="number" required name="pharmacy_delivery_charge" value="{{$data->pharmacy_delivery_charge ?? ''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="pharmacy_delivery_charge_input">
                           </div>
                        </div>

                        <div class="space-y-2">
                           <label class="flex items-center">
                              <input type="hidden" name="is_commission_required" value="0">
                              <input type="checkbox" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" id="is_commission_required" name="is_commission_required" value="1" {{ (!isset($data->is_commission_required) || $data->is_commission_required == '1') ? 'checked' : '' }}>
                              <span class="ml-2 text-sm text-gray-600 dark:text-gray-400 font-semibold text-primary-700 dark:text-primary-400">{{("Enforce Wallet Balance Commission Requirement (Disable during promo launch)")}}</span>
                           </label>
                           <label class="flex items-center">
                              <input type="checkbox" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" id="doctor_approved_checkbox" name="doctor_approved" value="1" <?=isset($data->doctor_approved)&&$data->doctor_approved=='1'?'checked="checked"':""?>>
                              <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{("You Need To Approve Doctors Profile")}}</span>
                           </label>
                           <label class="flex items-center">
                              <input type="checkbox" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" id="is_rtl_checkbox" <?=isset($data->is_rtl)&&$data->is_rtl=='1'?'checked="checked"':""?> name="is_rtl" value="1">
                              <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{("Is RTL")}}</span>
                           </label>
                        </div>
                        <div class="space-y-2">
                           <label class="flex items-center">
                              <input type="checkbox" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" id="is_wallet_enabled" name="is_wallet_enabled" value="1" {{ (isset($data->is_wallet_enabled) && $data->is_wallet_enabled == '1') ? 'checked' : '' }}>
                              <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{("Enable Wallet Feature")}}</span>
                           </label>
                           <label class="flex items-center">
                              <input type="checkbox" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" id="is_wallet_withdrawal_enabled" name="is_wallet_withdrawal_enabled" value="1" {{ (isset($data->is_wallet_withdrawal_enabled) && $data->is_wallet_withdrawal_enabled == '1') ? 'checked' : '' }}>
                              <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{("Enable Wallet Withdrawal")}}</span>
                           </label>
                        </div>

                        <div class="space-y-3 p-4 bg-blue-50/50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg my-4">
                           <h6 class="text-sm font-bold text-[#0A2B68] dark:text-blue-300 uppercase tracking-wider flex items-center gap-1"><i class="uil uil-credit-card"></i> Business Payment Model & Apple Policy Controls</h6>
                           
                           <label class="flex items-center">
                              <input type="hidden" name="is_patient_wallet_enabled" value="0">
                              <input type="checkbox" class="rounded border-gray-300 text-primary-600 shadow-sm" name="is_patient_wallet_enabled" value="1" {{ (!isset($data->is_patient_wallet_enabled) || $data->is_patient_wallet_enabled == '1') ? 'checked' : '' }}>
                              <span class="ml-2 text-sm font-semibold text-gray-700 dark:text-gray-300">{{("Enable Patient Wallet at Checkout (Disable to comply with Apple iOS Policy)")}}</span>
                           </label>

                           <div class="pt-2">
                              <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">{{("Primary Business Payment Model")}}</label>
                              <select name="payment_mode" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 text-sm">
                                  <option value="platform_gateway" {{ (isset($data->payment_mode) && $data->payment_mode == 'platform_gateway') ? 'selected' : '' }}>{{("Platform Gateway Settlement (ZimFlow / Card / In-App Checkout)")}}</option>
                                  <option value="direct_provider" {{ (isset($data->payment_mode) && $data->payment_mode == 'direct_provider') ? 'selected' : '' }}>{{("Direct Provider Mode (Direct EcoCash / Bank Transfer P2P + Provider Deposit Wallet Commission)")}}</option>
                              </select>
                              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                  In Direct Provider Mode, patients pay doctors and pharmacies directly via EcoCash, InnBucks, or Bank Transfer. Chiremba collects platform commission by deducting it from the provider's deposit wallet balance.
                              </p>
                           </div>
                        </div>

                        <hr class="dark:border-gray-700">

                        <h5 class="text-lg font-medium text-gray-900 dark:text-white">{{("Subscription System Settings")}}</h5>
                        <div class="space-y-2 p-4 border rounded-md dark:border-gray-700">
                           <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                               {{__("Enable or disable subscription requirements for different user groups.")}}
                           </p>
                           <label class="flex items-center">
                              <input type="hidden" name="is_doctor_subscription_enabled" value="0">
                              <input type="checkbox" class="rounded border-gray-300 text-primary-600 shadow-sm" name="is_doctor_subscription_enabled" value="1" {{ (isset($data->is_doctor_subscription_enabled) && $data->is_doctor_subscription_enabled == '1') ? 'checked' : '' }}>
                              <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{("Require Subscription for Doctors")}}</span>
                           </label>
                           <label class="flex items-center">
                              <input type="hidden" name="is_patient_subscription_enabled" value="0">
                              <input type="checkbox" class="rounded border-gray-300 text-primary-600 shadow-sm" name="is_patient_subscription_enabled" value="1" {{ (isset($data->is_patient_subscription_enabled) && $data->is_patient_subscription_enabled == '1') ? 'checked' : '' }}>
                              <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{("Require Subscription for Patients")}}</span>
                           </label>
                           <label class="flex items-center">
                              <input type="hidden" name="is_pharmacy_subscription_enabled" value="0">
                              <input type="checkbox" class="rounded border-gray-300 text-primary-600 shadow-sm" name="is_pharmacy_subscription_enabled" value="1" {{ (isset($data->is_pharmacy_subscription_enabled) && $data->is_pharmacy_subscription_enabled == '1') ? 'checked' : '' }}>
                              <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{("Require Subscription for Pharmacies")}}</span>
                           </label>

                           <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 pt-4 border-t dark:border-gray-700">
                              <div>
                                 <label class="block font-medium text-xs text-gray-700 dark:text-gray-300">{{("Freemium Free Appointments Limit for Doctors (0 = Unlimited Free)")}}</label>
                                 <input type="number" name="freemium_doctor_appointment_limit" min="0" value="{{ $data->freemium_doctor_appointment_limit ?? 0 }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm dark:bg-gray-800 dark:text-white dark:border-gray-600">
                              </div>
                              <div>
                                 <label class="block font-medium text-xs text-gray-700 dark:text-gray-300">{{("Freemium Free Consultations Limit for Patients (0 = Unlimited Free)")}}</label>
                                 <input type="number" name="freemium_patient_consultation_limit" min="0" value="{{ $data->freemium_patient_consultation_limit ?? 0 }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm dark:bg-gray-800 dark:text-white dark:border-gray-600">
                              </div>
                           </div>
                        </div>

                        <h5 class="text-lg font-medium text-gray-900 dark:text-white">{{("Additional Settings")}}</h5>

                        <div>
                           <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{("Show Doctor Contact Info on Profile")}}</label>
                           <div class="mt-2 space-x-4">
                              <label class="inline-flex items-center">
                                 <input type="radio" class="text-primary-600" name="doctor_contact_visibility" value="1" {{ (isset($data->doctor_contact_visibility) && $data->doctor_contact_visibility == '1') ? 'checked' : '' }}>
                                 <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{("Show")}}</span>
                              </label>
                              <label class="inline-flex items-center">
                                 <input type="radio" class="text-primary-600" name="doctor_contact_visibility" value="0" {{ (isset($data->doctor_contact_visibility) && $data->doctor_contact_visibility == '0') ? 'checked' : '' }}>
                                 <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{("Hide")}}</span>
                              </label>
                           </div>
                           <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{("Control whether contact details (phone, email) are visible on doctor profiles to patients.")}}</p>
                        </div>

                        <div>
                           <label for="google_tag_manager_id_input" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{("Google Tag Manager ID")}}</label>
                           <input class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" type="text" value="{{$data->google_tag_manager_id ?? ''}}" id="google_tag_manager_id_input" name="google_tag_manager_id" placeholder="e.g., GTM-XXXXXXX">
                           <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{("Enter your Google Tag Manager Container ID (e.g., GTM-XXXXXXX). Leave empty to disable.")}}</p>
                        </div>

                        <hr class="dark:border-gray-700">
                        <h5 class="text-lg font-medium text-gray-900 dark:text-white">{{("LiveKit Telehealth Video & Voice Call Settings")}}</h5>
                        <div class="p-4 bg-gray-100 dark:bg-gray-700/50 rounded-md border border-gray-200 dark:border-gray-600">
                           <p class="text-xs text-gray-600 dark:text-gray-300 mb-2">
                               <i class="uil uil-shield-check text-green-600 font-bold"></i> {{("LiveKit, EcoCash, and Paynow API credentials are securely managed via the server environment (.env).")}}
                           </p>
                           <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                              <div>
                                 <label class="block font-medium text-xs text-gray-500 dark:text-gray-400">{{("LiveKit Server Host URL (.env)")}}</label>
                                 <input type="text" disabled value="{{env('LIVEKIT_URL', 'wss://linkit-6u57ihxm.livekit.cloud')}}" class="mt-1 block w-full text-xs rounded-md bg-gray-200 dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">
                              </div>
                              <div>
                                 <label class="block font-medium text-xs text-gray-500 dark:text-gray-400">{{("LiveKit API Key (.env)")}}</label>
                                 <input type="text" disabled value="{{env('LIVEKIT_API_KEY', 'APIb5BFgiPRoHsp')}}" class="mt-1 block w-full text-xs rounded-md bg-gray-200 dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">
                              </div>
                              <div>
                                 <label class="block font-medium text-xs text-gray-500 dark:text-gray-400">{{("LiveKit API Secret (.env)")}}</label>
                                 <input type="password" disabled value="****************" class="mt-1 block w-full text-xs rounded-md bg-gray-200 dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>

                  <!-- Basic Details Tab -->
                  <div x-show="activeTab === 'basic'" style="display: none;">
                     <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                           <div>
                              <label for="verti-nav-phoneno-input" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{("Phone")}}</label>
                              <input type="text" required name="phone" value="{{$data->phone ?? ''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="verti-nav-phoneno-input">
                           </div>
                           <div>
                              <label for="verti-nav-email-input" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{("Email")}}</label>
                              <input type="email" required name="email" value="{{$data->email ?? ''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="verti-nav-email-input">
                           </div>
                        </div>
                        <div>
                           <label for="verti-nav-address-input" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{("Address")}}</label>
                           <textarea id="verti-nav-address-input" required name="address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" rows="2">{{$data->address ?? ''}}</textarea>
                        </div>
                        <div>
                           <label for="app_url_input" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{("App Store URL")}}</label>
                           <input type="text" required name="app_url" value="{{$data->app_url ?? ''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="app_url_input">
                        </div>
                        <div>
                           <label for="playstore_url_input" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{("Play Store URL")}}</label>
                           <input type="text" required name="playstore_url" value="{{$data->playstore_url ?? ''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="playstore_url_input">
                        </div>
                     </div>
                  </div>

                  <!-- Upload Section Tab -->
                  <div x-show="activeTab === 'upload'" style="display: none;">
                     <div class="space-y-6">
                        <div>
                           <label for="title_input" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{("Name")}}</label>
                           <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" value="{{$data->title ?? ''}}" name="title" id="title_input" required>
                        </div>

                        @php
                           $imageFields = [
                              'main_banner' => ['label' => __("Main Banner"), 'size' => 'h-32 w-auto'],
                              'favicon' => ['label' => __("Favicon"), 'size' => 'h-8 w-8'],
                              'logo' => ['label' => __("LOGO"), 'size' => 'h-12 w-auto'],
                              'app_banner' => ['label' => __("App Banner"), 'size' => 'h-32 w-auto'],
                              'icon1' => ['label' => __("Appointment Process Icon 1"), 'size' => 'h-16 w-16'],
                              'icon2' => ['label' => __("Appointment Process Icon 2"), 'size' => 'h-16 w-16'],
                              'icon3' => ['label' => __("Appointment Process Icon 3"), 'size' => 'h-16 w-16'],
                           ];
                        @endphp

                        @foreach($imageFields as $field => $details)
                        <div>
                           <label for="{{$field}}_input" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ $details['label'] }}</label>
                           @if(isset($data->$field))
                           <div class="mt-2">
                              <img src="{{asset('image_web').'/'.$data->$field}}" class="{{ $details['size'] }} object-contain rounded-md bg-gray-100 p-1">
                           </div>
                           @endif
                           <input type="file" id="{{$field}}_input" name="{{$field}}" class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 dark:file:bg-primary-800 file:text-primary-700 dark:file:text-primary-200 hover:file:bg-primary-100 dark:hover:file:bg-primary-700">
                        </div>
                        @endforeach
                     </div>
                  </div>

                  <!-- WhatsApp & Delivery Settings Tab -->
                  <div x-show="activeTab === 'whatsapp_delivery'" style="display: none;">
                     <div class="space-y-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{__("Zimflow WhatsApp API Settings")}}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                           <div>
                              <label for="is_whatsapp_enabled" class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300">
                                 <input type="checkbox" name="is_whatsapp_enabled" id="is_whatsapp_enabled" value="1" {{($data->is_whatsapp_enabled ?? 1) ? 'checked' : ''}} class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 mr-2">
                                 {{__("Enable WhatsApp OTP & Notifications")}}
                              </label>
                           </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                           <div>
                              <label for="zimflow_api_endpoint" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{__("Zimflow API Endpoint")}}</label>
                              <input type="text" name="zimflow_api_endpoint" id="zimflow_api_endpoint" value="{{$data->zimflow_api_endpoint ?? 'https://zimflow.co.zw/public-api/wpbox/'}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                           </div>
                           <div>
                              <label for="zimflow_api_token" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{__("Zimflow API Token")}}</label>
                              <input type="text" name="zimflow_api_token" id="zimflow_api_token" value="{{$data->zimflow_api_token ?? ''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                           </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                           <div>
                              <label for="zimflow_auth_template" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{__("Zimflow Auth Template Name")}}</label>
                              <input type="text" name="zimflow_auth_template" id="zimflow_auth_template" value="{{$data->zimflow_auth_template ?? 'pointlink_otp'}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                           </div>
                           <div>
                              <label for="zimflow_template_language" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{__("Zimflow Template Language Code")}}</label>
                              <input type="text" name="zimflow_template_language" id="zimflow_template_language" value="{{$data->zimflow_template_language ?? 'en'}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                           </div>
                        </div>

                        <hr class="border-gray-200 dark:border-gray-700 my-6">

                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{__("Delivery Service Settings")}}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                           <div>
                              <label for="default_delivery_partner" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{__("Default Delivery Partner")}}</label>
                              <select name="default_delivery_partner" id="default_delivery_partner" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                 <option value="" {{($data->default_delivery_partner ?? '') == '' ? 'selected' : ''}}>{{__("None / Pharmacy's Own Delivery")}}</option>
                                 <option value="linkgrid" {{($data->default_delivery_partner ?? '') == 'linkgrid' ? 'selected' : ''}}>LinkGrid (PointLink)</option>
                              </select>
                           </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                           <div>
                              <label for="linkgrid_api_endpoint" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{__("LinkGrid API Endpoint")}}</label>
                              <input type="text" name="linkgrid_api_endpoint" id="linkgrid_api_endpoint" value="{{$data->linkgrid_api_endpoint ?? 'https://linkgrid.co.zw/api/v1/'}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                           </div>
                           <div>
                              <label for="linkgrid_api_token" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{__("LinkGrid API Token")}}</label>
                              <input type="text" name="linkgrid_api_token" id="linkgrid_api_token" value="{{$data->linkgrid_api_token ?? ''}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                           </div>
                           <div>
                              <label for="pointlink_max_delivery_fee" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{__("PointLink Max Delivery Fee Cap ($)")}}</label>
                              <input type="number" step="0.5" name="pointlink_max_delivery_fee" id="pointlink_max_delivery_fee" value="{{$data->pointlink_max_delivery_fee ?? '25.00'}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" placeholder="25.00">
                              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{__("Prevents exorbitant intercity delivery fees.")}}</p>
                           </div>
                        </div>
                     </div>
                  </div>

                  <!-- Unified Submit Button -->
                  <div class="flex justify-end mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                     @if(Session::get("is_demo")=='0')
                     <button type="button" onclick="disablebtn()" class="inline-flex items-center px-6 py-3 bg-primary-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">{{('Save Changes')}}</button>
                     @else
                     <button type="submit" class="inline-flex items-center px-6 py-3 bg-primary-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">{{("Save Changes")}}</button>
                     @endif
                  </div>
                  </form>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
@stop
@section('footer')
@stop
