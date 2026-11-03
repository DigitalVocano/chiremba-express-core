<?php

namespace Database\Seeders;

use App\Models\ChildProfile;
use App\Models\ChwProxyIntake;
use App\Models\Doctors;
use App\Models\HealthRecord;
use App\Models\MaternalProfile;
use App\Models\Patient;
use App\Models\Services;
use App\Models\Setting;
use App\Services\EpdsScreenerService;
use App\Services\MaternalTriageService;
use App\Services\SrhrGuidanceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class FemtechUnicefDemoSeeder extends Seeder
{
    /**
     * Seed comprehensive demo data for the 5 UNICEF Femtech & Maternal/Child Health Pillars.
     */
    public function run()
    {
        // 1. Ensure default platform setting exists
        Setting::firstOrCreate(
            ['id' => 1],
            [
                'title' => 'Chiremba Express Core (Femtech)',
                'email' => 'support@chiremba.co.zw',
                'phone' => '+263771234567',
                'address' => 'Harare, Zimbabwe',
                'currency' => 'USD-$',
                'lang_code' => 'en',
                'is_wallet_enabled' => 1,
                'ai_free_daily_limit' => 10,
            ]
        );

        // 2. Ensure core Maternal & SRHR specialties exist
        $obgynService = Services::firstOrCreate(
            ['name' => 'Obstetrics, Gynaecology & Maternal Health'],
            ['description' => 'Antenatal care, high-risk pregnancy management, and SRHR consultations.', 'is_active' => 1, 'recommendable' => 1]
        );

        Services::firstOrCreate(
            ['name' => 'Paediatrics & First 1,000 Days Child Health'],
            ['description' => 'Neonatal care, EPI immunizations, and child nutrition monitoring.', 'is_active' => 1, 'recommendable' => 1]
        );

        Services::firstOrCreate(
            ['name' => 'Perinatal Mental Health & Psychosocial Support'],
            ['description' => 'EPDS postpartum depression counseling and maternal wellness.', 'is_active' => 1, 'recommendable' => 1]
        );

        // 3. Seed Maternal Specialist & SRHR Partner Pharmacy
        Doctors::updateOrCreate(
            ['email' => 'maternal.specialist@chiremba.com'],
            [
                'name' => 'Dr. Rutendo Moyo (OB/GYN & Maternal Specialist)',
                'slug' => 'dr-rutendo-moyo-obgyn',
                'phoneno' => '0772000111',
                'password' => Hash::make('123456'),
                'profile_type' => '1',
                'is_approve' => '1',
                'is_demo' => 1,
                'department_id' => $obgynService->id,
                'address' => 'Mbuya Nehanda Maternity Wing, Harare, Zimbabwe',
            ]
        );

        Doctors::updateOrCreate(
            ['email' => 'srhr.pharmacy@chiremba.com'],
            [
                'name' => 'Chiremba Discreet SRHR Community Pharmacy',
                'slug' => 'chiremba-discreet-srhr-pharmacy',
                'phoneno' => '0772000222',
                'password' => Hash::make('123456'),
                'profile_type' => '2',
                'is_approve' => '1',
                'is_demo' => 1,
                'department_id' => $obgynService->id,
                'address' => 'Samora Machel Ave, Harare, Zimbabwe',
            ]
        );

        // 4. Seed Expectant Mother Patient & Maternal Profile (Pillar 1)
        $motherAttrs = [
            'name' => 'Nyasha Chikwanda',
            'email' => 'patientdemo@chiremba.com',
            'password' => Hash::make('123456'),
        ];
        if (Schema::hasColumn('patient', 'is_demo')) {
            $motherAttrs['is_demo'] = 1;
        }
        $mother = Patient::updateOrCreate(
            ['phone' => '0771234567'],
            $motherAttrs
        );

        $maternalProfile = MaternalProfile::updateOrCreate(
            ['patient_id' => $mother->id],
            [
                'lmp_date' => now()->subWeeks(30)->format('Y-m-d'),
                'edd_date' => now()->addWeeks(10)->format('Y-m-d'),
                'gestational_weeks' => 30,
                'gravida' => 2,
                'para' => 1,
                'blood_group' => 'O+',
                'high_risk_flag' => true,
                'risk_factors' => ['Previous Gestational Hypertension'],
                'preferred_language' => 'en',
                'sms_reminders_enabled' => true,
            ]
        );

        // 5. Seed Maternal Vitals Log (FHIR R4 Exportable)
        HealthRecord::firstOrCreate(
            ['patient_id' => $mother->id, 'date' => now()->subDays(7)->format('Y-m-d')],
            [
                'blood_pressure' => '124/82',
                'blood_pressure_systolic' => 124,
                'blood_pressure_diastolic' => 82,
                'weight' => 67.5,
                'temperature' => 36.7,
                'glucose_level' => 5.1,
                'blood_glucose_level' => 92,
                'gestational_weeks' => 29,
                'fetal_heart_rate' => 144,
                'fundal_height_cm' => 29.0,
                'recorded_by_role' => 'patient',
            ]
        );

        HealthRecord::firstOrCreate(
            ['patient_id' => $mother->id, 'date' => now()->format('Y-m-d')],
            [
                'blood_pressure' => '148/96',
                'blood_pressure_systolic' => 148,
                'blood_pressure_diastolic' => 96,
                'weight' => 68.8,
                'temperature' => 36.8,
                'glucose_level' => 5.6,
                'blood_glucose_level' => 101,
                'gestational_weeks' => 30,
                'fetal_heart_rate' => 148,
                'fundal_height_cm' => 30.0,
                'recorded_by_role' => 'chw',
            ]
        );

        // 6. Seed Danger-Sign Triage Log (Pillar 1)
        $maternalService = app(MaternalTriageService::class);
        $maternalService->recordTriageAndRoute([
            'patient_id' => $mother->id,
            'maternal_profile_id' => $maternalProfile->id,
            'gestational_weeks' => 30,
            'symptoms' => ['severe_headache', 'blurred_vision', 'sudden_swelling'],
            'systolic_bp' => 148,
            'diastolic_bp' => 96,
            'temperature' => 36.8,
            'weight_kg' => 68.8,
            'blood_glucose_mmol' => 5.6,
            'channel' => 'web',
            'locale' => 'en',
        ]);

        // 7. Seed First 1,000 Days Child & EPI Vaccine Schedule (Pillar 1B)
        $child = ChildProfile::firstOrCreate(
            ['mother_patient_id' => $mother->id, 'child_name' => 'Tariro Chikwanda'],
            [
                'date_of_birth' => now()->subWeeks(6)->format('Y-m-d'),
                'sex' => 'female',
                'birth_weight_kg' => 3.25,
                'birth_length_cm' => 49.5,
                'feeding_mode' => 'exclusive_breastfeeding',
                'guardian_phone' => '0771234567',
                'preferred_language' => 'en',
            ]
        );
        $maternalService->generateFirst1000DaysSchedule($child);

        // 8. Seed Confidential SRHR Session & Inquiry (Pillar 2)
        $srhrService = app(SrhrGuidanceService::class);
        $anonSession = $srhrService->startAnonymousSession('15-19', 'en');
        $srhrService->answerConfidentialInquiry([
            'anonymous_session_token' => $anonSession->session_token,
            'topic_category' => 'emergency_contraception',
            'question' => 'How many hours after unprotected sex is emergency contraception effective?',
            'locale' => 'en',
        ]);

        // 9. Seed CHW Proxy Intake (Pillar 3)
        ChwProxyIntake::firstOrCreate(
            ['offline_uuid' => 'chw-demo-seed-0001'],
            [
                'chw_name' => 'VHW Chipo Ndlovu',
                'chw_location_ward' => 'Murehwa Ward 4 Rural Health Centre',
                'patient_id' => $mother->id,
                'patient_full_name' => 'Tendai Moyo (Rural Proxy Intake)',
                'patient_phone' => '0779001122',
                'patient_age' => 22,
                'is_pregnant' => true,
                'gestational_weeks' => 34,
                'vital_signs' => ['systolic_bp' => 152, 'diastolic_bp' => 98, 'weight_kg' => 64.0, 'muac_cm' => 23.5],
                'symptoms' => ['severe_headache', 'sudden_swelling'],
                'triage_risk_level' => 'high',
                'triage_summary' => 'URGENT CLINICAL REFERRAL REQUIRED: Pre-eclampsia Risk detected via CHW tablet intake.',
                'synced_from_offline' => true,
                'client_recorded_at' => now()->subHours(2),
            ]
        );

        // 10. Seed Postpartum Depression EPDS Screening (Pillar 4)
        $epdsService = app(EpdsScreenerService::class);
        $epdsService->scoreAndRecord([
            'patient_id' => $mother->id,
            'perinatal_stage' => 'postpartum_0_6w',
            'responses' => [1 => 2, 2 => 2, 3 => 2, 4 => 2, 5 => 1, 6 => 2, 7 => 1, 8 => 1, 9 => 1, 10 => 0],
            'locale' => 'en',
        ]);
    }
}

