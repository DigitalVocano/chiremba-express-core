<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AiAssistantService;
use App\Models\BookAppointment;
use App\Models\Subscriber;
use App\Models\ApImdUplod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AiController extends Controller
{
    public function getPrescriptionSuggestion(Request $request, AiAssistantService $aiAssistant)
    {
        $request->validate([
            'appointment_id' => 'required|exists:book_appointment,id',
        ]);

        $doctorId = \Session::get("user_id") ?: $request->get('doctor_id');

        $appointment = BookAppointment::find($request->appointment_id);
        if (!$appointment) {
            return response()->json(['error' => 'Appointment not found.'], 404);
        }

        if (!$doctorId) {
            $doctorId = $appointment->doctor_id;
        }

        $setting = \App\Models\Setting::first();
        $isAiFree = (bool)($setting->is_ai_free ?? 1);

        $activeSubscription = Subscriber::where('subscriber_id', $doctorId)
            ->where('subscriber_type', \App\Models\Doctors::class)
            ->where('status', '2')
            ->latest('id')
            ->first();

        if (!$isAiFree) {
            if (!$activeSubscription) {
                return response()->json(['error' => 'An active subscription is required to use AI features.'], 403);
            }

            if (is_null($activeSubscription->remaining_ai_credits) && $activeSubscription->subscription) {
                $aiFeature = $activeSubscription->subscription->features->where('code', 'ai_prescription_assistant')->first();
                if ($aiFeature) {
                    $creditsToSet = $aiFeature->value ?? $activeSubscription->subscription->ai_credits ?? 0;
                    $activeSubscription->remaining_ai_credits = (int) $creditsToSet;
                    $activeSubscription->save();
                }
            }

            if (($activeSubscription->remaining_ai_credits ?? 0) <= 0) {
                return response()->json(['error' => 'You have no AI credits remaining. Please upgrade your plan.'], 403);
            }
        }

        $symptoms = $appointment->user_description ?? $appointment->description ?? "";
        $doctorNotes = $request->get('doctor_notes') ?? $request->get('symptoms_override');

        if (!empty($doctorNotes)) {
            $symptoms = "Patient Symptoms: " . $symptoms . "\nDoctor Diagnostic Notes: " . $doctorNotes;
        }

        if (empty($symptoms)) {
            return response()->json(['error' => 'No patient symptoms or doctor diagnostic notes provided for AI analysis.'], 400);
        }

        DB::beginTransaction();
        try {
            if (!$isAiFree && $activeSubscription && ($activeSubscription->remaining_ai_credits ?? 0) > 0) {
                $activeSubscription->decrement('remaining_ai_credits');
            }

            $suggestions = $aiAssistant->getPrescriptionSuggestion($symptoms);
            DB::commit();

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions,
                'remaining_credits' => $activeSubscription->remaining_ai_credits ?? 999,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AI Suggestion Error: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred while generating suggestions: ' . $e->getMessage()], 500);
        }
    }

    public function analyzePatientReport(Request $request, AiAssistantService $aiAssistant)
    {
        return $this->analyzeReport($request, $aiAssistant);
    }

    public function analyzeReport(Request $request, AiAssistantService $aiAssistant)
    {
        $doctorId = (\Session::has("user_id") && \Session::get("role_id") == '2')
            ? \Session::get("user_id")
            : $request->get('doctor_id');

        if (!$doctorId) {
            return response()->json(['error' => 'Unauthorized access.'], 401);
        }

        $request->validate([
            'report_file' => 'required|file|mimes:jpeg,png,jpg,pdf|max:10240',
        ]);

        $setting = \App\Models\Setting::first();
        $isAiFree = (bool)($setting->is_ai_free ?? 1);

        $activeSubscription = Subscriber::where('subscriber_id', $doctorId)
            ->where('subscriber_type', \App\Models\Doctors::class)
            ->where('status', '2')
            ->latest('id')
            ->first();

        if (!$isAiFree) {
            if (!$activeSubscription || is_null($activeSubscription->remaining_ai_credits)) {
                return response()->json(['error' => 'Your subscription plan does not include AI features.'], 403);
            }

            if ($activeSubscription->remaining_ai_credits <= 0) {
                return response()->json(['error' => 'You have no AI credits remaining for analysis.'], 403);
            }
        }

        if ($request->hasFile('report_file')) {
            DB::beginTransaction();
            try {
                if (!$isAiFree && $activeSubscription && ($activeSubscription->remaining_ai_credits ?? 0) > 0) {
                    $activeSubscription->decrement('remaining_ai_credits');
                }

                $analysis = $aiAssistant->analyzeDocumentReport($request->file('report_file'));

                if (isset($analysis['error'])) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'error' => $analysis['error']], 400);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'analysis' => $analysis,
                    'remaining_credits' => $activeSubscription->remaining_ai_credits ?? 999,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('AI Report Analysis Error: ' . $e->getMessage());
                return response()->json(['error' => 'An unexpected error occurred during analysis.'], 500);
            }
        }

        return response()->json(['error' => 'No file was uploaded.'], 400);
    }

    public function getReportDetails($id)
    {
        if (!\Session::has("user_id") || \Session::get("role_id") != '2') {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $report = ApImdUplod::with('discussions')->where('id', $id)
            ->whereHas('appointment', function ($query) {
                $query->where('doctor_id', \Session::get('user_id'));
            })
            ->first();

        if (!$report) {
            return response()->json(['success' => false, 'error' => 'Report not found or access denied.'], 404);
        }

        return response()->json(['success' => true, 'report' => $report]);
    }

    public function refineReportAnalysis(Request $request, AiAssistantService $aiAssistant)
    {
        if (!\Session::has("user_id") || \Session::get("role_id") != '2') {
            return response()->json(['error' => 'Unauthorized access.'], 401);
        }

        $request->validate([
            'report_id' => 'required|exists:ap_imd_uplod,id',
            'feedback' => 'required|string|max:1000',
        ]);

        $doctorId = \Session::get("user_id");
        $report = ApImdUplod::find($request->report_id);

        if ($report->appointment->doctor_id != $doctorId) {
            return response()->json(['error' => 'Access denied.'], 403);
        }

        $setting = \App\Models\Setting::first();
        $isAiFree = (bool)($setting->is_ai_free ?? 1);
        $activeSubscription = Subscriber::where('subscriber_id', $doctorId)->where('status', '2')->latest('id')->first();

        if (!$isAiFree && (!$activeSubscription || is_null($activeSubscription->remaining_ai_credits) || $activeSubscription->remaining_ai_credits <= 0)) {
            return response()->json(['error' => 'You have no AI credits remaining for refinement.'], 403);
        }

        DB::beginTransaction();
        try {
            if (!$isAiFree && $activeSubscription && ($activeSubscription->remaining_ai_credits ?? 0) > 0) {
                $activeSubscription->decrement('remaining_ai_credits');
            }

            $report->discussions()->create(['role' => 'doctor', 'content' => $request->feedback]);

            $history = $report->discussions()->get()->toArray();
            $refinedAnalysis = $aiAssistant->refineDocumentAnalysis($history, $request->feedback);

            $newAiMessage = $report->discussions()->create(['role' => 'ai', 'content' => json_encode($refinedAnalysis)]);

            DB::commit();

            return response()->json([
                'success' => true,
                'new_message' => $newAiMessage,
                'remaining_credits' => $activeSubscription->remaining_ai_credits ?? 999,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AI Report Refinement Error: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred during refinement.'], 500);
        }
    }

    public function finalizeReport(Request $request)
    {
        if (!\Session::has("user_id") || \Session::get("role_id") != '2') {
            return response()->json(['error' => 'Unauthorized access.'], 401);
        }

        $request->validate([
            'report_id' => 'required|exists:ap_imd_uplod,id',
            'final_notes' => 'required|string|max:5000',
        ]);

        $report = ApImdUplod::find($request->report_id);

        if ($report->appointment->doctor_id != \Session::get('user_id')) {
            return response()->json(['error' => 'Access denied.'], 403);
        }

        $report->doctor_notes = $request->final_notes;
        $report->save();

        return response()->json(['success' => true, 'message' => 'Report finalized successfully.']);
    }
}
