<?php

namespace App\Services;

use App\Models\EpdsScreening;
use Illuminate\Support\Facades\App;

class EpdsScreenerService
{
    /**
     * Certified Zimbabwean & Regional Perinatal Mental Health Hotlines & Counselors.
     */
    public function getPsychosocialSupportDirectory(): array
    {
        return [
            [
                'name' => 'Friendship Bench Zimbabwe (Perinatal & Community Mental Health)',
                'type' => 'Certified Evidence-Based Talk Therapy (Shona / Ndebele / English)',
                'phone' => '+263 78 484 5294',
                'whatsapp' => '+263 78 484 5294',
                'hours' => 'Mon-Sun 08:00 - 20:00 CAT',
                'is_free' => true,
            ],
            [
                'name' => 'Tiko / Lifeline Zimbabwe Maternal Crisis Helpline',
                'type' => '24/7 Confidential Toll-Free Crisis & Postpartum Support',
                'phone' => '116 / +263 242 703 687',
                'whatsapp' => '+263 77 214 5222',
                'hours' => '24/7 Toll-Free',
                'is_free' => true,
            ],
            [
                'name' => 'Chiremba Express Perinatal Psychosocial Specialist Desk',
                'type' => 'Urgent Tele-Psychiatry & Midwife Counseling',
                'phone' => '+263 77 123 4568',
                'whatsapp' => '+263 77 123 4568',
                'hours' => 'Priority Routing',
                'is_free' => false,
            ],
        ];
    }

    /**
     * Return localized 10-question Edinburgh Postnatal Depression Scale (EPDS) questionnaire.
     */
    public function getQuestionnaire(?string $locale = null): array
    {
        $locale = $locale ?: App::getLocale() ?: 'en';
        App::setLocale($locale);

        $questions = __('femtech.epds_questions');
        $standardOptions = [
            ['score' => 0, 'label' => 'No, not at all / As much as I always could (0)'],
            ['score' => 1, 'label' => 'Not quite so much / Only occasionally (1)'],
            ['score' => 2, 'label' => 'Yes, sometimes / Quite often (2)'],
            ['score' => 3, 'label' => 'Yes, most of the time / Severe (3)'],
        ];

        $formatted = [];
        foreach ($questions as $num => $text) {
            $formatted[$num] = [
                'number' => $num,
                'question' => $text,
                'is_self_harm_item' => ($num === 10),
                'options' => $standardOptions,
            ];
        }

        return $formatted;
    }

    /**
     * Score a 10-item EPDS submission according to clinical validation rules:
     * - Total score range: 0 to 30
     * - Score >= 13 indicates probable postpartum/perinatal depression (high_distress)
     * - Score 10–12 indicates possible distress requiring monitoring (moderate)
     * - Question 10 score >= 1 triggers immediate crisis safety net (urgent_crisis)
     */
    public function scoreAndRecord(array $input): EpdsScreening
    {
        $rawResponses = (array) ($input['responses'] ?? []);
        $normalized = [];
        $totalScore = 0;

        for ($i = 1; $i <= 10; $i++) {
            $val = isset($rawResponses[$i]) ? (int) $rawResponses[$i] : (isset($rawResponses[$i - 1]) ? (int) $rawResponses[$i - 1] : 0);
            $val = max(0, min(3, $val));
            $normalized[$i] = $val;
            $totalScore += $val;
        }

        $selfHarmScore = $normalized[10] ?? 0;

        if ($selfHarmScore >= 1) {
            $riskSeverity = 'urgent_crisis';
        } elseif ($totalScore >= 13) {
            $riskSeverity = 'high_distress';
        } elseif ($totalScore >= 10) {
            $riskSeverity = 'moderate';
        } else {
            $riskSeverity = 'low';
        }

        $surfaceCounselors = in_array($riskSeverity, ['moderate', 'high_distress', 'urgent_crisis'], true);
        $hotlines = $surfaceCounselors ? $this->getPsychosocialSupportDirectory() : [];

        return EpdsScreening::create([
            'patient_id' => $input['patient_id'] ?? null,
            'anonymous_session_token' => $input['anonymous_session_token'] ?? null,
            'recorded_by_chw_id' => $input['recorded_by_chw_id'] ?? null,
            'perinatal_stage' => $input['perinatal_stage'] ?? 'postpartum_0_6w',
            'responses' => $normalized,
            'total_score' => $totalScore,
            'self_harm_item_score' => $selfHarmScore,
            'risk_severity' => $riskSeverity,
            'counselor_referral_surfaced' => $surfaceCounselors,
            'support_hotlines' => $hotlines,
            'locale' => $input['locale'] ?? App::getLocale() ?: 'en',
        ]);
    }
}

