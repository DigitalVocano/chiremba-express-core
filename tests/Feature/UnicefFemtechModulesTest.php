<?php

namespace Tests\Feature;

use App\Models\ChildProfile;
use App\Models\Patient;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnicefFemtechModulesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verify all migrations and seeders run cleanly without error.
     */
    public function test_migrations_and_unicef_femtech_seeders_run_cleanly()
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('patient', ['phone' => '0771234567']);
        $this->assertDatabaseHas('doctors', ['email' => 'maternal.specialist@chiremba.com']);
        $this->assertDatabaseHas('maternal_profiles', ['gestational_weeks' => 30]);
        $this->assertDatabaseHas('maternal_triage_logs', ['urgent_referral_triggered' => 1]);
        $this->assertDatabaseHas('child_profiles', ['child_name' => 'Tariro Chikwanda']);
        $this->assertDatabaseHas('chw_proxy_intakes', ['offline_uuid' => 'chw-demo-seed-0001']);
        $this->assertDatabaseHas('epds_screenings', ['perinatal_stage' => 'postpartum_0_6w']);
    }

    /**
     * Test Pillar 1: Maternal Danger-Sign Triage API & Urgent Referral Auto-Creation.
     */
    public function test_maternal_danger_sign_triage_api_endpoint_and_referral()
    {
        $this->seed(DatabaseSeeder::class);
        $patient = Patient::where('phone', '0771234567')->first();

        $response = $this->postJson('/api/v1/femtech/maternal/triage', [
            'patient_id' => $patient->id,
            'gestational_weeks' => 34,
            'symptoms' => ['severe_headache', 'blurred_vision'],
            'systolic_bp' => 158,
            'diastolic_bp' => 102,
            'locale' => 'sn',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', 1)
            ->assertJsonPath('data.urgent_referral_triggered', true)
            ->assertJsonPath('data.locale', 'sn');

        $this->assertNotNull($response->json('data.referral_appointment_id'));
    }

    /**
     * Test Pillar 1B: First 1,000 Days Child Registration & 9 EPI Vaccine Milestones Generation.
     */
    public function test_first_1000_days_child_vaccine_and_nutrition_tracker_api()
    {
        $this->seed(DatabaseSeeder::class);
        $patient = Patient::where('phone', '0771234567')->first();

        $response = $this->postJson('/api/v1/femtech/child/schedule', [
            'mother_patient_id' => $patient->id,
            'child_name' => 'Kudzai Chikwanda',
            'date_of_birth' => now()->subWeeks(10)->format('Y-m-d'),
            'sex' => 'male',
            'birth_weight_kg' => 3.4,
            'muac_cm' => 13.2,
            'guardian_phone' => '0771234567',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', 1)
            ->assertJsonPath('milestones_count', 9)
            ->assertJsonPath('nutrition_evaluation.status', 'NORMAL');
    }

    /**
     * Test Pillar 2: Confidential SRHR Anonymous Session, WHO Assistant & Discreet PIN-Verified Order.
     */
    public function test_confidential_srhr_anonymous_assistant_and_discreet_courier_pin_order()
    {
        $this->seed(DatabaseSeeder::class);

        // 1. Start Anonymous Session
        $sessionResp = $this->postJson('/api/v1/femtech/srhr/anonymous-session', [
            'age_bracket' => '15-19',
            'locale' => 'en',
        ]);
        $sessionResp->assertStatus(201);
        $anonToken = $sessionResp->json('session.session_token');
        $this->assertNotEmpty($anonToken);

        // 2. Ask Plain-Language Reproductive Health Assistant
        $askResp = $this->postJson('/api/v1/femtech/srhr/assistant', [
            'anonymous_session_token' => $anonToken,
            'question' => 'Can I take emergency contraception 48 hours after unprotected sex?',
            'locale' => 'en',
        ]);
        $askResp->assertStatus(201)
            ->assertJsonPath('data.topic_category', 'emergency_contraception');

        // 3. Place Discreet Courier Packaged Order with PIN-Verified Drop-Off
        $orderResp = $this->postJson('/api/v1/femtech/srhr/discreet-order', [
            'anonymous_session_token' => $anonToken,
            'supply_name' => 'Levonorgestrel 1.5mg Emergency Contraceptive',
            'quantity' => 1,
            'dropoff_landmark' => 'Highfield Machipisa Shopping Centre Gate 1',
            'contact_phone' => '0778009900',
        ]);

        $orderResp->assertStatus(201)
            ->assertJsonPath('order.is_discreet_packaging', true);
        $this->assertEquals(4, strlen($orderResp->json('discreet_verification_pin')));
    }

    /**
     * Test Pillar 3: Frontline Community Health Worker (CHW) Offline Batch Sync.
     */
    public function test_chw_offline_batch_sync_creates_proxy_intakes_and_urgent_referrals()
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->postJson('/api/v1/femtech/chw/sync', [
            'intakes' => [
                [
                    'offline_uuid' => 'chw-offline-test-uuid-991',
                    'chw_name' => 'VHW Siphiwe Ncube',
                    'chw_location_ward' => 'Gwanda Rural Ward 7',
                    'patient_full_name' => 'Nomsa Dube',
                    'patient_phone' => '0712345678',
                    'patient_age' => 19,
                    'is_pregnant' => true,
                    'gestational_weeks' => 36,
                    'systolic_bp' => 165,
                    'diastolic_bp' => 110,
                    'symptoms' => ['severe_headache', 'sudden_swelling'],
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('synced_count', 1)
            ->assertJsonPath('intakes.0.triage_risk_level', 'critical')
            ->assertJsonPath('intakes.0.synced_from_offline', true);
    }

    /**
     * Test Pillar 4: Maternal Mental Health EPDS Screener surfaces hotlines on high distress.
     */
    public function test_epds_maternal_mental_health_screener_surfaces_hotlines_when_distressed()
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->postJson('/api/v1/femtech/epds/screen', [
            'perinatal_stage' => 'postpartum_0_6w',
            'responses' => [1 => 2, 2 => 2, 3 => 2, 4 => 2, 5 => 2, 6 => 2, 7 => 2, 8 => 1, 9 => 1, 10 => 0],
            'locale' => 'en',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('screening.total_score', 16)
            ->assertJsonPath('screening.risk_severity', 'high_distress')
            ->assertJsonPath('screening.counselor_referral_surfaced', true);

        $this->assertNotEmpty($response->json('screening.support_hotlines'));
    }

    /**
     * Test Pillar 5: HL7/FHIR R4 Diagnostic Export & Omnichannel SMS/USSD Low-Bandwidth Webhook.
     */
    public function test_hl7_fhir_r4_export_and_omnichannel_low_bandwidth_webhook()
    {
        $this->seed(DatabaseSeeder::class);
        $patient = Patient::where('phone', '0771234567')->first();

        // 1. Verify HL7/FHIR R4 Bundle Export
        $fhirResp = $this->getJson("/api/v1/femtech/fhir/patient/{$patient->id}?anonymize=1");
        $fhirResp->assertStatus(200)
            ->assertJsonPath('resourceType', 'Bundle')
            ->assertJsonPath('type', 'collection');

        $resourceTypes = collect($fhirResp->json('entry'))->pluck('resource.resourceType')->unique()->toArray();
        $this->assertContains('Patient', $resourceTypes);
        $this->assertContains('Observation', $resourceTypes);
        $this->assertContains('Condition', $resourceTypes);
        $this->assertContains('Immunization', $resourceTypes);

        // 2. Verify Omnichannel SMS / USSD Low-Bandwidth Triage Webhook
        $smsResp = $this->postJson('/api/v1/femtech/webhook/omnichannel', [
            'from' => '263771234567',
            'channel' => 'sms',
            'text' => 'TRIAGE 32 HEADACHE VISION BP 155/100',
            'locale' => 'en',
        ]);

        $smsResp->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('urgent_referral_triggered', true);
    }
}

