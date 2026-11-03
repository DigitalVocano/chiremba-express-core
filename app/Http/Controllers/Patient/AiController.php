<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;
use App\Models\Patient;
use App\Models\ChatSession;
use App\Models\Services;
use App\Models\Medicines;
use App\Models\Symptom;
use App\Services\DoctorRecommendationService;
use App\Services\PharmacyRecommendationService;
use PDF;
use Exception;
use Carbon\Carbon;

class AiController extends Controller
{
    public function showAiAssistant(Request $request)
    {
        if (!Session::has("user_id") || Session::get("role_id") != '1') {
            return redirect()->route('login');
        }

        $initialSymptom = $request->query('symptom');
        $setting = Setting::find(1);
        $patient = Patient::with('wallet')->find(Session::get("user_id"));
        $ai_credits = $patient->wallet->ai_credits ?? 0;

        $activeSession = ChatSession::with(['messages' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }])
            ->where('patient_id', $patient->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        $latestCompletedSession = ChatSession::with(['messages' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }])
            ->where('patient_id', $patient->id)
            ->where('status', 'completed')
            ->latest()
            ->first();

        $formattedFinalReply = null;
        $isChatCompleted = false;

        if ($activeSession) {
            $isChatCompleted = false;
        } elseif ($latestCompletedSession) {
            $isChatCompleted = true;
            $finalReportMessage = $latestCompletedSession->messages->first(fn ($message) => $message->is_final_report);

            if ($finalReportMessage) {
                $decodedContent = json_decode($finalReportMessage->content, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $formattedFinalReply = $this->formatAiAssessmentAsHtml($decodedContent);

                    $recommendationService = new DoctorRecommendationService();
                    $recommendedDoctors = $recommendationService->getRecommendedDoctors($decodedContent['recommended_specialty'] ?? null);
                    if ($recommendedDoctors->isNotEmpty()) {
                        $formattedFinalReply .= $this->formatDoctorRecommendationsAsHtml($recommendedDoctors);
                    }

                    $pharmacyService = new PharmacyRecommendationService();
                    $suggestedMedicines = $decodedContent['suggested_medicines'] ?? [];
                    $recommendedPharmacies = $pharmacyService->getRecommendedPharmacies($suggestedMedicines, 2);
                    if ($recommendedPharmacies->isNotEmpty()) {
                        $formattedFinalReply .= $this->formatPharmacyRecommendationsAsHtml($recommendedPharmacies);
                    }
                }
            }
            $activeSession = null;
        }

        return view("user.patient.ai_assistant", [
            'setting' => $setting,
            'userdata' => $patient,
            'ai_credits' => $ai_credits,
            'activeSession' => $activeSession,
            'formattedFinalReply' => $formattedFinalReply,
            'isChatCompleted' => $isChatCompleted,
            'initialSymptom' => $initialSymptom
        ]);
    }

    public function askAiAssistant(Request $request)
    {
        $patient = null;
        if (Session::has("user_id") && Session::get("role_id") == '1') {
            $patient = Patient::with('wallet')->find(Session::get("user_id"));
        } elseif ($request->has('user_id')) {
            $patient = Patient::with('wallet')->find($request->user_id);
        } else {
             return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $request->validate([
            'action' => 'required|in:start,continue',
            'history' => 'required|json',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'session_id' => 'nullable|exists:chat_sessions,id'
        ]);

        $session = null;
        $initialCredits = $patient->wallet->ai_credits ?? 0;

        if ($request->action === 'start') {
            $setting = DB::table('setting')->first();
            $freeLimit = (int)($setting->ai_free_daily_limit ?? 0);
            $isFree = false;
            
            if (!$patient->wallet || $patient->wallet->ai_credits <= 0) {
                if ($freeLimit > 0) {
                    $todaySessionsCount = ChatSession::where('patient_id', $patient->id)
                        ->whereDate('created_at', Carbon::today())
                        ->count();

                    if ($todaySessionsCount < $freeLimit) {
                        $isFree = true;
                        Log::info("Patient {$patient->id} has no AI credits. Falling back to free daily query (Used: {$todaySessionsCount}/{$freeLimit}).");
                    } else {
                        return response()->json(['error' => 'You have exhausted your ' . $freeLimit . ' free daily AI consultations and have 0 AI credits left. Please purchase credits to continue.'], 403);
                    }
                } else {
                    return response()->json(['error' => 'You have 0 AI credits remaining. Please purchase AI credits or top up your wallet to start a consultation.'], 403);
                }
            } else {
                DB::beginTransaction();
                try {
                    $patient->wallet->decrement('ai_credits');
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();
                    return response()->json(['error' => 'A server error occurred while processing your request.'], 500);
                }
            }
        }

        try {
            $state = 'gathering';
            $replyContent = '';

            if ($request->input('session_id')) {
                $session = ChatSession::where('id', $request->input('session_id'))
                    ->where('patient_id', $patient->id)
                    ->where('status', 'active')
                    ->first();
            }

            if (!$session && $request->action === 'start') {
                $session = ChatSession::create([
                    'patient_id' => $patient->id,
                    'status' => 'active',
                ]);
            } elseif (!$session) {
                return response()->json(['error' => 'No active chat session found. Please start a new conversation.'], 404);
            }

            $attachmentPath = null;
            if ($request->hasFile('image')) {
                $attachmentPath = $request->file('image')->store('chat_attachments/' . $session->id, 'public');
            }

            $userHistory = json_decode($request->input('history', '[]'), true);
            $lastUserMessageFromFrontend = end($userHistory);
            $userMessageContent = $lastUserMessageFromFrontend['content'] ?? '';

            if ($lastUserMessageFromFrontend) {
                $userMessageContentForDb = $userMessageContent;
                if ($attachmentPath) {
                    $userMessageContentForDb .= " (Image attached)";
                }
                $session->messages()->create([
                    'role' => 'user',
                    'content' => $userMessageContentForDb,
                    'attachment_path' => $attachmentPath
                ]);
            }

            $specialties = Services::where('is_active', '1')->where('recommendable', 1)->pluck('name')->toArray();
            $specialtiesListString = "- " . implode("\n- ", $specialties);
            $medicines = Medicines::pluck('name')->toArray();
            $medicinesListString = "- " . implode("\n- ", $medicines);

            $systemPromptTemplate = Config::get('ai_prompts.health_assistant_system_prompt');
            $finalSystemPrompt = str_replace(
                ['[SPECIALTIES_LIST]', '[MEDICINES_LIST]'],
                [$specialtiesListString, $medicinesListString],
                $systemPromptTemplate
            );

            $historyForApi = [['role' => 'system', 'content' => $finalSystemPrompt]];
            $session->load(['messages' => function($query) { $query->orderBy('created_at', 'asc'); }]);

            foreach ($session->messages as $dbMessage) {
                if ($dbMessage->role === 'user') {
                    $userContentParts = [];
                    if (!empty($dbMessage->content)) {
                        $cleanContent = str_replace(" (Image attached)", "", $dbMessage->content);
                        $userContentParts[] = ['type' => 'text', 'text' => $cleanContent];
                    }
                    if ($dbMessage->attachment_path && Storage::disk('public')->exists($dbMessage->attachment_path)) {
                        $base64Image = base64_encode(Storage::disk('public')->get($dbMessage->attachment_path));
                        $mimeType = Storage::disk('public')->mimeType($dbMessage->attachment_path);
                        $userContentParts[] = ['type' => 'image_url', 'image_url' => ['url' => "data:{$mimeType};base64,{$base64Image}"]];
                    }
                    $historyForApi[] = ['role' => 'user', 'content' => $userContentParts];
                } elseif ($dbMessage->role === 'assistant') {
                    $historyForApi[] = ['role' => 'assistant', 'content' => $dbMessage->content];
                }
            }

            $aiResponse = $this->callOpenAI($historyForApi);
            $jsonResponse = null;
            $aiResponseForDb = $aiResponse;
            $replyContent = html_entity_decode(nl2br($aiResponse), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $state = 'gathering';


            $firstBrace = strpos($aiResponse, '{');
            $lastBrace = strrpos($aiResponse, '}');

            if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
                $potentialJson = substr($aiResponse, $firstBrace, $lastBrace - $firstBrace + 1);
                 try {
                    $decoded = json_decode($potentialJson, true, 512, JSON_THROW_ON_ERROR);
                    if (isset($decoded['recommended_specialty'])) {
                        $jsonResponse = $decoded;
                        $aiResponseForDb = $potentialJson;
                        $state = 'finished';
                    }
                } catch (\JsonException $e) {}
            }

            $doctorsData = [];
            $pharmaciesData = [];

            if ($state === 'finished') {
                $session->status = 'completed';
                $session->save();
                $replyContent = $this->formatAiAssessmentAsHtml($jsonResponse);
                $recommendationService = new DoctorRecommendationService();
                $recommendedDoctors = $recommendationService->getRecommendedDoctors($jsonResponse['recommended_specialty'] ?? null);
                if ($recommendedDoctors->isNotEmpty()) {
                    $replyContent .= $this->formatDoctorRecommendationsAsHtml($recommendedDoctors);
                    foreach ($recommendedDoctors as $doc) {
                        $doctorsData[] = [
                            'id' => $doc->id,
                            'name' => $doc->name,
                            'department_name' => $doc->department_name ?? 'General Specialist',
                            'image_url' => asset('upload/doctors/' . ($doc->image ?? 'default.png')),
                            'rating' => $doc->overall_rating ?? '4.8',
                            'address' => $doc->address ?? 'Harare, Zimbabwe',
                            'consultation_fee' => $doc->consultation_fee ?? $doc->fee ?? '20',
                            'phone' => $doc->phone ?? $doc->phoneno ?? '',
                            'slug' => $doc->slug ?? '',
                        ];
                    }
                }
                $pharmacyService = new PharmacyRecommendationService();
                $suggestedMedicines = $jsonResponse['suggested_medicines'] ?? [];
                $recommendedPharmacies = $pharmacyService->getRecommendedPharmacies($suggestedMedicines, 2);
                if ($recommendedPharmacies->isNotEmpty()) {
                    $replyContent .= $this->formatPharmacyRecommendationsAsHtml($recommendedPharmacies);
                    foreach ($recommendedPharmacies as $ph) {
                        $pharmaciesData[] = [
                            'id' => $ph->id,
                            'name' => $ph->name,
                            'address' => $ph->address ?? 'Harare, Zimbabwe',
                            'image_url' => asset('upload/pharmacies/' . ($ph->image ?? 'defaultpharmacy.png')),
                            'phone' => $ph->phone ?? $ph->phoneno ?? '',
                            'slug' => $ph->slug ?? '',
                            'medicines' => $suggestedMedicines,
                        ];
                    }
                }
            }

            $session->messages()->create([
                'role' => 'assistant',
                'content' => $aiResponseForDb,
                'is_final_report' => ($state === 'finished')
            ]);

            $creditsLeft = $patient->wallet ? ($patient->wallet->fresh()->ai_credits ?? 0) : 0;

            return response()->json([
                'reply' => $replyContent,
                'raw_reply' => $aiResponse,
                'state' => $state,
                'credits_left' => $creditsLeft,
                'session_id' => $session->id,
                'doctors' => $doctorsData,
                'pharmacies' => $pharmaciesData,
                'assessment_data' => $jsonResponse,
            ]);
        } catch (\Exception $e) {
            Log::error("AI Service Call Failed: " . $e->getMessage());
            if ($request->action === 'start' && $patient->wallet && ($patient->wallet->fresh()->ai_credits < $initialCredits)) {
                $patient->wallet->increment('ai_credits');
            }
            return response()->json(['error' => 'The AI service is currently unavailable. Please try again later.'], 503);
        }
    }

    public function guestAiChat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'history' => 'nullable|array'
        ]);

        try {
            $userMessage = $request->input('message');
            $history = $request->input('history', []);

            // Limit history for guests to prevent abuse
            if (count($history) > 6) {
                 return response()->json(['error' => 'Guest chat limit reached. Please login.'], 403);
            }

            $messages = [
                ['role' => 'system', 'content' => "You are Dr. Anaya, a warm, highly experienced attending medical doctor conducting a virtual health consultation at Chiremba Express. Evaluate the patient's symptoms, ask relevant follow-up questions, and provide a clear, empathetic preliminary medical assessment, specialist recommendation, and over-the-counter care advice. Never output robotic AI disclaimers such as 'As an AI...'. Maintain a professional, caring doctor persona at all times."],
            ];

            foreach ($history as $msg) {
                if (isset($msg['role']) && isset($msg['content'])) {
                    $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
                }
            }

            $messages[] = ['role' => 'user', 'content' => $userMessage];

            $aiResponse = $this->callOpenAI($messages);

            return response()->json(['reply' => html_entity_decode(nl2br($aiResponse), ENT_QUOTES | ENT_HTML5, 'UTF-8'), 'raw_reply' => $aiResponse]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Service temporarily unavailable.'], 503);
        }
    }

    public function showPublicAiAssistant()
    {
        $setting = Setting::find(1);
        return view('user.ai_assistant_public', compact('setting'));
    }

    public function checkSymptoms(Request $request)
    {
        $request->validate([
            'symptoms' => 'required|array',
            'symptoms.*' => 'string|max:255',
        ]);

        $symptoms = $request->input('symptoms');
        $prompt = "I have the following symptoms: " . implode(', ', $symptoms) . ". Can you help me identify potential health conditions? Provide the response as a clear, easy-to-read list of conditions and a brief explanation for each.";

        $messages = [
            ['role' => 'system', 'content' => 'You are Anaya, a helpful AI health assistant. Provide a list of potential health conditions based on the symptoms provided. Do not provide a definitive diagnosis. Always include a disclaimer that this is not a substitute for professional medical advice.'],
            ['role' => 'user', 'content' => $prompt],
        ];

        $possibleDiagnoses = $this->callOpenAI($messages);
        $symptomDetails = Symptom::whereIn('name', $symptoms)->get();

        return view('user.patient.symptom-check-results', [
            'possibleDiagnoses' => $possibleDiagnoses,
            'symptomDetails' => $symptomDetails,
        ]);
    }

    public function getHistory(Request $request)
    {
        $patient = null;
        if (Session::has("user_id")) {
            $patient = Patient::find(Session::get("user_id"));
        } elseif ($request->has('user_id')) {
            $patient = Patient::find($request->user_id);
        }

        if (!$patient) {
            return response()->json(['success' => 0, 'message' => 'User not found'], 401);
        }

        $session = ChatSession::with(['messages' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }])->where('patient_id', $patient->id)->latest()->first();

        return response()->json([
            'success' => 1,
            'session' => $session
        ]);
    }

    public function showSymptomChecker()
    {
        if (!Session::has("user_id") || Session::get("role_id") != '1') {
            return redirect()->route('login');
        }
        $setting = Setting::find(1);
        $userdata = Patient::with('wallet')->find(Session::get("user_id"));
        return view('user.patient.symptom_checker', compact('setting', 'userdata'));
    }

    public function downloadAiAssistantReport(Request $request)
    {
        $request->validate(['session_id' => 'required|exists:chat_sessions,id']);
        
        $patient = null;
        if (Session::has("user_id")) {
            $patient = Patient::find(Session::get("user_id"));
        } elseif ($request->has('user_id')) {
            $patient = Patient::find($request->user_id);
        }

        if (!$patient) {
             return response()->json(['success' => 0, 'message' => 'User not found'], 401);
        }

        $session = ChatSession::with('messages')->where('id', $request->session_id)->where('patient_id', $patient->id)->first();

        if (!$session) {
            if ($request->wantsJson()) {
                return response()->json(['success' => 0, 'message' => 'Chat session not found'], 404);
            }
            return back()->with('error', 'Chat session not found');
        }

        // Extract assessment report JSON data
        $assessmentData = null;
        $finalMessage = $session->messages->firstWhere('is_final_report', true);
        if ($finalMessage) {
            $decoded = json_decode($finalMessage->content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $assessmentData = $decoded;
            }
        }

        $recommendedDoctors = [];
        $recommendedPharmacies = [];

        if ($assessmentData && isset($assessmentData['recommended_specialty'])) {
            $recommendationService = new DoctorRecommendationService();
            $recommendedDoctors = $recommendationService->getRecommendedDoctors($assessmentData['recommended_specialty']);
            
            $pharmacyService = new PharmacyRecommendationService();
            $suggestedMedicines = $assessmentData['suggested_medicines'] ?? [];
            $recommendedPharmacies = $pharmacyService->getRecommendedPharmacies($suggestedMedicines, 2);
        }

        $pdf = PDF::loadView('pdf.health_report', [
            'session' => $session,
            'patient' => $patient,
            'assessmentData' => $assessmentData,
            'recommendedDoctors' => $recommendedDoctors,
            'recommendedPharmacies' => $recommendedPharmacies,
        ]);
        
        if ($request->wantsJson()) {
             $fileName = 'report_' . $session->id . '.pdf';
             $path = 'reports/' . $fileName;
             Storage::disk('public')->put($path, $pdf->output());
             return response()->json([
                 'success' => 1, 
                 'url' => asset('storage/' . $path)
             ]);
        }

        return $pdf->download('Health_Report_' . $session->id . '.pdf');
    }

    private function callOpenAI(array $messages): string
    {
        // Check if any message contains image data
        $hasImage = false;
        foreach ($messages as $msg) {
            if (isset($msg['content']) && is_array($msg['content'])) {
                foreach ($msg['content'] as $part) {
                    if (isset($part['type']) && $part['type'] === 'image_url') {
                        $hasImage = true;
                        break 2;
                    }
                }
            }
        }

        // --- PRIORITY 1: Famo API (Sovereign Local Model) ---
        if (!$hasImage) {
            try {
                $famoToken = env('FAMO_API_TOKEN');
                if ($famoToken && $famoToken !== 'famo_token_placeholder') {
                    Log::info("AI Engine: Attempting Famo API (famo.co.zw)");
                    
                    // Flatten messages for Famo compatibility
                    $famoMessages = [];
                    foreach ($messages as $msg) {
                        $content = '';
                        if (is_array($msg['content'])) {
                            foreach ($msg['content'] as $part) {
                                if (isset($part['type']) && $part['type'] === 'text') {
                                    $content .= $part['text'] . "\n";
                                }
                            }
                        } else {
                            $content = $msg['content'];
                        }
                        $famoMessages[] = ['role' => $msg['role'], 'content' => trim($content)];
                    }

                    $famoEndpoint = env('FAMO_API_ENDPOINT', 'https://famo.co.zw/api/v1/chat');
                    $famoModel = env('FAMO_MODEL', 'llama3');

                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $famoToken,
                        'Content-Type' => 'application/json',
                    ])->timeout(25)->post($famoEndpoint, [
                        'model' => $famoModel,
                        'messages' => $famoMessages,
                    ]);

                    if ($response->successful() && isset($response->json()['response'])) {
                        Log::info("AI Engine: Famo API succeeded.");
                        return $response->json()['response'];
                    }
                    Log::warning("AI Engine: Famo API returned status " . $response->status() . " or missing response key.");
                }
            } catch (\Exception $e) {
                Log::warning("AI Engine: Famo API exception: " . $e->getMessage());
            }

            // --- OPTIONAL: Local Ollama Fallback ---
            try {
                $ollamaHost = env('OLLAMA_HOST');
                if ($ollamaHost) {
                    Log::info("AI Engine: Attempting Local Ollama");
                    $ollamaMessages = [];
                    foreach ($messages as $msg) {
                        $content = '';
                        if (is_array($msg['content'])) {
                            foreach ($msg['content'] as $part) {
                                if (isset($part['type']) && $part['type'] === 'text') {
                                    $content .= $part['text'] . "\n";
                                }
                            }
                        } else {
                            $content = $msg['content'];
                        }
                        $ollamaMessages[] = ['role' => $msg['role'], 'content' => trim($content)];
                    }

                    $ollamaModel = env('OLLAMA_MODEL', 'llama3');
                    $response = Http::timeout(10)->post("{$ollamaHost}/api/chat", [
                        'model' => $ollamaModel,
                        'messages' => $ollamaMessages,
                        'stream' => false,
                    ]);

                    if ($response->successful()) {
                        Log::info("AI Engine: Local Ollama succeeded.");
                        return $response->json()['message']['content'] ?? '';
                    }
                }
            } catch (\Exception $e) {
                Log::warning("AI Engine: Local Ollama failed: " . $e->getMessage());
            }
        }

        $openRouterKey = env('OPENROUTER_API_KEY') ?: env('OPENAI_API_KEY');
        if ($openRouterKey) {
            // --- PRIORITY 2: OpenRouter Paid / High Quality Unrestricted Models ---
            $paidModels = array_filter(array_map('trim', explode(',', env('OPENROUTER_PAID_MODELS', 'meta-llama/llama-3.3-70b-instruct,deepseek/deepseek-chat,qwen/qwen-2.5-72b-instruct,mistralai/mistral-small-24b-instruct-2501'))));
            
            foreach ($paidModels as $currentModel) {
                try {
                    Log::info("AI Engine: Attempting OpenRouter Model ({$currentModel})");
                    
                    // Format messages for OpenRouter (supporting both text and vision)
                    $formattedMessages = [];
                    foreach ($messages as $msg) {
                        if (is_array($msg['content'])) {
                            $contentParts = [];
                            foreach ($msg['content'] as $part) {
                                if ($part['type'] === 'text') {
                                    $contentParts[] = ['type' => 'text', 'text' => $part['text']];
                                } elseif ($part['type'] === 'image_url') {
                                    $contentParts[] = [
                                        'type' => 'image_url',
                                        'image_url' => ['url' => $part['image_url']['url']]
                                    ];
                                }
                            }
                            $formattedMessages[] = ['role' => $msg['role'], 'content' => $contentParts];
                        } else {
                            $formattedMessages[] = ['role' => $msg['role'], 'content' => $msg['content']];
                        }
                    }

                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $openRouterKey,
                        'Content-Type' => 'application/json',
                        'HTTP-Referer' => url('/'),
                        'X-Title' => 'Chiremba Express',
                    ])->timeout(30)->post('https://openrouter.ai/api/v1/chat/completions', [
                        'model' => $currentModel,
                        'messages' => $formattedMessages,
                        'max_tokens' => 1500,
                    ]);

                    if ($response->successful()) {
                        $resData = $response->json();
                        if (isset($resData['choices'][0]['message']['content'])) {
                            Log::info("AI Engine: OpenRouter Model ({$currentModel}) succeeded.");
                            return $resData['choices'][0]['message']['content'];
                        }
                    }
                    Log::warning("AI Engine: OpenRouter Model ({$currentModel}) returned status " . $response->status() . " or missing content. Body: " . $response->body());
                } catch (\Exception $e) {
                    Log::warning("AI Engine: OpenRouter Model ({$currentModel}) failed: " . $e->getMessage());
                }
            }

            // --- PRIORITY 3: OpenRouter Free Models ---
            $freeModels = array_filter(array_map('trim', explode(',', env('OPENROUTER_FREE_MODELS', 'meta-llama/llama-3.1-8b-instruct:free,mistralai/mistral-7b-instruct:free,qwen/qwen-2.5-7b-instruct:free'))));
            
            foreach ($freeModels as $currentModel) {
                try {
                    Log::info("AI Engine: Attempting OpenRouter Free Model ({$currentModel})");
                    
                    // Format messages (Free models usually don't support vision, so flatten if needed, or send as is)
                    $formattedMessages = [];
                    foreach ($messages as $msg) {
                        $content = '';
                        if (is_array($msg['content'])) {
                            foreach ($msg['content'] as $part) {
                                if (isset($part['type']) && $part['type'] === 'text') {
                                    $content .= $part['text'] . "\n";
                                }
                            }
                        } else {
                            $content = $msg['content'];
                        }
                        $formattedMessages[] = ['role' => $msg['role'], 'content' => trim($content)];
                    }

                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $openRouterKey,
                        'Content-Type' => 'application/json',
                        'HTTP-Referer' => url('/'),
                        'X-Title' => 'Chiremba Express',
                    ])->timeout(20)->post('https://openrouter.ai/api/v1/chat/completions', [
                        'model' => $currentModel,
                        'messages' => $formattedMessages,
                        'max_tokens' => 1500,
                    ]);

                    if ($response->successful()) {
                        $resData = $response->json();
                        if (isset($resData['choices'][0]['message']['content'])) {
                            Log::info("AI Engine: OpenRouter Free Model ({$currentModel}) succeeded.");
                            return $resData['choices'][0]['message']['content'];
                        }
                    }
                    Log::warning("AI Engine: OpenRouter Free Model ({$currentModel}) returned status " . $response->status() . " or missing content.");
                } catch (\Exception $e) {
                    Log::warning("AI Engine: OpenRouter Free Model ({$currentModel}) failed: " . $e->getMessage());
                }
            }
        }

        throw new Exception('All AI models (Famo, OpenRouter Paid, OpenRouter Free) failed to return a response.');
    }

    private function formatAiAssessmentAsHtml(array $data): string
    {
        $html = '<div class="space-y-4"><div class="p-4 border border-blue-200 rounded-lg bg-blue-50">';
        $html .= '<h3 class="text-lg font-bold text-blue-800 mb-2">AI Health Assessment</h3>';
        if (isset($data['assessment'])) $html .= '<p class="text-sm text-gray-700 mb-3">' . nl2br(e($data['assessment'])) . '</p>';
        if (isset($data['recommendation'])) $html .= '<h4 class="font-semibold text-gray-800 mb-1">Recommendation</h4><p class="text-sm text-gray-600 mb-3">' . nl2br(e($data['recommendation'])) . '</p>';
        if (isset($data['reasoning'])) $html .= '<h4 class="font-semibold text-gray-800 mb-1">Reasoning</h4><p class="text-sm text-gray-600 mb-3">' . nl2br(e($data['reasoning'])) . '</p>';
        if (isset($data['disclaimer'])) $html .= '<p class="text-xs text-red-600 italic mt-4">' . nl2br(e($data['disclaimer'])) . '</p>';
        $html .= '</div></div>';
        return $html;
    }

    private function formatDoctorRecommendationsAsHtml($doctors): string
    {
        if ($doctors->isEmpty()) return '';
        $html = '<div class="mt-4 p-4 border border-green-200 rounded-lg bg-green-50"><h3 class="text-lg font-bold text-green-800 mb-3">Recommended Specialists</h3><div class="space-y-3">';
        foreach ($doctors as $doctor) {
            $profileUrl = route('viewdoctor', ['slug' => $doctor->slug]);
            $imageUrl = asset('upload/doctors/' . ($doctor->image ?? 'default.png'));
            $html .= '<a href="' . e($profileUrl) . '" class="flex items-center gap-4 p-3 bg-white rounded-lg shadow-sm">';
            $html .= '<img src="' . e($imageUrl) . '" class="w-16 h-16 rounded-full object-cover">';
            $html .= '<div><p class="font-bold text-gray-900">' . e($doctor->name) . '</p><p class="text-sm text-gray-600">' . e($doctor->address) . '</p></div></a>';
        }
        $html .= '</div></div>';
        return $html;
    }

    private function formatPharmacyRecommendationsAsHtml($pharmacies): string
    {
        if ($pharmacies->isEmpty()) return '';
        $html = '<div class="mt-4 p-4 border border-purple-200 rounded-lg bg-purple-50"><h3 class="text-lg font-bold text-purple-800 mb-3">Recommended Pharmacies</h3><div class="space-y-3">';
        foreach ($pharmacies as $pharmacy) {
            $profileUrl = route('viewpharmacy', ['slug' => $pharmacy->slug]);
            $imageUrl = asset('upload/pharmacies/' . ($pharmacy->image ?? 'defaultpharmacy.png'));
            $html .= '<a href="' . e($profileUrl) . '" class="flex items-center gap-4 p-3 bg-white rounded-lg shadow-sm">';
            $html .= '<img src="' . e($imageUrl) . '" class="w-16 h-16 rounded-full object-cover">';
            $html .= '<div><p class="font-bold text-gray-900">' . e($pharmacy->name) . '</p><p class="text-sm text-gray-600">' . e($pharmacy->address) . '</p></div></a>';
        }
        $html .= '</div></div>';
        return $html;
    }
}