@extends('admin.layout')
@section('title')
{{("System & Use Cases Guide")}} | {{("Admin")}}
@stop
@section('content')
<div class="main-content">
   <div class="page-content p-4 sm:p-6">
      <div class="container-fluid mx-auto space-y-6">

         <!-- Header Hero Card -->
         <div class="admin-header-card rounded-3xl p-6 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xl">
            <div class="flex items-center space-x-4">
               <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center border border-white/20 text-lime-400 text-2xl shadow-inner">
                  <i class="fa-solid fa-book-journal-whills"></i>
               </div>
               <div>
                  <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">{{("System Architecture & Use Cases Guide")}}</h1>
                  <p class="text-xs sm:text-sm text-blue-100/80 mt-1">Complete operational documentation for Zimflow, PointLink Logistics, Pharmacy Fulfillment, and Financial Workflows</p>
               </div>
            </div>
            <div class="flex items-center space-x-2 text-xs bg-white/10 backdrop-blur-md px-4 py-2.5 rounded-xl border border-white/20 self-start sm:self-auto">
               <a href="{{url('admin/dashboard')}}" class="hover:text-lime-300 transition-colors">Home</a>
               <span class="text-white/40">/</span>
               <span class="font-semibold text-lime-400">System Guide</span>
            </div>
         </div>

         <!-- Quick Jump Navigation Tabs -->
         <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <a href="#auth-section" class="p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-lime-500 transition-all flex items-center space-x-3">
               <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-900 flex items-center justify-center font-bold text-lg"><i class="fa-solid fa-shield-halved"></i></div>
               <div>
                  <div class="text-xs font-bold text-gray-900 dark:text-white">Unified Auth & OTP</div>
                  <div class="text-[10px] text-gray-400">Zimflow & Role Detection</div>
               </div>
            </a>
            <a href="#pointlink-section" class="p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-lime-500 transition-all flex items-center space-x-3">
               <div class="w-10 h-10 rounded-xl bg-lime-50 text-lime-700 flex items-center justify-center font-bold text-lg"><i class="fa-solid fa-truck-fast"></i></div>
               <div>
                  <div class="text-xs font-bold text-gray-900 dark:text-white">PointLink Courier</div>
                  <div class="text-[10px] text-gray-400">Multi-Stop Pickups & Recipient</div>
               </div>
            </a>
            <a href="#pharmacy-section" class="p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-lime-500 transition-all flex items-center space-x-3">
               <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-lg"><i class="fa-solid fa-prescription-bottle-medical"></i></div>
               <div>
                  <div class="text-xs font-bold text-gray-900 dark:text-white">Pharmacy Fleet</div>
                  <div class="text-[10px] text-gray-400">Bikes, COD & Digital</div>
               </div>
            </a>
            <a href="#finance-section" class="p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-lime-500 transition-all flex items-center space-x-3">
               <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center font-bold text-lg"><i class="fa-solid fa-wallet"></i></div>
               <div>
                  <div class="text-xs font-bold text-gray-900 dark:text-white">COD Debt & Payouts</div>
                  <div class="text-[10px] text-gray-400">Commission Ledger & Paynow</div>
               </div>
            </a>
         </div>

         <!-- Section 1: Unified Authentication & Zimflow OTP -->
         <div id="auth-section" class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-xl border border-gray-100 dark:border-gray-700 space-y-4">
            <div class="flex items-center space-x-3 border-b border-gray-100 dark:border-gray-700 pb-4">
               <div class="w-10 h-10 rounded-xl bg-blue-900 text-white flex items-center justify-center font-bold">1</div>
               <div>
                  <h2 class="text-lg font-bold text-gray-900 dark:text-white">Unified Auth, Auto Role Detection & Zimflow WhatsApp OTP</h2>
                  <p class="text-xs text-gray-500">Single portal login for Patients, Doctors, and Pharmacies with automatic role routing</p>
               </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
               <div class="p-4 bg-blue-50/50 dark:bg-gray-700/40 rounded-2xl border border-blue-100 dark:border-gray-700 space-y-2">
                  <h3 class="text-sm font-bold text-blue-900 dark:text-blue-300 flex items-center"><i class="fa-solid fa-user-check mr-2"></i> Auto Role Detection</h3>
                  <p class="text-xs text-gray-600 dark:text-gray-300">Users select their role (Patient, Doctor, Pharmacy) <strong>only once during Sign Up</strong>. During login, the API endpoint <code>POST /api/unified-login</code> checks across tables and automatically logs the user in with their registered role.</p>
               </div>

               <div class="p-4 bg-lime-50/50 dark:bg-gray-700/40 rounded-2xl border border-lime-100 dark:border-gray-700 space-y-2">
                  <h3 class="text-sm font-bold text-lime-800 dark:text-lime-400 flex items-center"><i class="fa-solid fa-phone mr-2"></i> Flexible Phone Formats (+263)</h3>
                  <p class="text-xs text-gray-600 dark:text-gray-300">System automatically matches phone numbers regardless of prefix format: <code>0771234567</code>, <code>771234567</code>, <code>263771234567</code>, or <code>+263771234567</code>. Mobile app includes interactive Country Code Picker (+263 default).</p>
               </div>

               <div class="p-4 bg-emerald-50/50 dark:bg-gray-700/40 rounded-2xl border border-emerald-100 dark:border-gray-700 space-y-2">
                  <h3 class="text-sm font-bold text-emerald-900 dark:text-emerald-300 flex items-center"><i class="fa-brands fa-whatsapp mr-2"></i> Zimflow WhatsApp OTP</h3>
                  <p class="text-xs text-gray-600 dark:text-gray-300">OTP requests dispatch via Zimflow WhatsApp Gateway API using template <code>chiremba_otp</code>. Configurable in Admin Settings tab <strong>WhatsApp & Delivery</strong>.</p>
               </div>
            </div>
         </div>

         <!-- Section 2: PointLink Courier & Multi-Pharmacy Delivery Logistics -->
         <div id="pointlink-section" class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-xl border border-gray-100 dark:border-gray-700 space-y-4">
            <div class="flex items-center space-x-3 border-b border-gray-100 dark:border-gray-700 pb-4">
               <div class="w-10 h-10 rounded-xl bg-lime-600 text-white flex items-center justify-center font-bold">2</div>
               <div>
                  <h2 class="text-lg font-bold text-gray-900 dark:text-white">PointLink Courier Network & Multi-Pharmacy Pickup Logistics</h2>
                  <p class="text-xs text-gray-500">How medicine orders are routed, dispatched, and delivered to patients</p>
               </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
               <div class="p-5 bg-gray-50 dark:bg-gray-700/40 rounded-2xl border border-gray-100 dark:border-gray-700 space-y-3">
                  <div class="font-bold text-sm text-gray-900 dark:text-white flex items-center">
                     <i class="fa-solid fa-map-location-dot text-lime-600 mr-2 text-lg"></i> Multi-Pharmacy Orders (Multi-Stop Pickups)
                  </div>
                  <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                     When a patient orders medicines from <strong>multiple pharmacies</strong> (e.g. Pharmacy A in Avondale and Pharmacy B in Eastlea):
                  </p>
                  <ul class="text-xs space-y-2 text-gray-600 dark:text-gray-300 list-disc pl-5">
                     <li>Items are grouped by <code>pharmacy_id</code>, creating individual sub-orders for inventory & prescription processing.</li>
                     <li>The PointLink delivery booking passes a <strong><code>stops</code> JSON array</strong> containing intermediate pharmacy locations.</li>
                     <li>PointLink computes a single consolidated courier ride fare for the full route: <code>Pharmacy A &rarr; Pharmacy B &rarr; Patient Address</code>.</li>
                  </ul>
               </div>

               <div class="p-5 bg-gray-50 dark:bg-gray-700/40 rounded-2xl border border-gray-100 dark:border-gray-700 space-y-3">
                  <div class="font-bold text-sm text-gray-900 dark:text-white flex items-center">
                     <i class="fa-solid fa-address-book text-blue-900 mr-2 text-lg"></i> Recipient Mobile Contacts & Payment Dispatch
                  </div>
                  <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                     PointLink delivery requests automatically include:
                  </p>
                  <ul class="text-xs space-y-2 text-gray-600 dark:text-gray-300 list-disc pl-5">
                     <li><strong><code>contact_name</code> & <code>contact_phone</code></strong>: Recipient's mobile details so the courier driver can call/SMS upon arrival.</li>
                     <li><strong><code>payment_method</code></strong>: Explicitly marked as <code>cash</code> (COD) or <code>wallet</code> (Digital Payment).</li>
                     <li><strong><code>item_price</code></strong>: Total value of medicine cargo attached to courier payload.</li>
                  </ul>
               </div>
            </div>
         </div>

         <!-- Section 3: Pharmacy Fleet & Delivery Capabilities -->
         <div id="pharmacy-section" class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-xl border border-gray-100 dark:border-gray-700 space-y-4">
            <div class="flex items-center space-x-3 border-b border-gray-100 dark:border-gray-700 pb-4">
               <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold">3</div>
               <div>
                  <h2 class="text-lg font-bold text-gray-900 dark:text-white">Pharmacy Fleet, Delivery Bikes & Payment Toggles</h2>
                  <p class="text-xs text-gray-500">Configurable operational settings for partner pharmacies</p>
               </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
               <div class="p-4 bg-gray-50 dark:bg-gray-700/40 rounded-2xl border border-gray-100 dark:border-gray-700 text-center space-y-2">
                  <div class="w-12 h-12 mx-auto rounded-2xl bg-lime-100 dark:bg-lime-900/40 text-lime-600 flex items-center justify-center text-xl"><i class="fa-solid fa-motorcycle"></i></div>
                  <div class="font-bold text-xs text-gray-900 dark:text-white">Has Own Bikes</div>
                  <p class="text-[11px] text-gray-500">Pharmacy utilizes its own motorbikes for local self-deliveries.</p>
               </div>

               <div class="p-4 bg-gray-50 dark:bg-gray-700/40 rounded-2xl border border-gray-100 dark:border-gray-700 text-center space-y-2">
                  <div class="w-12 h-12 mx-auto rounded-2xl bg-blue-100 dark:bg-blue-900/40 text-blue-900 flex items-center justify-center text-xl"><i class="fa-solid fa-network-wired"></i></div>
                  <div class="font-bold text-xs text-gray-900 dark:text-white">PointLink Couriers</div>
                  <p class="text-[11px] text-gray-500">Optionally allow PointLink network drivers to handle pickups.</p>
               </div>

               <div class="p-4 bg-gray-50 dark:bg-gray-700/40 rounded-2xl border border-gray-100 dark:border-gray-700 text-center space-y-2">
                  <div class="w-12 h-12 mx-auto rounded-2xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 flex items-center justify-center text-xl"><i class="fa-solid fa-money-bill-wave"></i></div>
                  <div class="font-bold text-xs text-gray-900 dark:text-white">Accept COD</div>
                  <p class="text-[11px] text-gray-500">Toggle Cash on Delivery for medicine orders.</p>
               </div>

               <div class="p-4 bg-gray-50 dark:bg-gray-700/40 rounded-2xl border border-gray-100 dark:border-gray-700 text-center space-y-2">
                  <div class="w-12 h-12 mx-auto rounded-2xl bg-purple-100 dark:bg-purple-900/40 text-purple-600 flex items-center justify-center text-xl"><i class="fa-solid fa-credit-card"></i></div>
                  <div class="font-bold text-xs text-gray-900 dark:text-white">Digital Payments</div>
                  <p class="text-[11px] text-gray-500">Accept Paynow, EcoCash, Visa & MasterCard online.</p>
               </div>
            </div>
         </div>

         <!-- Section 4: COD Commission Debt Ledger & Financial Model -->
         <div id="finance-section" class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-xl border border-gray-100 dark:border-gray-700 space-y-4">
            <div class="flex items-center space-x-3 border-b border-gray-100 dark:border-gray-700 pb-4">
               <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center font-bold">4</div>
               <div>
                  <h2 class="text-lg font-bold text-gray-900 dark:text-white">COD Commission Debt Ledger & Platform Revenue Safeguards</h2>
                  <p class="text-xs text-gray-500">Ensuring zero platform commission loss on cash transactions</p>
               </div>
            </div>

            <div class="p-5 bg-purple-50/50 dark:bg-gray-700/40 rounded-2xl border border-purple-100 dark:border-gray-700 space-y-3">
               <h3 class="text-sm font-bold text-purple-900 dark:text-purple-300 flex items-center"><i class="fa-solid fa-vault mr-2"></i> Commission Debt Mechanics</h3>
               <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                  When a patient pays <strong>Cash on Delivery (COD)</strong>, the pharmacy collects 100% of the cash directly. To ensure Chiremba Express retains its platform commission (e.g. 10%), the system automatically creates a <strong>Commission Debt transaction</strong>:
               </p>
               <div class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-purple-200 dark:border-gray-600 font-mono text-xs text-purple-950 dark:text-purple-200">
                  Platform Commission Debt: -$1.50 (10% of $15.00 COD Order #104) &rarr; Wallet Balance: -$1.50
               </div>
               <p class="text-xs text-gray-600 dark:text-gray-300">
                  When the pharmacy receives future digital payments (Paynow/EcoCash), system automatically clears accumulated commission debt before enabling withdrawal payouts.
               </p>
            </div>
         </div>

         <!-- Section 5: Troubleshooting & Integration Checklist -->
         <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-xl border border-gray-100 dark:border-gray-700 space-y-4">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center"><i class="fa-solid fa-wrench text-lime-600 mr-2"></i> Google Sign-In Fix (API Exception 10)</h2>
            <div class="p-4 bg-gray-50 dark:bg-gray-700/40 rounded-2xl border border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-300 space-y-2">
               <p>If mobile users encounter <strong>Google Sign-In Error (API Exception 10)</strong>:</p>
               <ol class="list-decimal pl-5 space-y-1">
                  <li>Generate your Android app SHA-1 fingerprint via <code>keytool -list -v -keystore ~/.android/debug.keystore</code>.</li>
                  <li>Go to <strong>Firebase Console &rarr; Project Settings &rarr; Your Android App</strong> (<code>com.chirembaexpress.app</code>).</li>
                  <li>Click <strong>Add Fingerprint</strong>, paste SHA-1, and save.</li>
                  <li>Download updated <code>google-services.json</code> and place in <code>android/app/</code>.</li>
               </ol>
            </div>
         </div>

      </div>
   </div>
</div>
@stop
