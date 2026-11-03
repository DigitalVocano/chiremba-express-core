<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\UserAuthController;
use App\Http\Controllers\API\Auth\DoctorAuthController;
use App\Http\Controllers\API\DoctorController;
use App\Http\Controllers\API\AppointmentController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\Shared\AiController as SharedAiController;
use App\Http\Controllers\API\GeneralController;
use App\Http\Controllers\API\PharmacyController;
use App\Http\Controllers\API\PatientController;
use App\Http\Controllers\API\CommonController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\Shared\AgoraController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(['middleware' => ['Localization']], function () {

    Route::any("searchdoctor", [DoctorController::class, "showsearchdoctor"]);
    Route::any("nearbydoctor", [DoctorController::class, "nearbydoctor"]);
    Route::post("register", [UserAuthController::class, "postregisterpatient"]);
    // Route::any("user_reject_appointment", [AppointmentController::class, "user_reject_appointment"]); 

    Route::get("user-health-records", [PatientController::class, "getUserHealthRecords"]);

    Route::any("savetoken", [UserAuthController::class, "storetoken"]);
    Route::any("login", [UserAuthController::class, "showlogin"]);
    Route::any("unified-login", [UserAuthController::class, "unifiedLogin"]);
    Route::post("send-otp", [UserAuthController::class, "sendOtp"]);
    Route::post("delete-account", [UserAuthController::class, "deleteAccount"]);
    Route::post("doctorregister", [DoctorAuthController::class, "doctorregister"]);
    Route::any("doctorlogin", [DoctorAuthController::class, "doctorlogin"]);
    Route::any("getspeciality", [GeneralController::class, "getspeciality"]);
    Route::any("medical_aid_societies", [CommonController::class, "get_medical_aid_societies"]);
    Route::any("bookappointment", [AppointmentController::class, "bookappointment"]);
    Route::any("appointment/verify-payment", [AppointmentController::class, "verifyAppointmentPayment"]);
    Route::any("viewdoctor", [DoctorController::class, "viewdoctor"]);
    Route::any("doctor_profile_stats", [DoctorController::class, "doctor_profile_stats"]);
    Route::any("doctordetail", [DoctorController::class, "viewdoctor"]); // Alias for viewdoctor to fix 404
    Route::any("getlistofdoctorbyspecialty", [DoctorController::class, "getListOfDoctorBySpecialty"]);
    Route::any("addreview", [ReviewController::class, "addreview"]);
    Route::any("appointmentdetail", [DoctorController::class, "appointment_detail"]);
    Route::any("getslot", [AppointmentController::class, "getslotdata"]);
    
    // Patient Routes
    Route::get("wallet", [PatientController::class, "getWallet"]);
    Route::any("usersuappointment", [PatientController::class, "usersupcomingappointment"]);
    Route::any("userspastappointment", [PatientController::class, "userspastappointment"]);
    Route::any("usereditprofile", [PatientController::class, "usereditprofile"]);
    Route::any("patient_detail", [PatientController::class, "getPatientDetail"]);
    Route::any("data_list", [PatientController::class, "data_list"]);
    Route::any("Reportspam", [PatientController::class, "reportspam"]);
    Route::get("medical-records", [\App\Http\Controllers\Patient\HealthRecordController::class, "showMedicalRecordsWallet"]);
    Route::post("upload-medical-record", [\App\Http\Controllers\Patient\HealthRecordController::class, "uploadMedicalRecord"]);
    Route::any("delete-medical-record/{id?}", [\App\Http\Controllers\Patient\HealthRecordController::class, "deleteMedicalRecord"]);
    Route::post("update-medical-record", [\App\Http\Controllers\Patient\HealthRecordController::class, "updateMedicalRecord"]);


    // Doctor Routes
    Route::any("appointmentstatuschange", [DoctorController::class, "appointmentstatuschange"]);
    Route::any("doctoruappointment", [DoctorController::class, "doctoruappointment"]);
    Route::any("doctorpastappointment", [DoctorController::class, "doctorpastappointment"]);
    Route::any("doctorallappointment", [DoctorController::class, "doctorallappointment"]);
    Route::any("doctoreditprofile", [DoctorController::class, "doctoreditprofile"]);
    Route::any("doctor_subscription_list", [DoctorController::class, "doctor_subscription_list"]);
    Route::get("doctor_wallet", [DoctorController::class, "doctor_wallet"]);
    Route::get("getdoctorschedule", [DoctorController::class, "getdoctorschedule"]);
    Route::get("getholiday", [DoctorController::class, "getholiday"]);
    Route::any("saveholiday", [DoctorController::class, "saveholiday"]);
    Route::any("deleteholiday", [DoctorController::class, "deleteholiday"]);
    Route::get("checkholiday", [DoctorController::class, "checkholiday"]);
    Route::any("income_report", [DoctorController::class, "income_report"]);
    Route::post("doctor/subscribe-with-wallet", [DoctorController::class, "subscribeWithWallet"]);
    Route::any("subscribewithwallet", [DoctorController::class, "subscribeWithWallet"]);
    Route::any("pharmacy/subscribe-with-wallet", [DoctorController::class, "subscribeWithWallet"]);
    Route::any("add_medicine_to_app", [DoctorController::class, "add_medicine_to_app"]);
    Route::any("upload_image", [DoctorController::class, "upload_image"]);
    
    // Doctor AI Routes (TODO: Secure with auth:api middleware)
    Route::post('doctor/ai/get-prescription-suggestion', [SharedAiController::class, 'getPrescriptionSuggestion']);
    Route::post('doctor/ai/analyze-report', [SharedAiController::class, 'analyzeReport']);

    Route::get("getalldoctors", [DoctorController::class, "getalldoctors"]);

    // Common Routes
    Route::any("bannerlist", [CommonController::class, "banner_list"]);
    Route::any("about", [CommonController::class, "about"]);
    Route::get("privacy-policy", [CommonController::class, "privacy_policy"]);
    Route::any("get_bankdetails", [CommonController::class, "get_bankdetails"]);
    Route::any("medical_aid_societies", [CommonController::class, "get_medical_aid_societies"]);
    Route::any("get_notifications", [CommonController::class, "get_notifications"]);
    Route::any("mark_notification_read", [CommonController::class, "mark_notification_read"]);
    Route::any("mediaupload", [CommonController::class, "mediaupload"]);
    Route::any("mobile-telemetry", [CommonController::class, "logMobileTelemetry"]);
    Route::post("chat/send-push", [CommonController::class, "sendChatPush"]);

    // Pharmacy Routes
    Route::any("pharmacyregister", [PharmacyController::class, "pharmacyregister"]);
    Route::any("pharmacylogin", [PharmacyController::class, "pharmacylogin"]);
    Route::any("pharmacy_medicines", [PharmacyController::class, "pharmacy_medicines"]);
    Route::any("add_pharmacy_medicine", [PharmacyController::class, "add_pharmacy_medicine"]);
    Route::get("most_used_medicine", [PharmacyController::class, "most_used_medicine"]);
    Route::any("search_master_medicine", [PharmacyController::class, "search_master_medicine"]);
    Route::get("get_categories", [PharmacyController::class, "get_categories"]);
    Route::any("medicine_order", [PharmacyController::class, "medicine_order"]);
    Route::post("prescription_order", [PharmacyController::class, "prescription_order"]);
    Route::any("get_pharmacy_order_list", [PharmacyController::class, "get_pharmacy_order_list"]);
    Route::any("get_user_order_list", [PharmacyController::class, "get_user_order_list"]);
    Route::any("pharmacy_dashboard", [PharmacyController::class, "pharmacy_dashboard"]);

    // Payment Routes
    Route::post("initiate_payment", [PaymentController::class, "initiate_payment"]);
    Route::get("verify_payment", [PaymentController::class, "verify_payment"]);
    Route::get("payment/ecocash/status", [App\Http\Controllers\Shared\EcocashController::class, "checkStatus"]);

    // LiveKit Routes
    Route::post("agora/token", [\App\Http\Controllers\Shared\LiveKitController::class, "token"]);
    Route::post("livekit/token", [\App\Http\Controllers\Shared\LiveKitController::class, "token"]);
    Route::post("livekit/call-notification", [\App\Http\Controllers\Shared\LiveKitController::class, "sendCallNotification"]);
    Route::post("livekit/cancel-call", [\App\Http\Controllers\Shared\LiveKitController::class, "cancelCallNotification"]);

    // AI Routes
    Route::post('user/ask-ai', [App\Http\Controllers\Patient\AiController::class, 'askAiAssistant']);
    Route::post('user/ai-assistant/download', [App\Http\Controllers\Patient\AiController::class, 'downloadAiAssistantReport']);
    Route::get('user/ai-history', [App\Http\Controllers\Patient\AiController::class, 'getHistory']);
});
Route::group(['middleware' => ['Localization', 'auth:api']], function () {
    Route::post('/health-records/manual', [\App\Http\Controllers\Patient\HealthRecordController::class, 'addHealthRecordManual']);
    Route::post('/health-records/smartwatch', [\App\Http\Controllers\Patient\HealthRecordController::class, 'addHealthRecordSmartwatch']);
});

Route::post('/test-app-manager-post', function () {
    return response()->json(['message' => 'Test AppManager POST successful']);
});

// Public Developer API v1 (Secured with X-API-KEY)
Route::prefix('v1')->middleware('dev.api')->group(function () {
    Route::get('/doctors', [\App\Http\Controllers\DeveloperPortalController::class, 'getDoctors']);
    Route::get('/pharmacies', [\App\Http\Controllers\DeveloperPortalController::class, 'getPharmacies']);
    Route::get('/departments', [\App\Http\Controllers\DeveloperPortalController::class, 'getDepartments']);
});

// UNICEF Femtech & Maternal/Child Health Open API & Omnichannel Interoperability Layer (v1)
Route::prefix('v1/femtech')->middleware(['Localization'])->group(function () {
    Route::post('/maternal/triage', [\App\Http\Controllers\Patient\FemtechController::class, 'submitMaternalTriage']);
    Route::post('/maternal/vitals', [\App\Http\Controllers\Patient\FemtechController::class, 'logMaternalVitals']);
    Route::post('/child/schedule', [\App\Http\Controllers\Patient\FemtechController::class, 'registerChildSchedule']);
    Route::post('/child/reminder/{milestoneId}', [\App\Http\Controllers\Patient\FemtechController::class, 'triggerVaccineReminder']);
    Route::post('/srhr/anonymous-session', [\App\Http\Controllers\Patient\FemtechController::class, 'startAnonymousSession']);
    Route::post('/srhr/assistant', [\App\Http\Controllers\Patient\FemtechController::class, 'askSrhrAssistant']);
    Route::post('/srhr/discreet-order', [\App\Http\Controllers\Patient\FemtechController::class, 'orderDiscreetSupply']);
    Route::post('/chw/sync', [\App\Http\Controllers\Patient\FemtechController::class, 'syncOfflineIntakes']);
    Route::post('/epds/screen', [\App\Http\Controllers\Patient\FemtechController::class, 'submitEpdsScreening']);
    Route::get('/fhir/patient/{patientId?}', [\App\Http\Controllers\Patient\FemtechController::class, 'exportFhirBundle']);
    Route::post('/webhook/omnichannel', [\App\Http\Controllers\Patient\FemtechController::class, 'omnichannelWebhook']);
// End UNICEF Femtech v1 API
});

