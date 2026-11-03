<?php

namespace App\Services;

use App\Models\PharmacyOrder;
use App\Models\SrhrAnonymousSession;
use App\Models\SrhrInquiry;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class SrhrGuidanceService
{
    /**
     * Start or retrieve a zero-PII anonymous SRHR session.
     */
    public function startAnonymousSession(?string $ageBracket = '15-19', ?string $locale = 'en'): SrhrAnonymousSession
    {
        $token = hash('sha256', Str::uuid()->toString() . microtime(true) . random_bytes(16));
        $alias = 'ANON-' . strtoupper( substr($token, 0, 6) );

        return SrhrAnonymousSession::create([
            'session_token' => $token,
            'alias_code' => $alias,
            'age_bracket' => $ageBracket,
            'preferred_language' => $locale ?: 'en',
            'expires_at' => now()->addHours(24),
        ]);
    }

    /**
     * Provide WHO-calibrated, stigma-free, plain-language reproductive health guidance.
     */
    public function answerConfidentialInquiry(array $data): SrhrInquiry
    {
        $question = trim($data['question'] ?? '');
        $category = $data['topic_category'] ?? $this->detectCategory($question);
        $locale = $data['locale'] ?? App::getLocale() ?: 'en';

        $guidelinePackage = $this->getWhoCalibratedGuidance($category, $question, $locale);

        return SrhrInquiry::create([
            'anonymous_session_token' => $data['anonymous_session_token'] ?? null,
            'patient_id' => $data['patient_id'] ?? null,
            'topic_category' => $category,
            'question' => $question,
            'guidance_response' => $guidelinePackage['response'],
            'who_guideline_ref' => $guidelinePackage['who_reference'],
            'recommended_supplies' => $guidelinePackage['supplies'],
            'locale' => $locale,
        ]);
    }

    /**
     * Generate a 4-digit PIN and flag a pharmacy order for unmarked, discreet courier drop-off.
     */
    public function applyDiscreetDispatchToOrder(PharmacyOrder $order, ?string $anonymousToken = null, ?string $srhrCategory = 'srhr_confidential'): PharmacyOrder
    {
        $pin = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);

        $order->is_discreet_packaging = true;
        $order->discreet_verification_pin = $pin;
        if ($anonymousToken) {
            $order->anonymous_session_token = $anonymousToken;
            $order->recipient_name = 'Confidential Alias (' . strtoupper(substr($anonymousToken, 0, 6)) . ')';
        }
        $order->srhr_category = $srhrCategory;
        $order->message = trim(($order->message ? $order->message . ' | ' : '') . '[DISCREET UNMARKED PACKAGING REQUIRED - PIN VERIFIED DROP-OFF]');
        $order->save();

        return $order;
    }

    /**
     * Detect SRHR topic category from natural language query (English, Shona, Ndebele).
     */
    public function detectCategory(string $question): string
    {
        $q = strtolower($question);
        if (Str::contains($q, ['emergency', 'morning after', 'postinor', 'unprotected', 'condom broke', 'levonorgestrel'])) {
            return 'emergency_contraception';
        }
        if (Str::contains($q, ['sti', 'std', 'discharge', 'ulcer', 'burning', 'hiv', 'syphilis', 'chlamydia', 'gonorrhea', 'test kit', 'siki'])) {
            return 'sti_testing';
        }
        if (Str::contains($q, ['period', 'menstrua', 'cup', 'pad', 'cramp', 'bleeding', 'kumwedzi', 'esikhathini'])) {
            return 'menstrual_health';
        }
        if (Str::contains($q, ['fertile', 'ovulat', 'conceive', 'pregnant test', 'pregnancy test', 'nhumbu', 'ukuzithwala'])) {
            return 'fertility';
        }
        return 'contraception';
    }

    /**
     * WHO Family Planning & SRHR Handbook (2022/2024) calibrated plain-language guidance.
     */
    protected function getWhoCalibratedGuidance(string $category, string $question, string $locale): array
    {
        $catalog = [
            'emergency_contraception' => [
                'who_reference' => 'WHO Family Planning Global Handbook (Emergency Contraception Pill Guidelines)',
                'supplies' => ['Levonorgestrel 1.5mg (Emergency Contraceptive Pill)', 'Combined Oral Contraceptive Pack', 'Dual HIV/Syphilis Self-Test Kit', 'Home Urine Pregnancy Test Strip'],
                'en' => "Emergency contraception pills (such as Levonorgestrel 1.5mg) can prevent pregnancy up to 5 days (120 hours) after unprotected sex, but they are most effective when taken as soon as possible (ideally within 72 hours). They do not interrupt an existing pregnancy. Common mild side effects include temporary nausea, fatigue, or your next period arriving a few days early or late. If you vomit within 2 hours of taking the dose, another dose is recommended. You can order emergency contraception discreetly in unmarked packaging with PIN-verified delivery.",
                'sn' => "Mapiritsi ekudzivirira pamuviri nechimbichimbi (Emergency Contraception seLevonorgestrel 1.5mg) anoshanda mukati memazuva mashanu (maawa 120) mushureokusangana pabonde pasina dziviriro, asi anoshanda zvakanyanya kana akanwiwa nekukasika (mukati memaawa 72). Haabvisi pamuviri pava паpo kare. Unogona kuodha mapiritsi aya pachivande pasina zita rako.",
                'nr' => "Amaphilisi okuvikela ukuzithwala ngokushesha (Emergency Contraception njenge-Levonorgestrel 1.5mg) asebenza phakathi kwensuku ezinhlanu (amahola angu-120) ngemva kocansi olungavikelekanga, kodwa asebenza kuhle kakhulu nxa enathwe masinyane (phakathi kwamahola angu-72). Ungawa-oda ngemfihlo ngephakheji engelabizo.",
            ],
            'sti_testing' => [
                'who_reference' => 'WHO Guidelines for the Management of Symptomatic Sexually Transmitted Infections',
                'supplies' => ['OraQuick HIV Self-Test Kit', 'Dual HIV/Syphilis Rapid Self-Test', 'Urine Pregnancy Test', 'Male/Female Barrier Condoms'],
                'en' => "Unusual vaginal/urethral discharge, pain during urination, pelvic pain, or genital sores/itching can be signs of a curable Sexually Transmitted Infection (such as Chlamydia, Gonorrhoea, Trichomoniasis, or Syphilis) or a common yeast/bacterial infection. WHO guidelines recommend prompt syndromic or laboratory evaluation, completing the full antibiotic course if prescribed, and partner treatment to prevent re-infection. Confidential self-test kits (HIV & Syphilis) and private tele-consultations are available without exposing your identity.",
                'sn' => "Kubuda mvura isina kujairika kuzasi, kurwadziwa pakurasa mvura, kana maronda panhengo dzakavandika zvinogona kuratidza chirwere chepabonde (STI) chinorapika. Sangano reWHO rinokurudzira kuongororwa nekukasika nekunwa mishonga yose inonyorwa naChiremba. Unogona kuodha maSelf-Test Kits pachivande.",
                'nr' => "Ukuphuma amanzi angajayelekanga ngaphansi, ukuzwa ubuhlungu nxa uchama, kumbe izilonda zingaba yimpawu zemikhuhlane yocansi (STI) eyelaphekayo. I-WHO ikhuthaza ukuhlolwa masinyane lokunatha amaphilisi wonke. Ungathola ama-Self-Test Kits ngemfihlo.",
            ],
            'menstrual_health' => [
                'who_reference' => 'WHO Guideline on Self-Care Interventions for Health and Well-Being (Menstrual Health)',
                'supplies' => ['Medical-Grade Silicone Menstrual Cup', 'Ibuprofen 400mg / Mefenamic Acid (Menstrual Cramp Relief)', 'Iron + Folic Acid Supplement Tablets'],
                'en' => "A healthy menstrual cycle typically occurs every 21 to 35 days and lasts 2 to 7 days. Mild cramping can be managed with anti-inflammatory pain relief (like Ibuprofen or Mefenamic acid) and warmth. Medical-grade menstrual cups can be safely worn for up to 8–12 hours and sterilized in boiling water between cycles. Seek clinical advice if you soak through a pad/cup every hour for 2+ consecutive hours, experience severe sudden pelvic pain, or miss periods unexpectedly.",
                'sn' => "Kuenda kumwedzi kweutano kunoitika mazuva makumi maviri nerimwe kusvika makumi matatu nemashanu (21-35 days). Marwadzo emudumbu anogona kuderedzwa neIbuprofen. Menstrual Cup inokwanisa kushandiswa kwemaawa 8-12 yakachengeteka. Kana ropa richibuda zrakanyanyisa kana kurwadza zvikuru, taura naChiremba pachivande.",
                'nr' => "Ukuya esikhathini okujayelekileyo kwenzakala phakathi kwensuku ezingu-21 kusiya ku-35. Ubuhlungu besisu bungaphathwa nge-Ibuprofen. I-Menstrual Cup ingasetshenziswa amahola angu-8-12 iphephile.",
            ],
            'fertility' => [
                'who_reference' => 'WHO Preconception & Fertility Awareness Clinical Guidance',
                'supplies' => ['High-Sensitivity Home Pregnancy Test Strip', 'LH Ovulation Test Strips', 'Prenatal Folic Acid 400mcg'],
                'en' => "Ovulation typically occurs about 12 to 16 days before the start of your next menstrual period, creating a 5-to-6-day fertile window. Home urine pregnancy tests are most accurate from the first day of a missed period using first-morning urine. If you are planning a pregnancy, WHO recommends starting 400 mcg of Folic Acid daily at least one month before conception to prevent neural tube defects.",
                'sn' => "Nguva yekubata pamuviri (ovulation) inowanzova mazuva 12 kusvika 16 usati waenda kumwedzi unotevera. MaPregnancy Test anoshanda zvakanaka kubva pazuva rekutanga ramunenge madarikwa nemwedzi. WHO inokurudzira kunwa Folic Acid (400mcg) zuva nezuva.",
                'nr' => "Isikhathi sokubambaisisu (ovulation) sijwayele ukuba zinsuku ezingu-12 kusiya ku-16 ungakayi esikhathini esilandelayo. I-Pregnancy Test isebenza kuhle kusukela ngosuku lokuqala oludlulileyo ungayanga esikhathini.",
            ],
            'contraception' => [
                'who_reference' => 'WHO Medical Eligibility Criteria for Contraceptive Use (MEC 5th Edition)',
                'supplies' => ['Oral Contraceptive Pills (28-Day Pack)', 'Sayana Press (DMPA-SC Self-Injectable)', 'Barrier Condoms', 'Emergency Contraceptive Pill'],
                'en' => "Modern contraceptives—including daily oral pills, DMPA-SC (Sayana Press) 3-month injectables, subdermal implants, and IUDs—are over 99% effective when used correctly. Mild irregular spotting or changes in menstrual bleeding during the first 3–6 months of hormonal contraception are normal and not harmful. Dual protection (using condoms alongside hormonal contraception) protects against both unintended pregnancy and STIs/HIV.",
                'sn' => "Nzira dzemazuva ano dzekurera mhuri—dzinosanganisira mapiritsi ezuva nezuva, majekiseni emwedzi mitatu (Sayana Press/Depo), neJadelle—dzinoshanda zvikuru. Kuchinja kwekutevera kumwedzi mumwedzi mitatu yekutanga hakuna njodzi.",
                'nr' => "Indlela zesimanje zokuhlela imuli—ezigoqela amaphilisi ansuku zonke, amajekiseni enyanga ezintathu (Sayana Press), le-Jadelle—zisebenza kakhulu. Ukutshiyana kwesikhathi sokuya esikhathini enyangeni ezintathu zokuqala kujayelekile.",
            ],
        ];

        $entry = $catalog[$category] ?? $catalog['contraception'];
        $langKey = in_array($locale, ['en', 'sn', 'nr'], true) ? $locale : 'en';

        return [
            'response' => $entry[$langKey] ?? $entry['en'],
            'who_reference' => $entry['who_reference'],
            'supplies' => $entry['supplies'],
        ];
    }
}

