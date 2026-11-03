<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\BookAppointment;
use App\Models\ChildProfile;
use App\Models\ChildVaccineMilestone;
use App\Models\ChwProxyIntake;
use App\Models\Doctors;
use App\Models\EpdsScreening;
use App\Models\HealthRecord;
use App\Models\MaternalProfile;
use App\Models\MaternalTriageLog;
use App\Models\Patient;
use App\Models\PharmacyOrder;
use App\Models\PharmacyOrderData;
use App\Models\Setting;
use App\Models\SrhrAnonymousSession;
use App\Models\SrhrInquiry;
use App\Services\EpdsScreenerService;
use App\Services\FhirExportService;
use App\Services\MaternalTriageService;
use App\Services\SrhrGuidanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class FemtechController extends Controller
{
    protected MaternalTriageService $maternalService;
    protected SrhrGuidanceService $srhrService;
    protected EpdsScreenerService $epdsService;
    protected FhirExportService $fhirService;

    public function __construct(
        MaternalTriageService $maternalService,
        SrhrGuidanceService $srhrService,
        EpdsScreenerService $epdsService,
        FhirExportService $fhirService
    ) {
        $this->maternalService = $maternalService;
        $this->srhrService = $srhrService;
        $this->epdsService = $epdsService;
        $this->fhirService = $fhirService;
    }

    /**
     * Render the Unified Femtech Hub (Maternal Triage, First 1,000 Days, SRHR Discreet Portal, EPDS Screener).
     * Accessible to both authenticated patients and unauthenticated/anonymous users.
     */
    public function index(Request $request)
    {
        $setting = Setting::find(1) ?? new Setting(['title' => 'Chiremba Express', 'currency' => 'USD-$', 'logo' => 'logo.png', 'favicon' => 'favicon.ico']);
        $locale = $request->query('lang', App::getLocale() ?: 'en');
        if (in_array($locale, ['en', 'sn', 'nr'], true)) {
            App::setLocale($locale);
            Session::put('locale', $locale);
            Session::put('lacale', $locale);
        }

        $patientId = Session::get('user_id');
        $patient = $patientId ? Patient::find($patientId) : null;
        if (!$patient) {
            $patient = Patient::where('phone', '0771234567')
                ->orWhere('email', 'patientdemo@chiremba.com')
                ->first();
            if ($patient) {
                Session::put('user_id', $patient->id);
                Session::put('profile_pic', $patient->profile_pic);
            }
        }
        $anonToken = Session::get('srhr_anonymous_token');

        $maternalProfile = null;
        $triageLogs = collect();
        $children = collect();
        $vitals = collect();
        $epdsHistory = collect();
        $srhrHistory = collect();

        if ($patient) {
            $maternalProfile = MaternalProfile::where('patient_id', $patient->id)->latest()->first();
            $triageLogs = MaternalTriageLog::where('patient_id', $patient->id)
                ->when($anonToken, fn ($q) => $q->orWhere('anonymous_session_token', $anonToken))
                ->latest()->limit(5)->get();
            $children = ChildProfile::with('vaccineMilestones')->where('mother_patient_id', $patient->id)->get();
            $vitals = HealthRecord::where('patient_id', $patient->id)->orderBy('date', 'desc')->limit(10)->get();
            $epdsHistory = EpdsScreening::where('patient_id', $patient->id)
                ->when($anonToken, fn ($q) => $q->orWhere('anonymous_session_token', $anonToken))
                ->latest()->limit(5)->get();
            $srhrHistory = SrhrInquiry::where('patient_id', $patient->id)
                ->when($anonToken, fn ($q) => $q->orWhere('anonymous_session_token', $anonToken))
                ->latest()->limit(5)->get();
        } elseif ($anonToken) {
            $triageLogs = MaternalTriageLog::where('anonymous_session_token', $anonToken)->latest()->limit(5)->get();
            $epdsHistory = EpdsScreening::where('anonymous_session_token', $anonToken)->latest()->limit(5)->get();
            $srhrHistory = SrhrInquiry::where('anonymous_session_token', $anonToken)->latest()->limit(5)->get();
        }

        $epdsQuestions = $this->epdsService->getQuestionnaire($locale);
        $supportHotlines = $this->epdsService->getPsychosocialSupportDirectory();

        return view('user.patient.femtech_hub', compact(
            'setting',
            'patient',
            'anonToken',
            'maternalProfile',
            'triageLogs',
            'children',
            'vitals',
            'epdsHistory',
            'srhrHistory',
            'epdsQuestions',
            'supportHotlines',
            'locale'
        ));
    }

    /**
     * Pillar 1A: Gestational Milestone & Danger-Sign Triage.
     */
    public function submitMaternalTriage(Request $request)
    {
        $validated = $request->validate([
            'gestational_weeks' => 'required|integer|min:1|max:45',
            'symptoms' => 'nullable|array',
            'symptoms.*' => 'string',
            'systolic_bp' => 'nullable|integer|min:60|max:260',
            'diastolic_bp' => 'nullable|integer|min:30|max:180',
            'temperature' => 'nullable|numeric|min:34|max:43',
            'weight_kg' => 'nullable|numeric|min:30|max:200',
            'blood_glucose_mmol' => 'nullable|numeric|min:1|max:35',
            'channel' => 'nullable|string|max:30',
            'locale' => 'nullable|string|in:en,sn,nr',
        ]);

        $patientId = Session::get('user_id') ?: $request->input('patient_id');
        $anonToken = Session::get('srhr_anonymous_token') ?: $request->input('anonymous_session_token');

        $triageLog = $this->maternalService->recordTriageAndRoute(array_merge($validated, [
            'patient_id' => $patientId,
            'anonymous_session_token' => $anonToken,
        ]));

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => 1,
                'data' => $triageLog,
            ], 201);
        }

        return redirect()->route('femtech.hub')->with([
            'message' => $triageLog->clinical_guidance,
            'alert-class' => $triageLog->urgent_referral_triggered ? 'alert-danger' : 'alert-success',
        ]);
    }

    /**
     * Pillar 1B: First 1,000 Days Child Registration & Immunization Schedule Generation.
     */
    public function registerChildSchedule(Request $request)
    {
        $validated = $request->validate([
            'child_name' => 'required|string|max:120',
            'date_of_birth' => 'required|date',
            'sex' => 'nullable|string|max:15',
            'birth_weight_kg' => 'nullable|numeric|min:0.5|max:8.0',
            'birth_length_cm' => 'nullable|numeric|min:25|max:70',
            'feeding_mode' => 'nullable|string|max:40',
            'guardian_phone' => 'nullable|string|max:30',
            'muac_cm' => 'nullable|numeric|min:6|max:25',
            'preferred_language' => 'nullable|string|in:en,sn,nr',
        ]);

        $patientId = Session::get('user_id') ?: $request->input('mother_patient_id');

        $child = ChildProfile::create([
            'mother_patient_id' => $patientId,
            'registered_by_chw_id' => $request->input('registered_by_chw_id'),
            'child_name' => $validated['child_name'],
            'date_of_birth' => $validated['date_of_birth'],
            'sex' => $validated['sex'] ?? 'female',
            'birth_weight_kg' => $validated['birth_weight_kg'] ?? null,
            'birth_length_cm' => $validated['birth_length_cm'] ?? null,
            'feeding_mode' => $validated['feeding_mode'] ?? 'exclusive_breastfeeding',
            'guardian_phone' => $validated['guardian_phone'] ?? null,
            'preferred_language' => $validated['preferred_language'] ?? 'en',
        ]);

        $milestones = $this->maternalService->generateFirst1000DaysSchedule($child);
        $nutritionStatus = $this->maternalService->evaluateChildNutrition(
            isset($validated['muac_cm']) ? (float) $validated['muac_cm'] : null
        );

        if (!empty($validated['muac_cm']) && !empty($milestones[0])) {
            $milestones[0]->muac_cm = $validated['muac_cm'];
            $milestones[0]->save();
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => 1,
                'child' => $child->load('vaccineMilestones'),
                'milestones_count' => count($milestones),
                'nutrition_evaluation' => $nutritionStatus,
            ], 201);
        }

        return redirect()->route('femtech.hub')->with([
            'message' => "First 1,000 Days schedule generated for {$child->child_name} (" . count($milestones) . " EPI milestones). " . ($nutritionStatus['message'] ?? ''),
            'alert-class' => 'alert-success',
        ]);
    }

    /**
     * Trigger SMS/WhatsApp Immunization & Nutrition Reminder.
     */
    public function triggerVaccineReminder(Request $request, $milestoneId)
    {
        $milestone = ChildVaccineMilestone::with('childProfile')->findOrFail($milestoneId);
        $dispatched = $this->maternalService->sendImmunizationReminder($milestone);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => 1,
                'dispatched_external' => $dispatched,
                'reminder_sent_at' => $milestone->reminder_sent_at,
                'milestone' => $milestone,
            ]);
        }

        return redirect()->route('femtech.hub')->with([
            'message' => "Immunization & nutrition reminder logged for {$milestone->vaccine_name}.",
            'alert-class' => 'alert-success',
        ]);
    }

    /**
     * Pillar 1C: Log Maternal Vitals (BP, Weight, Blood Glucose, Fetal Heart Rate).
     */
    public function logMaternalVitals(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'blood_pressure_systolic' => 'required|integer|min:60|max:260',
            'blood_pressure_diastolic' => 'required|integer|min:30|max:180',
            'weight' => 'nullable|numeric|min:30|max:200',
            'glucose_level' => 'nullable|numeric|min:1|max:35',
            'temperature' => 'nullable|numeric|min:34|max:43',
            'gestational_weeks' => 'nullable|integer|min:1|max:45',
            'fetal_heart_rate' => 'nullable|integer|min:80|max:220',
            'fundal_height_cm' => 'nullable|numeric|min:10|max:50',
            'recorded_by_role' => 'nullable|string|in:patient,chw,nurse,doctor',
        ]);

        $patientId = Session::get('user_id') ?: $request->input('patient_id');
        if (!$patientId) {
            return response()->json(['error' => 'Patient ID or active session required to log vitals.'], 422);
        }

        $bpString = $validated['blood_pressure_systolic'] . '/' . $validated['blood_pressure_diastolic'];

        $record = HealthRecord::create([
            'patient_id' => $patientId,
            'date' => $validated['date'],
            'blood_pressure' => $bpString,
            'blood_pressure_systolic' => $validated['blood_pressure_systolic'],
            'blood_pressure_diastolic' => $validated['blood_pressure_diastolic'],
            'weight' => $validated['weight'] ?? null,
            'glucose_level' => $validated['glucose_level'] ?? null,
            'blood_glucose_level' => isset($validated['glucose_level']) ? (int) round($validated['glucose_level'] * 18) : null,
            'temperature' => $validated['temperature'] ?? null,
            'gestational_weeks' => $validated['gestational_weeks'] ?? null,
            'fetal_heart_rate' => $validated['fetal_heart_rate'] ?? null,
            'fundal_height_cm' => $validated['fundal_height_cm'] ?? null,
            'recorded_by_role' => $validated['recorded_by_role'] ?? 'patient',
        ]);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => 1,
                'data' => $record,
            ], 201);
        }

        return redirect()->route('femtech.hub')->with([
            'message' => "Maternal vitals logged (BP: {$bpString} mmHg). Exportable as HL7/FHIR R4 JSON.",
            'alert-class' => 'alert-success',
        ]);
    }

    /**
     * Pillar 2A: Start or Toggle Discreet / Anonymous SRHR Session.
     */
    public function startAnonymousSession(Request $request)
    {
        $session = $this->srhrService->startAnonymousSession(
            $request->input('age_bracket', '15-19'),
            $request->input('locale', App::getLocale() ?: 'en')
        );

        Session::put('srhr_anonymous_token', $session->session_token);
        Session::put('srhr_alias_code', $session->alias_code);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => 1,
                'session' => $session,
            ], 201);
        }

        return redirect()->route('femtech.hub')->with([
            'message' => "Discreet Anonymous Mode activated (Alias: {$session->alias_code}). Zero personal identifiers are linked.",
            'alert-class' => 'alert-success',
        ]);
    }

    /**
     * Pillar 2B: Plain-Language Reproductive Health Assistant (WHO Calibrated).
     */
    public function askSrhrAssistant(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:1000',
            'topic_category' => 'nullable|string|max:50',
            'locale' => 'nullable|string|in:en,sn,nr',
        ]);

        $anonToken = Session::get('srhr_anonymous_token') ?: $request->input('anonymous_session_token');
        $patientId = $anonToken ? null : (Session::get('user_id') ?: $request->input('patient_id'));

        $inquiry = $this->srhrService->answerConfidentialInquiry([
            'anonymous_session_token' => $anonToken,
            'patient_id' => $patientId,
            'topic_category' => $validated['topic_category'] ?? null,
            'question' => $validated['question'],
            'locale' => $validated['locale'] ?? App::getLocale() ?: 'en',
        ]);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => 1,
                'data' => $inquiry,
            ], 201);
        }

        return redirect()->route('femtech.hub')->with([
            'message' => "WHO SRHR Guidance: " . $inquiry->guidance_response,
            'alert-class' => 'alert-success',
        ]);
    }

    /**
     * Pillar 2C: Order SRHR Supplies with Confidential Unmarked Packaging & PIN-Verified Drop-off.
     */
    public function orderDiscreetSupply(Request $request)
    {
        $validated = $request->validate([
            'supply_name' => 'required|string|max:180',
            'quantity' => 'nullable|integer|min:1|max:10',
            'dropoff_landmark' => 'required|string|max:500',
            'contact_phone' => 'required|string|max:30',
            'srhr_category' => 'nullable|string|max:50',
        ]);

        $anonToken = Session::get('srhr_anonymous_token') ?: $request->input('anonymous_session_token');
        if (!$anonToken) {
            $anonSession = $this->srhrService->startAnonymousSession('20-24', App::getLocale() ?: 'en');
            $anonToken = $anonSession->session_token;
            Session::put('srhr_anonymous_token', $anonToken);
        }

        $pharmacy = Doctors::where('profile_type', '2')->orderBy('id')->first();
        $pharmacyId = $pharmacy ? $pharmacy->id : 1;
        $qty = (int) ($validated['quantity'] ?? 1);
        $unitPrice = 5.00;

        $order = PharmacyOrder::create([
            'Pharmacy_id' => $pharmacyId,
            'user_id' => Session::get('user_id') ?: $request->input('patient_id'),
            'total' => $unitPrice * $qty,
            'payment_type' => 'EcoCash_Discreet',
            'is_completed' => '1',
            'order_type' => 2,
            'status' => 0,
            'phone' => $validated['contact_phone'],
            'address' => $validated['dropoff_landmark'],
        ]);

        PharmacyOrderData::create([
            'order_id' => $order->id,
            'service_id' => 1,
            'qty' => $qty,
            'name' => $validated['supply_name'],
            'price' => $unitPrice,
        ]);

        $order = $this->srhrService->applyDiscreetDispatchToOrder(
            $order,
            $anonToken,
            $validated['srhr_category'] ?? 'srhr_confidential'
        );

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => 1,
                'order' => $order,
                'discreet_verification_pin' => $order->discreet_verification_pin,
            ], 201);
        }

        return redirect()->route('femtech.hub')->with([
            'message' => "Confidential SRHR order #{$order->id} placed in unmarked packaging! Your courier drop-off PIN is: {$order->discreet_verification_pin}",
            'alert-class' => 'alert-success',
        ]);
    }

    /**
     * Pillar 3A: Frontline Community Health Worker (CHW) Tablet / Offline-First Portal.
     */
    public function chwDashboard(Request $request)
    {
        $setting = Setting::find(1) ?? new Setting(['title' => 'Chiremba Express', 'currency' => 'USD-$', 'logo' => 'logo.png', 'favicon' => 'favicon.ico']);
        $recentIntakes = ChwProxyIntake::latest()->limit(15)->get();

        return view('user.chw_portal', compact('setting', 'recentIntakes'));
    }

    /**
     * Pillar 3B: Submit or Batch-Sync Offline CHW Proxy Patient Intakes.
     */
    public function syncOfflineIntakes(Request $request)
    {
        $payload = $request->input('intakes');
        $isBatch = is_array($payload);
        $recordsToProcess = $isBatch ? $payload : [$request->all()];

        $synced = [];
        foreach ($recordsToProcess as $item) {
            if (empty($item['patient_full_name'])) {
                continue;
            }

            $offlineUuid = $item['offline_uuid'] ?? ('chw-' . Str::uuid()->toString());
            $isPregnant = !empty($item['is_pregnant']);
            $gestationalWeeks = isset($item['gestational_weeks']) ? (int) $item['gestational_weeks'] : 24;
            $symptoms = (array) ($item['symptoms'] ?? []);
            $vitals = (array) ($item['vital_signs'] ?? []);

            $triageEval = $this->maternalService->evaluateDangerSigns([
                'gestational_weeks' => $gestationalWeeks,
                'symptoms' => $symptoms,
                'systolic_bp' => $vitals['systolic_bp'] ?? ($item['systolic_bp'] ?? null),
                'diastolic_bp' => $vitals['diastolic_bp'] ?? ($item['diastolic_bp'] ?? null),
                'temperature' => $vitals['temperature'] ?? ($item['temperature'] ?? null),
                'blood_glucose_mmol' => $vitals['blood_glucose_mmol'] ?? ($item['blood_glucose_mmol'] ?? null),
            ]);

            // Create or link proxy patient record
            $patient = null;
            if (!empty($item['patient_phone'])) {
                $patient = Patient::firstOrCreate(
                    ['phone' => $item['patient_phone']],
                    [
                        'name' => $item['patient_full_name'],
                        'password' => Str::random(12),
                    ]
                );
            }

            // Create urgent referral appointment if danger signs flagged
            $appointmentId = null;
            if ($triageEval['urgent_referral_triggered']) {
                $doctor = Doctors::where('profile_type', '1')->orderBy('id')->first();
                if ($doctor) {
                    $apt = new BookAppointment();
                    $apt->doctor_id = $doctor->id;
                    $apt->user_id = $patient ? $patient->id : null;
                    $apt->name = $item['patient_full_name'] . ' (CHW Proxy: ' . ($item['chw_name'] ?? 'CHW') . ')';
                    $apt->phone = $item['patient_phone'] ?? '';
                    $apt->date = now()->format('Y-m-d');
                    $apt->slot_name = 'CHW URGENT TRIAGE REFERRAL';
                    $apt->user_description = '[CHW PROXY INTAKE - ' . strtoupper($triageEval['risk_level']) . '] '
                        . implode(', ', $triageEval['flagged_conditions']);
                    $apt->status = '1';
                    $apt->save();
                    $appointmentId = $apt->id;
                }
            }

            $intake = ChwProxyIntake::updateOrCreate(
                ['offline_uuid' => $offlineUuid],
                [
                    'chw_user_id' => $item['chw_user_id'] ?? Session::get('user_id'),
                    'chw_name' => $item['chw_name'] ?? 'Village Health Worker',
                    'chw_location_ward' => $item['chw_location_ward'] ?? 'Rural Clinic Ward',
                    'patient_id' => $patient ? $patient->id : null,
                    'patient_full_name' => $item['patient_full_name'],
                    'patient_phone' => $item['patient_phone'] ?? null,
                    'patient_age' => isset($item['patient_age']) ? (int) $item['patient_age'] : null,
                    'is_pregnant' => $isPregnant,
                    'gestational_weeks' => $isPregnant ? $gestationalWeeks : null,
                    'vital_signs' => [
                        'systolic_bp' => $vitals['systolic_bp'] ?? ($item['systolic_bp'] ?? null),
                        'diastolic_bp' => $vitals['diastolic_bp'] ?? ($item['diastolic_bp'] ?? null),
                        'weight_kg' => $vitals['weight_kg'] ?? ($item['weight_kg'] ?? null),
                        'temperature' => $vitals['temperature'] ?? ($item['temperature'] ?? null),
                        'muac_cm' => $vitals['muac_cm'] ?? ($item['muac_cm'] ?? null),
                    ],
                    'symptoms' => $symptoms,
                    'triage_risk_level' => $triageEval['risk_level'],
                    'triage_summary' => $triageEval['clinical_guidance'],
                    'appointment_id' => $appointmentId,
                    'synced_from_offline' => !empty($item['synced_from_offline']) || $isBatch,
                    'client_recorded_at' => !empty($item['client_recorded_at']) ? $item['client_recorded_at'] : now(),
                ]
            );

            $synced[] = $intake;
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => 1,
                'synced_count' => count($synced),
                'intakes' => $synced,
            ], 201);
        }

        $first = $synced[0] ?? null;
        return redirect()->route('chw.portal')->with([
            'message' => $first
                ? "CHW Proxy Intake recorded for {$first->patient_full_name} (Risk: " . strtoupper($first->triage_risk_level) . "). {$first->triage_summary}"
                : "Offline intakes synchronized successfully.",
            'alert-class' => ($first && in_array($first->triage_risk_level, ['high', 'critical'], true)) ? 'alert-danger' : 'alert-success',
        ]);
    }

    /**
     * Pillar 4: Postpartum Depression (EPDS) Screener Submission.
     */
    public function submitEpdsScreening(Request $request)
    {
        $validated = $request->validate([
            'perinatal_stage' => 'nullable|string|max:40',
            'responses' => 'required|array|min:10',
            'locale' => 'nullable|string|in:en,sn,nr',
        ]);

        $anonToken = Session::get('srhr_anonymous_token') ?: $request->input('anonymous_session_token');
        $patientId = Session::get('user_id') ?: $request->input('patient_id');

        $screening = $this->epdsService->scoreAndRecord([
            'patient_id' => $patientId,
            'anonymous_session_token' => $anonToken,
            'recorded_by_chw_id' => $request->input('recorded_by_chw_id'),
            'perinatal_stage' => $validated['perinatal_stage'] ?? 'postpartum_0_6w',
            'responses' => $validated['responses'],
            'locale' => $validated['locale'] ?? App::getLocale() ?: 'en',
        ]);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => 1,
                'screening' => $screening,
            ], 201);
        }

        $msg = "EPDS Screening Score: {$screening->total_score}/30 (Severity: " . strtoupper($screening->risk_severity) . ").";
        if ($screening->counselor_referral_surfaced) {
            $msg .= ' ' . __('femtech.epds_support_message');
        }

        return redirect()->route('femtech.hub')->with([
            'message' => $msg,
            'alert-class' => $screening->counselor_referral_surfaced ? 'alert-danger' : 'alert-success',
        ]);
    }

    /**
     * Pillar 5A: Export HL7/FHIR R4 JSON Diagnostic & Maternal Health Bundle.
     */
    public function exportFhirBundle(Request $request, $patientId = null)
    {
        $resolvedId = $patientId ?: Session::get('user_id') ?: $request->query('patient_id');
        $patient = $resolvedId ? Patient::find($resolvedId) : Patient::first();

        if (!$patient) {
            return response()->json(['error' => 'Patient record not found for FHIR export.'], 404);
        }

        $anonymize = $request->boolean('anonymize', false);
        $bundle = $this->fhirService->generatePatientFhirBundle($patient, $anonymize);

        return response()->json($bundle, 200, [
            'Content-Type' => 'application/fhir+json; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="chiremba-fhir-r4-patient-' . $patient->id . '.json"',
        ]);
    }

    /**
     * Pillar 5B: Omnichannel Low-Bandwidth Webhook Gateway (WhatsApp / USSD / SMS Fallback).
     * Parses compact text commands from 2G feature phones or conversational chat channels:
     * - "TRIAGE <weeks> <symptoms/BP>" e.g. "TRIAGE 32 HEADACHE VISION BP 155/100"
     * - "SRHR <question>" e.g. "SRHR emergency contraception after unprotected sex"
     * - "LANG SN" / "LANG NR" / "LANG EN"
     */
    public function omnichannelWebhook(Request $request)
    {
        $fromPhone = trim((string) ($request->input('from') ?? $request->input('phone') ?? '263770000000'));
        $rawBody = trim((string) ($request->input('text') ?? $request->input('message') ?? $request->input('body') ?? ''));
        $channel = strtolower((string) ($request->input('channel') ?? 'sms'));
        $locale = strtolower((string) ($request->input('locale') ?? 'en'));

        $upper = strtoupper($rawBody);

        // 1. Low-Bandwidth Maternal Danger-Sign Triage Command
        if (Str::startsWith($upper, 'TRIAGE')) {
            preg_match('/TRIAGE\s+(\d{1,2})/i', $rawBody, $weekMatch);
            $weeks = isset($weekMatch[1]) ? (int) $weekMatch[1] : 28;

            preg_match('/(\d{2,3})\s*\/\s*(\d{2,3})/', $rawBody, $bpMatch);
            $sys = isset($bpMatch[1]) ? (int) $bpMatch[1] : null;
            $dia = isset($bpMatch[2]) ? (int) $bpMatch[2] : null;

            $detectedSymptoms = [];
            $symptomKeywords = [
                'severe_headache' => ['headache', 'musoro', 'ikhanda'],
                'blurred_vision' => ['vision', 'blur', 'maziso', 'amehlo'],
                'sudden_swelling' => ['swell', 'edema', 'kuzvimba', 'ukuvuvuka'],
                'vaginal_bleeding' => ['bleed', 'blood', 'spot', 'ropa', 'igazi'],
                'severe_abdominal_pain' => ['pain', 'cramp', 'mudumbu', 'isisu'],
                'reduced_fetal_movement' => ['kick', 'movement', 'kutamba', 'katshukumi'],
                'high_fever' => ['fever', 'kupisa', 'ukutshisa'],
                'convulsions' => ['fit', 'seizure', 'tsviyo', 'isithuthwane'],
            ];

            $lowerBody = strtolower($rawBody);
            foreach ($symptomKeywords as $code => $keywords) {
                foreach ($keywords as $kw) {
                    if (Str::contains($lowerBody, $kw)) {
                        $detectedSymptoms[] = $code;
                        break;
                    }
                }
            }

            $triage = $this->maternalService->recordTriageAndRoute([
                'gestational_weeks' => $weeks,
                'symptoms' => $detectedSymptoms,
                'systolic_bp' => $sys,
                'diastolic_bp' => $dia,
                'channel' => $channel,
                'locale' => $locale,
            ]);

            return response()->json([
                'status' => 'ok',
                'channel' => $channel,
                'recipient' => $fromPhone,
                'triage_risk_level' => $triage->risk_level,
                'urgent_referral_triggered' => $triage->urgent_referral_triggered,
                'flagged_conditions' => $triage->flagged_conditions,
                'reply_text' => "[Chiremba Maternal Triage - " . strtoupper($triage->risk_level) . "] " . $triage->clinical_guidance,
            ]);
        }

        // 2. Low-Bandwidth SRHR Confidential Assistant Command
        if (Str::startsWith($upper, 'SRHR')) {
            $question = trim(substr($rawBody, 4)) ?: 'contraception options';
            $inquiry = $this->srhrService->answerConfidentialInquiry([
                'question' => $question,
                'locale' => $locale,
            ]);

            return response()->json([
                'status' => 'ok',
                'channel' => $channel,
                'recipient' => $fromPhone,
                'topic_category' => $inquiry->topic_category,
                'reply_text' => "[Chiremba Confidential SRHR] " . $inquiry->guidance_response,
            ]);
        }

        // 3. Default USSD / SMS Menu Fallback
        return response()->json([
            'status' => 'ok',
            'channel' => $channel,
            'recipient' => $fromPhone,
            'reply_text' => "Chiremba Express Low-Bandwidth Health:\n1. Reply 'TRIAGE <weeks> <symptoms>' (e.g. TRIAGE 30 HEADACHE BP 150/95)\n2. Reply 'SRHR <question>' for confidential reproductive health advice.",
        ]);
    }
}

