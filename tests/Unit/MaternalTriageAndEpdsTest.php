<?php

namespace Tests\Unit;

use App\Services\EpdsScreenerService;
use App\Services\MaternalTriageService;
use App\Services\SrhrGuidanceService;
use Tests\TestCase;

class MaternalTriageAndEpdsTest extends TestCase
{
    /**
     * Test Pillar 1: Rule-based maternal danger-sign triage flags Pre-eclampsia and urgent referral.
     */
    public function test_maternal_triage_flags_preeclampsia_and_triggers_urgent_referral()
    {
        $service = new MaternalTriageService();

        $result = $service->evaluateDangerSigns([
            'gestational_weeks' => 32,
            'symptoms' => ['severe_headache', 'blurred_vision', 'sudden_swelling'],
            'systolic_bp' => 162,
            'diastolic_bp' => 112,
            'locale' => 'en',
        ]);

        $this->assertEquals('critical', $result['risk_level']);
        $this->assertTrue($result['urgent_referral_triggered']);
        $this->assertContains('Pre-eclampsia Risk (Hypertensive Disorder of Pregnancy)', $result['flagged_conditions']);
    }

    /**
     * Test Pillar 1: Early pregnancy bleeding flags threatened miscarriage risk.
     */
    public function test_maternal_triage_flags_early_pregnancy_miscarriage_risk()
    {
        $service = new MaternalTriageService();

        $result = $service->evaluateDangerSigns([
            'gestational_weeks' => 11,
            'symptoms' => ['vaginal_bleeding', 'severe_abdominal_pain'],
            'systolic_bp' => 118,
            'diastolic_bp' => 76,
            'locale' => 'en',
        ]);

        $this->assertEquals('critical', $result['risk_level']);
        $this->assertTrue($result['urgent_referral_triggered']);
        $this->assertContains('Threatened Miscarriage or Ectopic Pregnancy Risk', $result['flagged_conditions']);
    }

    /**
     * Test Pillar 1: Child MUAC nutrition evaluation accurately classifies SAM, MAM, and Normal.
     */
    public function test_child_muac_nutrition_screening_thresholds()
    {
        $service = new MaternalTriageService();

        $sam = $service->evaluateChildNutrition(10.8);
        $this->assertEquals('SAM', $sam['status']);
        $this->assertEquals('critical', $sam['risk_level']);

        $mam = $service->evaluateChildNutrition(12.0);
        $this->assertEquals('MAM', $mam['status']);
        $this->assertEquals('high', $mam['risk_level']);

        $normal = $service->evaluateChildNutrition(13.5);
        $this->assertEquals('NORMAL', $normal['status']);
        $this->assertEquals('low', $normal['risk_level']);
    }

    /**
     * Test Pillar 2: SRHR natural-language category detection across English, Shona, and Ndebele terms.
     */
    public function test_srhr_topic_detection_and_multilingual_support()
    {
        $service = new SrhrGuidanceService();

        $this->assertEquals('emergency_contraception', $service->detectCategory('I need morning after pill after unprotected sex'));
        $this->assertEquals('menstrual_health', $service->detectCategory('How long can I wear a menstrual cup pakutevera kumwedzi?'));
        $this->assertEquals('sti_testing', $service->detectCategory('Where can I get an HIV and syphilis self test kit?'));
    }

    /**
     * Test Pillar 4: EPDS questionnaire localization in English, Shona (sn), and Ndebele (nr).
     */
    public function test_epds_questionnaire_localization_in_shona_and_ndebele()
    {
        $service = new EpdsScreenerService();

        $shonaQuestions = $service->getQuestionnaire('sn');
        $this->assertCount(10, $shonaQuestions);
        $this->assertStringContainsString('kuseka', $shonaQuestions[1]['question']);

        $ndebeleQuestions = $service->getQuestionnaire('nr');
        $this->assertCount(10, $ndebeleQuestions);
        $this->assertStringContainsString('ukuhleka', $ndebeleQuestions[1]['question']);
    }
}

