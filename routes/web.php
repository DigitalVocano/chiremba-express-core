<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Front\HomeController;
// Removed legacy non-existent OrderController & CommentController imports
use App\Http\Controllers\Doctor\DashboardController as DoctorDashboardController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Patient\DashboardController as PatientDashboardController;
use App\Http\Controllers\Patient\ChatController as PatientChatController;
use App\Http\Controllers\Patient\AiController as PatientAiController;
use App\Http\Controllers\Patient\HealthRecordController as PatientHealthRecordController;
use App\Http\Controllers\Patient\WalletController as PatientWalletController;
use App\Http\Controllers\Doctor\ChatController as DoctorChatController;
use App\Http\Controllers\Doctor\WalletController as DoctorWalletController;
use App\Http\Controllers\Doctor\ScheduleController as DoctorScheduleController;
use App\Http\Controllers\Doctor\AppointmentController as DoctorAppointmentController;
use App\Http\Controllers\Shared\CalendarController as FullCalendarEventMasterController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Shared\PaymentController as MakePaymentController;
use App\Http\Controllers\Admin\PaymentSettingController as AdminPaymentSettingController;
use App\Http\Controllers\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Admin\LanguageController as AdminLanguageController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\MedicineController as MedicinesController;
use App\Http\Controllers\Shared\PaynowController;
use App\Http\Controllers\Shared\EcocashController;
use App\Http\Controllers\Shared\AgoraController;
use App\Http\Controllers\Admin\MedicalAidController as AdminMedicalAidController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\FeaturedController as AdminFeaturedController;
use App\Http\Controllers\Admin\PharmacyController as AdminPharmacyController;
use App\Http\Controllers\Pharmacy\AuthController as PharmacyAuthController;
use App\Http\Controllers\Pharmacy\PharmacyPanelController;
use App\Http\Controllers\Pharmacy\WalletController as PharmacyWalletController;
use App\Http\Controllers\Patient\PharmacyController as PatientPharmacyController;
use App\Http\Controllers\Front\PageController as PharmacyPublicController;
use App\Http\Controllers\Admin\SubscriberController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\AiCreditController as AdminAiCreditController;
use Illuminate\Support\Facades\Artisan;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('cache-clear', function () {
    Artisan::call("optimize:clear");
    echo "done";
});

Route::group(['middleware' => ['DomainCheck']], function () {

Route::group(['middleware' => ['Localization']], function () {
        // Centralized Wallet Payment Initiation (Paynow, Braintree)
        Route::Post("make-payment",[PaynowController::class,"make_payment"])->name('make-payment');
        Route::any("wallet/topup/initiate",[MakePaymentController::class,"initiatePaymentForWallet"])->name("wallet.topup.initiate");
        Route::any("paynow/result", [MakePaymentController::class, "handlePaynowCallback"])->name("paynow.result"); // For Paynow server-to-server status updates
        Route::any("wallet/topup/callback",[MakePaymentController::class,"handlePaynowCallback"])->name("wallet.topup.callback");
Route::get("payment-success",[MakePaymentController::class,"payment_success"])->name("payment-success");
Route::get("payment-failed",[MakePaymentController::class,"payment_failed"])->name("payment-failed");
Route::post("payment/ecocash/notify", [EcocashController::class, "notify"])->name("payment.ecocash.notify");
Route::get("payment/ecocash/status", [EcocashController::class, "checkStatus"])->name("payment.ecocash.status");
Route::post("agora/token", [\App\Http\Controllers\Shared\LiveKitController::class, "token"])->name("agora.token");
Route::post("livekit/token", [\App\Http\Controllers\Shared\LiveKitController::class, "token"])->name("livekit.token");

Route::group(['prefix' => '/'], function () {
        // --- Public Routes (Accessible without specific user role login) ---
    Route::post("postforgotpassword",[HomeController::class,"postforgotpassword"]);
    Route::get("send-reset-link", [HomeController::class, "sendSpecificResetEmail"])->name('send.reset.link');
	Route::get("/",[HomeController::class,"showhome"]);
	Route::get("addnewsletter",[HomeController::class,"addnewsletter"]);
	Route::get("privacy_policy",[HomeController::class,"privacy_policy"]);
	Route::get("insurance",[HomeController::class,"insurance"]);
	Route::get("medical-aid",[HomeController::class,"medicalAids"])->name('medical.aid');
	Route::get("viewspecialist",[HomeController::class,"viewspecialist"]);
	Route::get('viewdoctor/{slug}', [HomeController::class, 'viewdoctor'])->name('viewdoctor');
	Route::get('patient_history/{id}', [PatientHealthRecordController::class, 'doctorViewPatientHistory'])->name('public.patient_history');
	Route::get("searchdoctor",[HomeController::class,"searchdoctor"]);
	Route::get("aboutus",[HomeController::class,"aboutus"]);
	Route::get("contactus",[HomeController::class,"contactus"]);
	Route::get("patientlogin",[HomeController::class,"patientlogin"]);
	Route::get("patientregister/{code?}",[HomeController::class,"patientregister"])->name('patientregister');
	Route::get('/auth/google/pharmacy', [PharmacyAuthController::class, 'redirectToGoogle'])->name('auth.google.pharmacy');
    Route::get('/auth/google/pharmacy/callback', [PatientDashboardController::class, 'handleGoogleCallback']); // Pointing to User as per analysis
	Route::get('/auth/google/doctor', [DoctorDashboardController::class, 'redirectToGoogle'])->name('auth.google.doctor');
    Route::get('/auth/google/doctor/callback', [DoctorDashboardController::class, 'handleGoogleCallback']);
	Route::get("forgotpassword",[HomeController::class,"forgotpassword"]);
	Route::get("profilelogin",[HomeController::class,"doctorlogin"]);

        Route::get('languages/{locale}', [AdminLanguageController::class, 'index'])->name('languages.index');
        Route::get('languages/{lang}/edit/{key}', [AdminLanguageController::class, 'edit'])->name('languages.edit');
        Route::put('languages/{lang}/update/{key}', [AdminLanguageController::class, 'update'])->name('languages.update');
	Route::get('auth/google', [PatientDashboardController::class, 'redirectToGoogle'])->name('auth/google');
	Route::get('auth/google/callback', [PatientDashboardController::class, 'handleGoogleCallback']);
	// Route::get("doctor.login",[HomeController::class,"doctor.login"]);
	Route::get("doctorregister/{code?}",[HomeController::class,"doctorregister"])->name('doctorregister');
	Route::get("getslotlist",[HomeController::class,"getslotlist"]);
	Route::get("getschedule",[HomeController::class,"getschedule"]);
	Route::get("corporates",[HomeController::class,"showCorporatesPage"]);
	Route::post("send-corporate-quote",[HomeController::class,"sendCorporateQuote"]);
	Route::post("savecontact",[HomeController::class,"savecontact"]);
    Route::post("guest/ask-ai", [PatientAiController::class, "guestAiChat"])->name('guest.ask_ai');
    Route::get("ai-health-assistant", [PatientAiController::class, "showPublicAiAssistant"])->name('public.ai_assistant');
});

Route::get('local/{locale}', [AdminLanguageController::class, 'switch'])->name('locale.switch');
Route::get("patientlogin",[HomeController::class,"patientlogin"])->name('login');
Route::get("profilelogin",[HomeController::class,"doctorlogin"])->name('doctor.login'); // Used for both doctor and pharmacy login
Route::get("privacy-user",[HomeController::class,"privacy_admin"]);
Route::get("terms",[HomeController::class,"terms_and_conditions"])->name('terms');
Route::get("privacy_policy",[HomeController::class,"privacy_policy"]);
Route::get("privacy-policy",[HomeController::class,"privacy_policy"])->name('privacy.policy');
Route::get("Privacy_Policy",[HomeController::class,"privacy_front_app"]);
Route::get("accountdeletion",[HomeController::class,"accountdeletion"]);
Route::post("savereview",[HomeController::class,"savereview"]);

    Route::get("searchpharmacy",[PatientPharmacyController::class,"searchpharmacy"]);
    Route::get('viewpharmacy/{slug}', [PatientPharmacyController::class, 'viewpharmacy'])->name('viewpharmacy');

        // --- Patient Specific Routes ---
        Route::group([], function () {
            // Routes that DO NOT require a subscription
            Route::post("userpostregister",[PatientDashboardController::class,"userpostregister"]);
            Route::post("postloginuser",[PatientDashboardController::class,"postloginuser"]);
            Route::get("user/logout",[PatientDashboardController::class,"logout"])->name('user.logout');

            // Subscription and Wallet routes should always be accessible
            Route::get("patient_subscription_plan",[PatientDashboardController::class,"patient_subscription_plan"])->name('patient.subscription_plan');
            Route::post("patient/subscribe-with-wallet", [PatientDashboardController::class, "subscribeWithWallet"])->name('patient.subscribe.wallet');
            Route::get('/money-wallet', [PatientWalletController::class, 'showMedicalWallet'])->name('medical.wallet');
            Route::get("wallet/medical-aid-topup",[PatientWalletController::class,"showMedicalAidTopupForm"])->name('user.medical.aid.topup.form');
            Route::get('user/buy-credits', [PatientWalletController::class, 'showBuyCreditsPage'])->name('patient.buy_credits');
            Route::post('user/buy-credits', [PatientWalletController::class, 'purchaseCreditsWithWallet'])->name('patient.purchase_credits.wallet');
            Route::post("wallet/medical-aid-topup/submit",[PatientWalletController::class,"submitMedicalAidTopupRequest"])->name('user.medical.aid.topup.submit');

            // Password change and profile edit should always be accessible
            Route::get("changepassword",[PatientDashboardController::class,"changepassword"]);
            Route::get("checkuserpwd",[PatientDashboardController::class,"checkuserpwd"]);
            Route::post("updateuserpassword",[PatientDashboardController::class,"updateuserpassword"]);
            Route::get("usereditprofile",[PatientDashboardController::class,"usereditprofile"]);
            Route::post("updateuserprofile",[PatientDashboardController::class,"updateuserprofile"]);
            Route::post("create_payment",[PatientDashboardController::class,"create_payment"]); // This might be redundant if all payments go through wallet top-up
            Route::get("user/my-subscription", [PatientDashboardController::class, "mySubscription"])->name('patient.my_subscription');

            // Routes that REQUIRE an active subscription (if enabled)
            Route::group(['middleware' => ['patient.subscription']], function () {
                Route::get("userdashboard/{type?}",[PatientDashboardController::class,"userdashboard"])->name('userdashboard');
                Route::get('/medical-records-wallet', [PatientHealthRecordController::class, 'showMedicalRecordsWallet'])->name('medical.records.wallet');
                Route::put('update-medical-record-visibility/{record}', [PatientHealthRecordController::class, 'updateMedicalRecordVisibility'])->name('update.medical.record.visibility');
                Route::get('get-appointment-medicines/{id}', [PatientHealthRecordController::class, 'getAppointmentMedicines'])->name('get-appointment-medicines');
                Route::get('/symptom-checker', [PatientAiController::class, 'showSymptomChecker'])->name('show.symptom.checker');
                Route::post('/check-symptoms', [PatientAiController::class, 'checkSymptoms'])->name('check.symptoms');
                Route::get('/health-tracking', [PatientHealthRecordController::class, 'showHealthTracking'])->name('health.tracking');
                Route::post('/add-health-record', [PatientHealthRecordController::class, 'addHealthRecord'])->name('add.health.record');
                Route::post('/upload-medical-record', [PatientHealthRecordController::class, 'uploadMedicalRecord'])->name('upload.medical.record');
                Route::get('/view-medical-record/{record}', [PatientHealthRecordController::class, 'viewMedicalRecord'])->name('view.medical.record');
                Route::delete('/delete-medical-record/{record}', [PatientHealthRecordController::class, 'deleteMedicalRecord'])->name('delete.medical.record');
                Route::post("makeappointment",[PatientDashboardController::class,"makeappointment"]);
                Route::get("userfavorite/{doc_id}",[PatientDashboardController::class,"userfavorite"]);
                Route::get("favouriteuser",[PatientDashboardController::class,"favouriteuser"]);
                Route::get("viewschedule",[PatientDashboardController::class,"viewschedule"]);
                Route::get("viewappointment/{id}",[PatientDashboardController::class,"viewappointment"]);
                Route::get('/fullcalendareventmaster',[FullCalendarEventMasterController::class,'index']);
                Route::get("userreview",[PatientDashboardController::class,"userreview"]);
                Route::get("get-user-appointment/{id}",[PatientDashboardController::class,"get_user_appointment"]);
                Route::get('userchat/{id}', [PatientChatController::class, 'userchat']);
                Route::get('get_user_chat_data/{id}', [PatientChatController::class, 'get_user_chat_data']);
                Route::post('send_msg_user', [PatientChatController::class, 'send_msg_user']);
                Route::post('send_file_user', [PatientChatController::class, 'send_file_user']);
                Route::get('user/ai-assistant', [PatientAiController::class, 'showAiAssistant'])->name('patient.ai_assistant');
                Route::post('user/ask-ai', [PatientAiController::class, 'askAiAssistant'])->name('patient.ask_ai');
                Route::get('/export-health-records-pdf', [PatientHealthRecordController::class, 'exportHealthRecordsToPDF'])->name('export.health.records.pdf');
                Route::get("addToCart/{id}", [PatientPharmacyController::class, 'addToCart']);
                  Route::get('user/ai-assistant/download', [PatientAiController::class, 'downloadAiAssistantReport'])->name('patient.ai_assistant.download');
                Route::get("removeCartItem/{id}", [PatientPharmacyController::class, 'removeCartItem']);
                Route::get("cart", [PatientPharmacyController::class, "viewCart"])->name('cart.view');
                Route::post("cart/checkout", [PatientPharmacyController::class, "processCartCheckout"])->name('cart.checkout');
                Route::get("usermedicineorder", [PatientPharmacyController::class, 'usermedicineorder']);
                Route::get("get_orderdata/{id}", [PatientPharmacyController::class, 'get_orderdata']);
                Route::get("user/referrals", [PatientDashboardController::class, "referralDashboard"])->name('user.referrals');
                Route::post("user/referrals/redeem", [PatientDashboardController::class, "redeemReward"])->name('user.referrals.redeem');
            });
        });

        // --- Doctor Specific Routes ---
        Route::group([], function () {
            // Routes that DO NOT require a subscription
            Route::post("postdoctorregister", [DoctorDashboardController::class, "postdoctorregister"]);
            Route::post("postlogindoctor", [DoctorDashboardController::class, "postlogindoctor"]);
            Route::get("doctor/logout", [DoctorDashboardController::class, "logout"])->name('doctor.logout');

            // Subscription and Wallet routes should always be accessible
            Route::get("doctor_subscription_plan", [DoctorDashboardController::class, "doctor_subscription_plan"])->name('doctor.subscription_plan');
            Route::post("doctor/subscribe-with-wallet", [DoctorWalletController::class, "subscribeWithWallet"])->name('doctor.subscribe.wallet');
            Route::get("doctor-wallet", [DoctorWalletController::class, "showDoctorWallet"])->name('doctor.wallet');
            Route::get("doctor/my-subscription", [DoctorWalletController::class, "mySubscription"])->name('doctor.my_subscription');
            Route::post("doctor-withdrawal-request", [DoctorWalletController::class, "submitWithdrawalRequest"])->name('doctor.withdrawal.request');

            // Password change and profile edit should always be accessible
            Route::get("doctorchangepassword", [DoctorDashboardController::class, "doctorchangepassword"]);
            Route::get("checkdoctorpwd", [DoctorDashboardController::class, "checkdoctorpwd"]);
            Route::post("updatedoctorpassword", [DoctorDashboardController::class, "updatedoctorpassword"]);
            Route::get("doctoreditprofile", [DoctorDashboardController::class, "doctoreditprofile"]);
            Route::post("updatedoctor", [DoctorDashboardController::class, "updatedoctorsideprofile"]);

            // Routes that REQUIRE an active subscription (if enabled)
            // Note: The 'doctor.buy_credits' route is placed outside the subscription group to allow access for all logged-in doctors.
            Route::get('doctor/buy-credits', [DoctorWalletController::class, 'showBuyCreditsPage'])->name('doctor.buy_credits');
            Route::post('doctor/purchase-credits', [DoctorWalletController::class, 'purchaseCreditsWithWallet'])->name('doctor.purchase_credits.wallet');
            Route::group(['middleware' => ['doctor.subscription']], function () { 
                Route::get("doctordashboard", [DoctorDashboardController::class, "doctordashboard"])->name('doctordashboard');
                Route::get("doctorreview", [DoctorDashboardController::class, "doctorreview"]);
                Route::get("doctorappointment", [DoctorAppointmentController::class, "doctorappointment"])->name('doctorappointment');
                Route::get("doctortiming", [DoctorScheduleController::class, "doctortimingfront"]);
                Route::get("findpossibletime", [DoctorScheduleController::class, "findpossibletime"]);
                Route::get("generateslot", [DoctorScheduleController::class, "generateslotfront"]);
                Route::post("updatedoctortiming", [DoctorScheduleController::class, "updatedoctortiming"]);
                Route::get("changeappointment/{status}/{id}", [DoctorAppointmentController::class, "changeappointmentdoctor"]);
                Route::get("paymenthistory", [DoctorWalletController::class, "paymenthistory"]);
                Route::get("deletedoctorhoilday/{id}", [DoctorScheduleController::class, "deletedoctorhoilday"]);
                Route::post("complete-doctor-appointment", [DoctorAppointmentController::class, "show_complete_doctor_appointment"])->name("complete-doctor-appointment");
                Route::get("doctor_hoilday", [DoctorScheduleController::class, "show_doctor_hoilday"]);
                Route::post("post_my_hoilday", [DoctorScheduleController::class, "show_post_my_hoilday"])->name("post-my-hoilday");
                Route::get('doctorchat/{id}', [DoctorChatController::class, 'doctorchat']);
                Route::get('get_doctor_chat_data/{id}', [DoctorChatController::class, 'get_doctor_chat_data']);
                Route::post('send_msg_doctor', [DoctorChatController::class, 'send_msg_doctor']);
                Route::post('send_file_doctor', [DoctorChatController::class, 'send_file_doctor']);
                Route::get("appointment_detail/{id}", [DoctorAppointmentController::class, "appointment_detail"]);
                Route::post("save_prescription", [DoctorAppointmentController::class, "save_prescription"]);
                Route::get('/getmedicines', [DoctorAppointmentController::class, 'getmedicines']);
                Route::get('backtoappointment', [DoctorAppointmentController::class, 'backtoappointment']);
                Route::get('delete_prescription/{id}', [DoctorAppointmentController::class, 'delete_prescription']);
                Route::get('delete_report/{id}', [DoctorAppointmentController::class, 'delete_report']);
                Route::post('edit_prescription', [DoctorAppointmentController::class, 'edit_prescription']);
                Route::get("get-user-appointment1/{id}", [DoctorAppointmentController::class, "get_user_appointment1"]);
                Route::get("doctor/referrals", [DoctorWalletController::class, "referralDashboard"])->name('doctor.referrals');
                Route::post("doctor/referrals/redeem", [DoctorWalletController::class, "redeemReward"])->name('doctor.referrals.redeem');
                // New: AI Assistant route for doctors
                Route::post('/doctor/ai/get-prescription-suggestion', [\App\Http\Controllers\Shared\AiController::class, 'getPrescriptionSuggestion'])->name('doctor.ai.getSuggestion');
                // New: AI Report Analysis route
                Route::post('/doctor/ai/analyze-report', [\App\Http\Controllers\Shared\AiController::class, 'analyzeReport'])->name('doctor.ai.analyzeReport');
                // New: Routes for interactive AI report discussion
                Route::get('/doctor/ai/get-report-details/{id}', [\App\Http\Controllers\Shared\AiController::class, 'getReportDetails'])->name('doctor.ai.getReportDetails');
                // New: Route for refining report analysis
                Route::post('/doctor/ai/refine-report-analysis', [\App\Http\Controllers\Shared\AiController::class, 'refineReportAnalysis'])->name('doctor.ai.refineReport');
                // New: Route for finalizing report
                Route::post('/doctor/ai/finalize-report', [\App\Http\Controllers\Shared\AiController::class, 'finalizeReport'])->name('doctor.ai.finalizeReport');
            });
        }); // End Doctor Specific Routes

        // --- Pharmacy Specific Routes (Requires user_id and role_id = 3) ---
        Route::group([], function () {
            // Routes that DO NOT require a subscription
            Route::post('pharmacylogin', [PharmacyAuthController::class, 'pharmacylogin']);
            Route::post("postpharmacyregister",[PharmacyAuthController::class,"postpharmacyregister"]);
            Route::get("pharmacy/logout",[PharmacyAuthController::class,"logout"])->name('pharmacy.logout');

            // Subscription and Wallet routes
            Route::get("pharmacy_subscription_plan", [PharmacyWalletController::class, "pharmacy_subscription_plan"])->name('pharmacy.subscription_plan');
            Route::post("pharmacy/subscribe-with-wallet", [PharmacyWalletController::class, "subscribeWithWallet"])->name('pharmacy.subscribe.wallet');
            Route::get("pharmacy/my-subscription", [PharmacyWalletController::class, "mySubscription"])->name('pharmacy.my_subscription');

            // Routes that REQUIRE an active subscription (if enabled)
            Route::get('pharmacydashboard', [PharmacyPanelController::class, 'pharmacydashboard'])->name('pharmacydashboard');
            Route::group(['middleware' => ['pharmacy.subscription']], function () {
                Route::get("pharmacyeditprofile",[PharmacyPanelController::class,"pharmacyeditprofile"]);
                Route::post("updatepharmacy",[PharmacyPanelController::class,"updatepharmacysideprofile"]);
                Route::get("pharmacyreview",[PharmacyPanelController::class,"pharmacyreview"]);
                Route::get("pharmacychangepassword",[PharmacyPanelController::class,"pharmacychangepassword"]);
                Route::get("pharmacyorderchangestatus/{id}/{status}",[PharmacyPanelController::class,"pharmacyorderchangestatus"]);
                Route::post("addprice",[PharmacyPanelController::class,"addprice"]);
                Route::get("pharmacy/order/{id}/invoice", [PharmacyPanelController::class, "downloadInvoice"])->name('pharmacy.order.invoice');
                Route::get("pharmacy/pos", [PharmacyPanelController::class, "showPos"])->name('pharmacy.pos');
                Route::get("pharmacymedicine",[PharmacyPanelController::class,"pharmacymedicine"]);
                Route::get("pharmacy-wallet",[PharmacyWalletController::class,"showPharmacyWallet"])->name('pharmacy.wallet');
                Route::get("medicinedeletefront/{pharmacy_id}",[PharmacyPanelController::class,"medicinedeletefront"])->name('medicinedeletefront');
                Route::post("updatemedicinefront",[PharmacyPanelController::class,"updatemedicinefront"]);
                Route::get("getdatapharmmacy/{id}",[PharmacyPanelController::class,"getdatapharmmacy"]);
                Route::get("deleteimgpharmmacy/{id}",[PharmacyPanelController::class,"deleteimgpharmmacy"]);
                Route::post("pharmacy-withdrawal-request",[PharmacyWalletController::class,"submitWithdrawalRequest"])->name('pharmacy.withdrawal.request');
                Route::get("pharmacy/referrals", [PharmacyWalletController::class, "referralDashboard"])->name('pharmacy.referrals');
                Route::post("pharmacy/referrals/redeem", [PharmacyWalletController::class, "redeemReward"])->name('pharmacy.referrals.redeem');
                Route::get("pharmacy-profit-loss",[PharmacyPanelController::class,"showProfitLoss"])->name('pharmacy.profit-loss');
            });
        });

        // --- Admin Routes (Requires AdminCheck middleware) ---
        Route::prefix('admin')->group(function () {
            Route::get("/",[AdminAuthController::class,"showlogin"])->name('admin.login');
            Route::post("postlogin",[AdminAuthController::class,"postlogin"]);
            Route::get("verify-2fa", [AdminAuthController::class, "show2faVerify"])->name('admin.2fa.verify');
            Route::post("post-verify-2fa", [AdminAuthController::class, "post2faVerify"])->name('admin.2fa.post_verify');


            Route::group(['middleware' => ['AdminCheck']], function () {

            Route::get('medicines', [MedicinesController::class, 'showmedicines'])->name('admin.medicines.index');
            Route::get('medicines/create', [MedicinesController::class, 'medicinesadd1'])->name('admin.medicines.create');
            Route::post('medicines/store', [MedicinesController::class, 'addmedicinessave'])->name('admin.medicines.store');
            Route::get('medicines/{id}/edit', [MedicinesController::class, 'editmedicines'])->name('admin.medicines.edit');
            Route::patch('medicines/update', [MedicinesController::class, 'editmedicinessave'])->name('admin.medicines.update');
            Route::get('medicines/{id}/delete', [MedicinesController::class, 'deletemedicines'])->name('admin.medicines.destroy');
            Route::get("medicines/table", [MedicinesController::class, "medicine"])->name('admin.medicines.table');

    	    Route::get("dashboard",[AdminAuthController::class,'showdashboard'])->name('dashboard');
    	    Route::get("system-guide",[AdminAuthController::class,'showSystemGuide'])->name('admin.system_guide');
		    Route::get("2fa-setup", [AdminAuthController::class, "show2faSetup"])->name('admin.2fa.setup');
		    Route::post("2fa-enable", [AdminAuthController::class, "enable2fa"])->name('admin.2fa.enable');
		    Route::post("2fa-disable", [AdminAuthController::class, "disable2fa"])->name('admin.2fa.disable');
		    Route::get("logout",[AdminAuthController::class,'logout']);
		    Route::get("reset_password/{code}",[AdminAuthController::class,"reset_password"]);
		    Route::get("services",[ServiceController::class,'showservice']);
		    Route::any("reset_new_pwd",[AdminAuthController::class,"reset_new_pwd"]);
		    
		    Route::get("services",[ServiceController::class,'showservice'])->name('admin.services.index');
		    Route::get("saveservices/{id}",[ServiceController::class,"saveservices"])->name('admin.services.save');
		    Route::post("updateservice",[ServiceController::class,"updateservice"])->name('admin.services.update');
		    Route::get("servicestable",[ServiceController::class,"servicestable"])->name('admin.services.table');
		    Route::get("deleteservices/{id}",[ServiceController::class,"deleteservices"])->name('admin.services.delete');
            Route::post('services/{service}/toggle-recommendable', [ServiceController::class, 'toggleRecommendable'])->name('admin.services.toggle_recommendable');

		    Route::get("doctors",[DoctorDashboardController::class,"showdoctors"])->name('admin.doctors.index');
		    Route::get("doctorstable",[DoctorDashboardController::class,"doctorstable"])->name('admin.doctors.table');
		    Route::get("savedoctor/{id}",[DoctorDashboardController::class,"savedoctor"])->name('admin.doctors.save');
		    Route::post("updatedoctor",[DoctorDashboardController::class,"updatedoctor"])->name('admin.doctors.update');
		    Route::get("doctortiming/{id}",[DoctorDashboardController::class,"doctortiming"])->name('admin.doctors.timing');
		    Route::get("findpossibletime",[DoctorDashboardController::class,"findpossibletime"])->name('admin.doctors.findtime');
		    Route::get("generateslot",[DoctorDashboardController::class,"generateslot"])->name('admin.doctors.generateslot');
		    Route::post("savescheduledata",[DoctorDashboardController::class,"savescheduledata"])->name('admin.doctors.saveschedule');
		    Route::get("deletedoctor/{id}",[DoctorDashboardController::class,"deletedoctor"])->name('admin.doctors.delete');
		    Route::get("approvedoctor/{id}/{status}",[DoctorDashboardController::class,"postapprovedoctor"])->name('admin.doctors.approve');
            Route::post('doctors/{doctor}/toggle-recommendable', [DoctorDashboardController::class, 'toggleRecommendable'])->name('admin.doctors.toggle_recommendable');

            // Admin AI Profile Assistant Routes
            Route::post('ai/generate-doctor-bio', [\App\Http\Controllers\Admin\AiProfileAssistantController::class, 'generateDoctorBio'])->name('admin.ai.doctor_bio');
            Route::post('ai/generate-pharmacy-bio', [\App\Http\Controllers\Admin\AiProfileAssistantController::class, 'generatePharmacyBio'])->name('admin.ai.pharmacy_bio');
            Route::post('ai/auto-enhance-doctor/{id}', [\App\Http\Controllers\Admin\AiProfileAssistantController::class, 'autoEnhanceDoctor'])->name('admin.ai.enhance_doctor');
            Route::post('ai/auto-enhance-pharmacy/{id}', [\App\Http\Controllers\Admin\AiProfileAssistantController::class, 'autoEnhancePharmacy'])->name('admin.ai.enhance_pharmacy');
            Route::get('ai/audit-profiles', [\App\Http\Controllers\Admin\AiProfileAssistantController::class, 'auditProfiles'])->name('admin.ai.audit_profiles');
            Route::post('ai/bulk-enhance', [\App\Http\Controllers\Admin\AiProfileAssistantController::class, 'bulkEnhance'])->name('admin.ai.bulk_enhance');

		    Route::get("reviews",[DoctorDashboardController::class,'showreviews'])->name('admin.reviews.index');
		    Route::get("reviewtable",[DoctorDashboardController::class,"reviewtable"])->name('admin.reviews.table');
		    Route::get("deletereview/{id}",[DoctorDashboardController::class,"deletereview"])->name('admin.reviews.delete');

		    Route::get("patients",[AdminAuthController::class,"showsuser"])->name('admin.patients.index');
		    Route::get("userstable",[AdminAuthController::class,"userstable"])->name('admin.patients.table');
		    Route::get("deleteuser/{id}",[AdminAuthController::class,"deleteuser"])->name('admin.patients.delete');

		    Route::get("editprofile",[AdminAuthController::class,"editprofile"]);
		    Route::post("updateprofile",[AdminAuthController::class,"updateprofile"]);

		    Route::get("changepassword",[AdminAuthController::class,"changepassword"]);
		    Route::post("updatepassword",[AdminAuthController::class,"updatepassword"]);
		    Route::get("check_password_same/{val}",[AdminAuthController::class,"checkcurrentpassword"]);
            Route::post("updateaccount",[AdminAuthController::class,"updateaccount"]);

            Route::get("appointment",[AppointmentController::class,"showappointment"])->name('admin.appointment.index');
            Route::get("appointmenttable",[AppointmentController::class,"appointmenttable"])->name('admin.appointment.table');
            Route::get("changeappstatus/{id}/{status_id}",[AppointmentController::class,"changeappstatus"])->name('admin.appointment.changestatus');

            Route::get("sendnotification",[NotificationController::class,"showsendnotification"]);
            Route::get("notificationkey",[NotificationController::class,"notificationkey"]);
            Route::post("updatenotificationkey",[NotificationController::class,"updatenotificationkey"]);
            Route::get("notificationtable",[NotificationController::class,"notificationtable"]);
 
            Route::get("savenotification",[NotificationController::class,"savenotification"]);
            Route::post("sendnotificationtouser",[NotificationController::class,"sendnotificationtouser"]);
            Route::get("notification/{id}",[AppointmentController::class,"notification"]);
            Route::get("latsrappointmenttable",[AppointmentController::class,"latsrappointmenttable"])->name('admin.appointment.latest');
		    Route::post("updatesettingfour",[AdminAuthController::class,"updatesettingfour"]);
		    Route::get("complain",[AdminAuthController::class,"showcomplain"]);
		    Route::get("compaintable",[AdminAuthController::class,"compaintable"]);

		    // New Unified Settings Routes
            Route::get('setting', [AdminSettingController::class, 'showNewSettings'])->name('admin.settings.show');
            Route::post('updatesetting', [AdminSettingController::class, 'updatesetting'])->name('admin.settings.update');
		    Route::post("store_keys",[AdminAuthController::class,"store_keys"])->name("store_keys");

		    Route::get("pending_payment",[AdminPaymentController::class,"show_pending_payment"])->name("pending_payment");
		    Route::get("pendingpaymenttable",[AdminPaymentController::class,"show_pendingpaymenttable"])->name("pendingpaymenttable");
		    Route::get("payamount/{doc_id}",[AdminPaymentController::class,"show_payamount"]);
		    Route::post("updatepayment",[AdminPaymentController::class,"updatepayment"]);
		    Route::get("complete_payment",[AdminPaymentController::class,"complete_payment"])->name("complete_payment");
		    Route::get("completepaymenttable",[AdminPaymentController::class,"show_completepaymenttable"])->name("completepaymenttable");
		    Route::get("refundappointment/{id}",[AppointmentController::class,"show_refundappointment"])->name('admin.appointment.refund');

		    Route::get("payment-setting",[AdminPaymentSettingController::class,"show_payment_setting"])->name('payment-setting');
            Route::post("updategateway",[AdminPaymentSettingController::class,"show_update_gateway"])->name('updategateway');
            Route::get("paynow-history", [AdminPaymentSettingController::class, "showPaynowHistory"])->name('admin.paynow.history');
            Route::get("paynow-history/table", [AdminPaymentSettingController::class, "paynowHistoryTable"])->name('admin.paynow.history.table');

            Route::get("contact_list",[AdminPaymentController::class,"show_contact_list"])->name("contact_list");
		    Route::get("contact_list_table",[AdminPaymentController::class,"contact_list_table"])->name("contact_list_table");

		    Route::get("news",[AdminPaymentController::class,"show_news"])->name("news");
		    Route::post("sendnews",[AdminPaymentController::class,"sendnews"])->name("sendnews");

		    Route::get("subscription", [SubscriberController::class, "show_subscription"])->name("Subscription");
            Route::get("subscriptiontable", [SubscriberController::class, "show_subscriptiontable"])->name("subscriptiontable");
            Route::get("edit_subscription_price/{id}", [SubscriberController::class, "edit_subscription_price"]);

            Route::get("subscriber_doc", [SubscriberController::class, "subscriber_doc"])->name("subscriber_doc");
            Route::get("subscribetable", [SubscriberController::class, "show_subscribetable"])->name("subscribetable");

            Route::get("view_subscription_price/{id}", [SubscriberController::class, "view_subscription_price"]);

            Route::post("update_subscriptio_price", [SubscriberController::class, "update_subscriptio_price"]);

            Route::any("disable_order", [SubscriberController::class, "disable_order"])->name("disable-order");

            Route::any("active_order", [SubscriberController::class, "active_order"])->name("active-order");

            Route::get("banner",[AdminBannerController::class,"showbanner"]);
	        Route::get("bannertable",[AdminBannerController::class,"bannertable"]);
            Route::post("savebanner",[AdminBannerController::class,"savebanner"]);
	        Route::get("edit-img/{id}",[AdminBannerController::class,"edit_banner"]);
	        Route::post("updatebanner",[AdminBannerController::class,"updatebanner"]);
	        Route::get("deletebanner/{id}",[AdminBannerController::class,"deletebanner"]);

            // Medical Aid Societies
            Route::get("medical-aid", [App\Http\Controllers\Admin\MedicalAidController::class, "index"]);
            Route::post("save-medical-aid", [App\Http\Controllers\Admin\MedicalAidController::class, "store"]);
            Route::get("edit-medical-aid/{id}", [App\Http\Controllers\Admin\MedicalAidController::class, "edit"]);
            Route::post("update-medical-aid", [App\Http\Controllers\Admin\MedicalAidController::class, "update"]);
            Route::get("delete-medical-aid/{id}", [App\Http\Controllers\Admin\MedicalAidController::class, "destroy"]);

    	    Route::get("about",[HomeController::class,"about"]);
    		Route::get("Terms_condition",[HomeController::class,"admin_privacy"]);
    		Route::get("app_privacy",[HomeController::class,"app_privacy"]);
    		Route::get("data_deletion",[HomeController::class,"data_deletion"]);

    		Route::post("edit_about",[HomeController::class,"edit_about"]);
    		Route::post("edit_terms",[HomeController::class,"edit_terms"]);
    		Route::post("edit_app_privacy",[HomeController::class,"edit_app_privacy"]);
    		Route::post("edit_data_deletion",[HomeController::class,"edit_data_deletion"]);

    		Route::get("doctor_report",[ReportController::class,"doctor_report"])->name('doctor_report');
    		Route::get("user_report",[ReportController::class,"user_report"])->name('user_report');
    		Route::get("do_sub_report",[ReportController::class,"do_sub_report"])->name('do_sub_report');
    		Route::get("app_book_report",[ReportController::class,"app_book_report"])->name('app_book_report');

			Route::get("pharmacy",[AdminPharmacyController::class,"pharmacy"])->name('pharmacy');
			Route::get("pharmacytable",[AdminPharmacyController::class,"pharmacytable"])->name('pharmacy.table');
			Route::get("addpharmacy/{id}",[AdminPharmacyController::class,"addpharmacy"])->name('addpharmacy');
			Route::post("updatepharmacy",[AdminPharmacyController::class,"updatepharmacy"])->name('updatepharmacy');
			Route::post('pharmacy/{pharmacy}/toggle-recommendable', [AdminPharmacyController::class, 'toggleRecommendable'])->name('admin.pharmacy.toggle_recommendable');
			Route::get("deletepharmacy/{id}",[AdminPharmacyController::class,"deletepharmacy"])->name('deletepharmacy');

			Route::get("medicine/{pharmacy_id}",[AdminPharmacyController::class,"medicine"])->name('medicine');
			Route::get("medicinedelete/{pharmacy_id}",[AdminPharmacyController::class,"medicinedelete"])->name('medicinedelete');
			Route::get("addmedicine/{pharmacy_id}/{medicine_id}",[AdminPharmacyController::class,"addmedicine"]);
			Route::post("updatemedicine",[AdminPharmacyController::class,"updatemedicine"]);

            Route::get("pharmacyorder",[AdminPharmacyController::class,"pharmacyorder"])->name('pharmacyorder');
            Route::get("pharmacyordertable", [AdminPharmacyController::class, "pharmacyOrderTable"])->name('pharmacyorder.table');

			// Admin Wallet Management
            Route::get("wallet",[AdminAuthController::class,"showWalletManagement"])->name('admin.wallet');
            Route::get("wallet/table",[AdminAuthController::class,"walletListTable"])->name('admin.wallet.table');
            Route::get("wallet/{id}/transactions",[AdminAuthController::class,"showWalletTransactions"])->name('admin.wallet.transactions');
            Route::post("wallet/adjust",[AdminAuthController::class,"adjustWalletBalance"])->name('admin.wallet.adjust');
            Route::get("wallet/withdrawals",[AdminAuthController::class,"showWithdrawalRequests"])->name('admin.wallet.withdrawals');
            Route::post("wallet/withdrawals/process",[AdminAuthController::class,"processWithdrawalRequest"])->name('admin.wallet.process_withdrawal');
            
            // Medical Aid Providers Management
            Route::get("wallet/medical-aid-providers", [AdminMedicalAidController::class, "providersIndex"])->name('admin.medical_aid_providers.index');
            Route::get("wallet/medical-aid-providers/table", [AdminMedicalAidController::class, "providersTable"])->name('admin.medical_aid_providers.table');
            Route::post("wallet/medical-aid-providers", [AdminMedicalAidController::class, "storeProvider"])->name('admin.medical_aid_providers.store');
            Route::post("wallet/medical-aid-providers/update", [AdminMedicalAidController::class, "updateProvider"])->name('admin.medical_aid_providers.update');
            Route::delete("wallet/medical-aid-providers/{provider}", [AdminMedicalAidController::class, "destroyProvider"])->name('admin.medical_aid_providers.destroy');

            Route::get("wallet/medical-aid-submissions",[AdminMedicalAidController::class,"index"])->name('admin.medical_aid_submissions.index');
            Route::get("wallet/medical-aid-submissions/table",[AdminMedicalAidController::class,"medicalAidSubmissionsTable"])->name('admin.medical_aid_submissions.table');
            Route::get("wallet/medical-aid-submissions/{id}/show",[AdminMedicalAidController::class,"showSubmission"])->name('admin.medical_aid_submissions.show');
            Route::post("wallet/medical-aid-submissions/process",[AdminMedicalAidController::class,"processSubmission"])->name('admin.medical_aid_submissions.process');

            // Featured Management Routes
            Route::get('featured', [AdminFeaturedController::class, 'index'])->name('admin.featured.index');
            Route::get('featured/table', [AdminFeaturedController::class, 'featuredTable'])->name('admin.featured.table');
            Route::post('featured/feature', [AdminFeaturedController::class, 'feature'])->name('admin.featured.feature');
            Route::post('featured/unfeature', [AdminFeaturedController::class, 'unfeature'])->name('admin.featured.unfeature');

            // Referral System Management
            Route::group(['prefix' => 'referrals', 'as' => 'admin.referrals.'], function () {
                Route::get('settings', [\App\Http\Controllers\Admin\AdminReferralController::class, 'settings'])->name('settings');
                Route::post('settings', [\App\Http\Controllers\Admin\AdminReferralController::class, 'updateSettings'])->name('settings.update');
                Route::get('rewards', [\App\Http\Controllers\Admin\AdminReferralController::class, 'rewardsIndex'])->name('rewards.index');
                Route::get('rewards/create', [\App\Http\Controllers\Admin\AdminReferralController::class, 'rewardsCreate'])->name('rewards.create');
                Route::post('rewards', [\App\Http\Controllers\Admin\AdminReferralController::class, 'rewardsStore'])->name('rewards.store');
                Route::get('rewards/{reward}/edit', [\App\Http\Controllers\Admin\AdminReferralController::class, 'rewardsEdit'])->name('rewards.edit');
                Route::put('rewards/{reward}', [\App\Http\Controllers\Admin\AdminReferralController::class, 'rewardsUpdate'])->name('rewards.update');
                Route::delete('rewards/{reward}', [\App\Http\Controllers\Admin\AdminReferralController::class, 'rewardsDestroy'])->name('rewards.destroy');
                Route::get('history', [\App\Http\Controllers\Admin\AdminReferralController::class, 'history'])->name('history');
            });

            // Subscription Plan Management
            Route::get('subscriptions', [AdminSubscriptionController::class, 'index'])->name('admin.subscriptions.index');
            Route::get('subscriptions/table', [AdminSubscriptionController::class, 'table'])->name('admin.subscriptions.table');
            Route::post('subscriptions', [AdminSubscriptionController::class, 'store'])->name('admin.subscriptions.store');
            Route::post('subscriptions/update', [AdminSubscriptionController::class, 'update'])->name('admin.subscriptions.update');
            Route::delete('subscriptions/{subscription}', [AdminSubscriptionController::class, 'destroy'])->name('admin.subscriptions.destroy');

            // Admin User Routes
            Route::get('admins', [AdminUserController::class, 'index'])->name('admin.admins.index');
            Route::get('admins/create', [AdminUserController::class, 'create'])->name('admin.admins.create');
            Route::post('admins', [AdminUserController::class, 'store'])->name('admin.admins.store');
            Route::get('admins/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.admins.edit');
            Route::put('admins/{user}', [AdminUserController::class, 'update'])->name('admin.admins.update');
            Route::delete('admins/{user}', [AdminUserController::class, 'destroy'])->name('admin.admins.destroy');
            Route::get('languages/{locale}', [AdminLanguageController::class, 'index'])->name('admin.languages.index');
            Route::get('languages/{lang}/edit/{key}', [AdminLanguageController::class, 'edit'])->name('admin.languages.edit');
            Route::get('languages/{lang}/create', [AdminLanguageController::class, 'create'])->name('languages.create');
            Route::delete('languages/{lang}/{key}', [AdminLanguageController::class, 'destroy'])->name('languages.destroy');
            Route::post('languages/{lang}', [AdminLanguageController::class, 'store'])->name('languages.store');
            Route::post('languages/store-language', [AdminLanguageController::class, 'storeLanguage'])->name('languages.store-language');
            Route::post('languages/store-language', [AdminLanguageController::class, 'storeLanguage'])->name('languages.store-language');

            // Role Management Routes
            Route::group(['prefix' => 'roles', 'as' => 'admin.roles.'], function () {
                Route::get('/', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->name('index');
                Route::get('/{role}/edit', [\App\Http\Controllers\Admin\RoleController::class, 'edit'])->name('edit');
                Route::put('/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'update'])->name('update');
            });

            // Subscription Features Management Routes
            Route::get('subscriptions/{subscription}/features', [AdminSubscriptionController::class, 'manageFeatures'])->name('admin.subscriptions.features.index');
            Route::post('subscriptions/{subscription}/features', [AdminSubscriptionController::class, 'storeFeature'])->name('admin.subscriptions.features.store');
            Route::post('subscriptions/{subscription}/features/{feature}', [AdminSubscriptionController::class, 'updateFeature'])->name('admin.subscriptions.features.update');
            Route::delete('subscriptions/{subscription}/features/{feature}', [AdminSubscriptionController::class, 'destroyFeature'])->name('admin.subscriptions.features.destroy');

            // AI Credit Packages Management
            Route::get('ai-credits', [AdminAiCreditController::class, 'index'])->name('admin.ai_credits.index');
            Route::get('ai-credits/table', [AdminAiCreditController::class, 'table'])->name('admin.ai_credits.table');
            Route::post('ai-credits', [AdminAiCreditController::class, 'store'])->name('admin.ai_credits.store');
            Route::post('ai-credits/update', [AdminAiCreditController::class, 'update'])->name('admin.ai_credits.update');
            Route::delete('ai-credits/{package}', [AdminAiCreditController::class, 'destroy'])->name('admin.ai_credits.destroy');
            // Developer Console & API Management
            Route::get('developers', [\App\Http\Controllers\Admin\DeveloperController::class, 'index'])->name('admin.developers.index');
            Route::post('developers/test-push', [\App\Http\Controllers\Admin\DeveloperController::class, 'testPushToken'])->name('admin.developers.test_push');
            Route::post('developers/clear-logs', [\App\Http\Controllers\Admin\DeveloperController::class, 'clearDiagnosticLogs'])->name('admin.developers.clear_logs');
            Route::post('developers/clear-server-logs', [\App\Http\Controllers\Admin\DeveloperController::class, 'clearServerLogs'])->name('admin.developers.clear_server_logs');
            Route::post('developers/{id}/toggle', [\App\Http\Controllers\Admin\DeveloperController::class, 'toggleStatus'])->name('admin.developers.toggle');
            Route::post('developers/{id}/regenerate', [\App\Http\Controllers\Admin\DeveloperController::class, 'regenerateSecret'])->name('admin.developers.regenerate');
            Route::delete('developers/{id}', [\App\Http\Controllers\Admin\DeveloperController::class, 'destroy'])->name('admin.developers.destroy');

    });

});
});

});

// Public Developer Portal & API Documentation
Route::get('/developers', [\App\Http\Controllers\DeveloperPortalController::class, 'docs'])->name('developers.portal');
Route::get('/developer', [\App\Http\Controllers\DeveloperPortalController::class, 'docs']);
Route::get('/developers/api', [\App\Http\Controllers\DeveloperPortalController::class, 'docs'])->name('developers.docs');
Route::post('/developers/register', [\App\Http\Controllers\DeveloperPortalController::class, 'register'])->name('developers.register');

// UNICEF Venture Fund Femtech & Maternal/Child Health Platform Routes (Pillar 1 & Pillar 2)
Route::group(['middleware' => ['Localization']], function () {
    Route::get('/femtech-hub', [\App\Http\Controllers\Patient\FemtechController::class, 'index'])->name('femtech.hub');
    Route::get('/femtech/quick-exit', [\App\Http\Controllers\Patient\FemtechController::class, 'quickExit'])->name('femtech.quick_exit');
    Route::post('/femtech/maternal/triage', [\App\Http\Controllers\Patient\FemtechController::class, 'submitMaternalTriage'])->name('femtech.maternal.triage');
    Route::post('/femtech/maternal/vitals', [\App\Http\Controllers\Patient\FemtechController::class, 'logMaternalVitals'])->name('femtech.maternal.vitals');
    Route::post('/femtech/child/register', [\App\Http\Controllers\Patient\FemtechController::class, 'registerChildSchedule'])->name('femtech.child.register');
    Route::post('/femtech/child/reminder/{milestoneId}', [\App\Http\Controllers\Patient\FemtechController::class, 'triggerVaccineReminder'])->name('femtech.child.reminder');
    Route::post('/femtech/srhr/anonymous', [\App\Http\Controllers\Patient\FemtechController::class, 'startAnonymousSession'])->name('femtech.srhr.anonymous');
    Route::post('/femtech/srhr/ask', [\App\Http\Controllers\Patient\FemtechController::class, 'askSrhrAssistant'])->name('femtech.srhr.ask');
    Route::post('/femtech/srhr/order', [\App\Http\Controllers\Patient\FemtechController::class, 'orderDiscreetSupply'])->name('femtech.srhr.order');
    Route::post('/femtech/epds/submit', [\App\Http\Controllers\Patient\FemtechController::class, 'submitEpdsScreening'])->name('femtech.epds.submit');
    Route::get('/femtech/fhir/export/{patientId?}', [\App\Http\Controllers\Patient\FemtechController::class, 'exportFhirBundle'])->name('femtech.fhir.export');
    Route::get('/chw-portal', [\App\Http\Controllers\Patient\FemtechController::class, 'chwDashboard'])->name('chw.portal');
    Route::post('/chw-portal/intake', [\App\Http\Controllers\Patient\FemtechController::class, 'syncOfflineIntakes'])->name('chw.intake.submit');
});


