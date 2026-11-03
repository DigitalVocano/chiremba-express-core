<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\PdfToText\Pdf;

class AiAssistantService
{
    /**
     * Get prescription suggestions from an AI model based on patient symptoms.
     *
     * @param string $symptoms The patient's symptoms or problem description.
     * @return array An array containing the AI's suggestions.
     */
    public function getPrescriptionSuggestion(string $symptoms): array
    {
        $systemPrompt = 'You are a helpful medical assistant. Based on the symptoms provided, suggest potential diagnoses and common prescriptions. Format the output as a simple JSON object with three keys: "diagnoses" (an array of strings), "prescriptions" (an array of strings), and "notes" (a string).';

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $symptoms],
        ];

        return $this->callOpenAI($messages);
    }

    /**
     * Get AI-powered analysis from a document (image/pdf).
     *
     * @param UploadedFile $file The uploaded file.
     * @return array An array containing the AI's analysis.
     */
    public function analyzeDocumentReport(UploadedFile $file): array
    {
        $systemPrompt = 'You are an expert medical diagnostician. Your task is to analyze the provided medical document. Focus solely on the clinical data presented. Do not identify, name, or comment on any individuals. Ignore all personally identifiable information (PII). Provide a detailed analysis in a JSON object with the following structure: {"diagnosis": "string", "findings": [{"finding": "string", "reasoning": "string"}], "summary": "string"}. The diagnosis should be your primary conclusion. The findings array should detail specific observations and the reasoning behind them. The summary should be a concise conclusion for the doctor.';

        try {
            $mimeType = $file->getMimeType();
            $messages = [['role' => 'system', 'content' => $systemPrompt]];

            if (str_starts_with($mimeType, 'image/')) {
                // Handle image files
                $base64Image = base64_encode($file->get());
                $messages[] = [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => 'Please analyze this medical image.'],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:{$mimeType};base64,{$base64Image}"
                            ]
                        ]
                    ]
                ];
            } elseif ($mimeType === 'application/pdf') {
                // Handle PDF files by extracting text
                try {
                    $text = (new Pdf())
                        ->setPdf($file->getPathname())
                        ->text();

                    if (empty(trim($text))) {
                        return ['error' => 'The uploaded PDF contains no readable text. It might be an image-based PDF.'];
                    }

                    $messages[] = [
                        'role' => 'user',
                        'content' => 'Please analyze the text from this medical PDF report: ' . $text,
                    ];
                } catch (\Exception $e) {
                    Log::error('PDF to text conversion failed: ' . $e->getMessage());
                    return ['error' => 'Failed to process the PDF file.'];
                }
            } else {
                Log::warning('Attempted to analyze an unsupported file type.', ['mime_type' => $mimeType]);
                return ['error' => 'Unsupported file type. Please upload an image (JPG, PNG) or a text-based PDF.'];
            }

        } catch (\Exception $e) {
            Log::error('Failed to process uploaded file for AI analysis: ' . $e->getMessage());
            return ['error' => 'Could not read the uploaded file.'];
        }

        return $this->callOpenAI($messages);
    }

    /**
     * Get a refined AI analysis based on discussion history and new feedback.
     *
     * @param array $history The existing conversation history.
     * @param string $feedback The doctor's new feedback or question.
     * @return array An array containing the AI's refined analysis.
     */
    public function refineDocumentAnalysis(array $history, string $feedback): array
    {
        $systemPrompt = 'You are an expert radiologist and medical diagnostician. You previously provided an analysis. Now, a doctor is providing feedback. Refine your original analysis based on this new information. Maintain the original JSON structure: {"diagnosis": "string", "findings": [{"finding": "string", "reasoning": "string"}], "summary": "string"}.';

        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        // Reconstruct the conversation history for the AI
        foreach ($history as $message) {
            $role = ($message['role'] == 'ai') ? 'assistant' : 'user';
            $content = ($role == 'assistant' && json_decode($message['content'])) ? $message['content'] : $message['content'];
            $messages[] = ['role' => $role, 'content' => $content];
        }

        // Add the latest feedback from the doctor
        $messages[] = ['role' => 'user', 'content' => $feedback];

        return $this->callOpenAI($messages);
    }

    /**
     * Calls the OpenAI API to get a response.
     *
     * @param array $messages The conversation history.
     * @return array The decoded JSON response from the AI or an error array.
     */
    private function callOpenAI(array $messages): array
    {
        // --- FAMO AI PRIORITY WITH FALLBACK ---
        $famoApiKey = env('FAMO_CHAT_API_KEY') ?: env('FAMO_API_KEY');
        $famoModel = env('FAMO_CHAT_MODEL', 'llama3');

        if (!empty($famoApiKey)) {
            try {
                $promptText = "";
                $famoMessages = [];
                foreach ($messages as $msg) {
                    $role = $msg['role'] ?? 'user';
                    $content = $msg['content'] ?? '';
                    if (is_array($content)) {
                        $textChunks = [];
                        foreach ($content as $chunk) {
                            if (isset($chunk['text'])) {
                                $textChunks[] = $chunk['text'];
                            }
                        }
                        $content = implode(" ", $textChunks);
                    }
                    $promptText .= "{$role}: {$content}\n";
                    $famoMessages[] = [
                        'role' => $role,
                        'content' => $content
                    ];
                }

                $famoResponse = Http::timeout(60)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $famoApiKey,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])
                    ->post('https://famo.co.zw/api/v1/chat', [
                        'model' => $famoModel,
                        'prompt' => $promptText,
                        'messages' => $famoMessages
                    ]);

                if ($famoResponse->successful() && isset($famoResponse->json()['response'])) {
                    $responseText = $famoResponse->json()['response'];
                    $responseText = preg_replace('/<think>.*?<\/think>/s', '', $responseText);
                    $responseText = preg_replace('/```json\s*/i', '', $responseText);
                    $responseText = preg_replace('/```\s*/', '', $responseText);
                    $responseText = trim($responseText);
                    $json = json_decode($responseText, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return $json;
                    }
                }
            } catch (\Throwable $famoEx) {
                Log::warning("Famo AI call failed in chiremba. Falling back. Error: " . $famoEx->getMessage());
            }
        }
        // --- END OF FAMO AI PRIORITY ---

        // --- PRIORITY 2: OpenRouter Global API (Geographically Unrestricted) ---
        $openRouterKey = env('OPENROUTER_API_KEY') ?: env('OPENAI_API_KEY');
        if (!empty($openRouterKey)) {
            $models = ['meta-llama/llama-3.3-70b-instruct', 'deepseek/deepseek-chat', 'qwen/qwen-2.5-72b-instruct', 'mistralai/mistral-small-24b-instruct-2501'];
            
            foreach ($models as $currentModel) {
                try {
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $openRouterKey,
                        'Content-Type' => 'application/json',
                        'HTTP-Referer' => url('/'),
                        'X-Title' => 'Chiremba Express',
                    ])->timeout(35)->post('https://openrouter.ai/api/v1/chat/completions', [
                        'model' => $currentModel,
                        'messages' => $messages,
                        'max_tokens' => 2048,
                    ]);

                    if ($response->successful()) {
                        $resData = $response->json();
                        if (isset($resData['choices'][0]['message']['content'])) {
                            $content = $resData['choices'][0]['message']['content'];
                            $content = preg_replace('/<think>.*?<\/think>/s', '', $content);
                            $content = preg_replace('/```json\s*/i', '', $content);
                            $content = preg_replace('/```\s*/', '', $content);
                            $content = trim($content);
                            $decoded = json_decode($content, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                return $decoded;
                            }
                            // If response is plain text, return wrapped object
                            return ['raw_text' => $content, 'notes' => $content];
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning("OpenRouter model {$currentModel} failed: " . $e->getMessage());
                }
            }
        }

        return ['error' => 'AI service is currently offline or unreachable. Please try again.'];
    }

    /**
     * Generate an SEO-friendly, professional profile description for a doctor.
     *
     * @param array $data
     * @return array
     */
    public function generateDoctorBio(array $data): array
    {
        $name = trim($data['name'] ?? 'Healthcare Specialist');
        $specialty = trim($data['specialty'] ?? 'General Medicine');
        $address = trim($data['address'] ?? 'Zimbabwe');
        $workingTime = trim($data['working_time'] ?? 'Regular clinic hours');
        $existingAbout = trim($data['existing_about'] ?? '');
        $existingServices = trim($data['existing_services'] ?? '');
        $existingHealthcare = trim($data['existing_healthcare'] ?? '');

        $systemPrompt = 'You are an expert medical copywriter and healthcare SEO specialist at Chiremba Express. Your goal is to write rich, trustworthy, engaging, empathetic, and SEO-optimized profiles for doctors and healthcare specialists in Zimbabwe.
Given the doctor details, output a clean JSON object with EXACTLY the following keys:
- "aboutus": A well-crafted, professional 2-3 paragraph biography highlighting their clinical dedication, patient-centric approach, compassionate care, and expertise in their specialty. Include relevant healthcare and location keywords naturally.
- "services": A structured, clear paragraph or bullet points detailing the key medical consultations, diagnostic procedures, treatments, and specialized care they offer.
- "healthcare": A concise summary of conditions managed, preventive health checkups, and wellness advice.
- "seo_keywords": A comma-separated string of 5-8 high-intent search keywords (e.g., "Cardiologist Harare, heart specialist Zimbabwe, medical consultation").

Format: Output pure JSON only without markdown code fences.';

        $userPrompt = "Doctor Name: {$name}\nSpecialty / Department: {$specialty}\nClinic Location: {$address}\nWorking Hours: {$workingTime}\n"
            . ($existingAbout ? "Current Notes / Draft About: {$existingAbout}\n" : "")
            . ($existingServices ? "Current Services Notes: {$existingServices}\n" : "")
            . ($existingHealthcare ? "Current Healthcare Notes: {$existingHealthcare}\n" : "");

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];

        $aiResult = $this->callOpenAI($messages);

        if (is_array($aiResult) && !empty($aiResult['aboutus'])) {
            return [
                'success' => true,
                'aboutus' => trim($aiResult['aboutus']),
                'services' => trim($aiResult['services'] ?? "Comprehensive {$specialty} consultations, clinical assessments, diagnostics, and patient treatment plans."),
                'healthcare' => trim($aiResult['healthcare'] ?? "Preventative health screenings, chronic disease management, and specialized medical guidance."),
                'seo_keywords' => trim($aiResult['seo_keywords'] ?? "{$specialty} doctor, medical specialist Zimbabwe, healthcare consultation"),
                'source' => 'ai',
            ];
        }

        // Intelligent local fallback template if AI is offline
        $fallbackAbout = "{$name} is a dedicated and highly experienced {$specialty} specialist practicing in {$address}. With a steadfast commitment to patient-centered care, {$name} combines clinical expertise with compassionate communication to deliver comprehensive medical assessments, accurate diagnoses, and individualized treatment pathways for every patient.\n\nWhether you are seeking routine consultations, specialized preventive care, or ongoing management of medical conditions, {$name} ensures a welcoming, modern clinical environment designed to prioritize your long-term health and wellbeing.";
        
        $fallbackServices = "• Comprehensive {$specialty} Consultations & Clinical Diagnostics\n• Personalized Patient Treatment Plans & Follow-ups\n• Preventive Health Screenings & Wellness Assessments\n• Collaborative In-Person and Telemedicine Support";
        
        $fallbackHealthcare = "Specialized management of conditions within {$specialty}, routine checkups, early detection protocols, lifestyle and wellness counseling, and chronic care support tailored to patient needs.";

        return [
            'success' => true,
            'aboutus' => $fallbackAbout,
            'services' => $fallbackServices,
            'healthcare' => $fallbackHealthcare,
            'seo_keywords' => "{$specialty} doctor, {$name}, medical specialist {$address}, health consultation Zimbabwe",
            'source' => 'template',
        ];
    }

    /**
     * Generate an SEO-friendly, trustworthy description and services for a pharmacy.
     *
     * @param array $data
     * @return array
     */
    public function generatePharmacyBio(array $data): array
    {
        $name = trim($data['name'] ?? 'Pharmacy');
        $address = trim($data['address'] ?? 'Zimbabwe');
        $workingTime = trim($data['working_time'] ?? 'Monday to Saturday');
        $existingAbout = trim($data['existing_about'] ?? '');
        $existingServices = trim($data['existing_services'] ?? '');

        $systemPrompt = 'You are an expert pharmaceutical marketing copywriter and local SEO specialist at Chiremba Express. Your goal is to write trustworthy, community-oriented, engaging, and SEO-optimized profiles for retail pharmacies and dispensaries in Zimbabwe.
Given the pharmacy details, output a clean JSON object with EXACTLY the following keys:
- "aboutus": A professional, welcoming 2-paragraph overview describing the pharmacy\'s dedication to authentic medication dispensing, expert pharmacist counseling, prompt fulfillment, and reliable community healthcare support.
- "services": A structured description or bullet points covering prescription dispensing, over-the-counter wellness products, chronic medication management, vitals monitoring (BP/Blood Glucose checks), and health consultations.
- "seo_keywords": A comma-separated string of 5-8 local search keywords (e.g., "Pharmacy Harare, buy prescription medicine Zimbabwe, authentic drugs dispensary").

Format: Output pure JSON only without markdown code fences.';

        $userPrompt = "Pharmacy Name: {$name}\nLocation / Address: {$address}\nWorking Hours: {$workingTime}\n"
            . ($existingAbout ? "Current Notes / Draft About: {$existingAbout}\n" : "")
            . ($existingServices ? "Current Services Notes: {$existingServices}\n" : "");

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];

        $aiResult = $this->callOpenAI($messages);

        if (is_array($aiResult) && !empty($aiResult['aboutus'])) {
            return [
                'success' => true,
                'aboutus' => trim($aiResult['aboutus']),
                'services' => trim($aiResult['services'] ?? "Prescription medication dispensing, over-the-counter pharmaceuticals, chronic disease medication refills, vitals screening, and professional pharmacist consultations."),
                'seo_keywords' => trim($aiResult['seo_keywords'] ?? "pharmacy, buy medicine Zimbabwe, prescription dispensary"),
                'source' => 'ai',
            ];
        }

        // Intelligent local fallback template
        $fallbackAbout = "{$name} is a premier community pharmacy and healthcare dispensary located at {$address}. Committed to upholding the highest standards of pharmaceutical care, {$name} provides quick, reliable access to genuine prescription medications, quality over-the-counter health products, and personalized wellness guidance.\n\nOur certified pharmacy team is dedicated to safeguarding your health through attentive medication reviews, dosage counseling, and accessible wellness services designed to support you and your family every step of the way.";
        
        $fallbackServices = "• Fast & Accurate Prescription Dispensing & Refill Management\n• Over-the-Counter (OTC) Health, First-Aid, and Wellness Products\n• Chronic Disease Medication Support (Hypertension, Diabetes, Asthma)\n• Complimentary Vitals Checks (Blood Pressure & Blood Glucose Monitoring)\n• Professional Pharmacist Consultation and Medication Guidance";

        return [
            'success' => true,
            'aboutus' => $fallbackAbout,
            'services' => $fallbackServices,
            'seo_keywords' => "pharmacy {$address}, prescription dispensing, buy medicine {$address}, {$name} Zimbabwe",
            'source' => 'template',
        ];
    }
}
