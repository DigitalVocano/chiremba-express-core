@extends('user.layout')
@section('title', __('femtech.portal_title') . ' | Chiremba Express')

@section('content')
<div class="bg-gray-100 dark:bg-gray-900 min-h-screen py-8 text-gray-800 dark:text-gray-100"
     x-data="{
        activeTab: 'maternal',
        aiPrompt: '32 weeks pregnant. Experiencing severe persistent headache since morning, blurred vision with flashing lights, and sudden swelling of face and hands.',
        aiGestWeeks: 32,
        aiSysBp: 158,
        aiDiaBp: 102,
        aiMode: 'maternal',
        aiAnalyzing: false,
        aiStep: 1,
        aiResult: {
            riskBadge: 'CRITICAL RISK FLAGGED • URGENT REFERRAL',
            condition: 'Severe Pre-eclampsia Risk (Hypertensive Disorder of Pregnancy • ICD-10 O14.1)',
            protocol: 'WHO Antenatal Care (ANC) & Maternal Danger-Sign Protocol',
            reasoning: 'AI Clinical Assessment: Patient is at 32 weeks gestation presenting with severe hypertension (BP 158/102 mmHg >= 140/90 threshold) accompanied by neurological/visual danger signs (severe persistent headache, scotomata/blurred vision, facial/hand edema). Immediate antihypertensive stabilization and obstetric specialist consultation required.',
            doctorName: 'Dr. Rutendo Moyo (MBChB, MMed OB/GYN)',
            doctorFacility: 'Mbuya Nehanda Maternity Wing, Harare • MDPCZ Verified Specialist',
            rxCode: 'RX-ZW-2026-904',
            rxItems: 'Nifedipine SR 20mg + Methyldopa 250mg + Prenatal Folate/Iron Pack',
            pharmacyName: 'Chiremba Discreet SRHR Community Pharmacy (Samora Machel Ave, Harare)',
            courierPin: '4829',
            amountUsd: '5.00'
        },
        bookingConfirmed: false,
        bookingSlot: 'Immediate Priority Video Consultation (Today • In 5 Mins)',
        ecocashPhone: '0771234567',
        ecocashProcessing: false,
        ecocashPaid: false,
        ecocashRef: 'MP261103.1555.C94',
        loadPreset(type) {
            this.aiMode = type;
            this.bookingConfirmed = false;
            this.ecocashPaid = false;
            if (type === 'maternal') {
                this.aiPrompt = '32 weeks pregnant. Experiencing severe persistent headache since morning, blurred vision with flashing lights, and sudden swelling of face and hands.';
                this.aiGestWeeks = 32;
                this.aiSysBp = 158;
                this.aiDiaBp = 102;
                this.aiResult = {
                    riskBadge: 'CRITICAL RISK FLAGGED • URGENT REFERRAL',
                    condition: 'Severe Pre-eclampsia Risk (Hypertensive Disorder of Pregnancy • ICD-10 O14.1)',
                    protocol: 'WHO Antenatal Care (ANC) & Maternal Danger-Sign Protocol',
                    reasoning: 'AI Clinical Assessment: Patient is at 32 weeks gestation presenting with severe hypertension (BP 158/102 mmHg >= 140/90 threshold) accompanied by neurological/visual danger signs (severe persistent headache, blurred vision, facial/hand edema). Immediate specialist referral prioritized.',
                    doctorName: 'Dr. Rutendo Moyo (MBChB, MMed OB/GYN)',
                    doctorFacility: 'Mbuya Nehanda Maternity Wing, Harare • MDPCZ Verified Specialist',
                    rxCode: 'RX-ZW-2026-904',
                    rxItems: 'Nifedipine SR 20mg + Methyldopa 250mg + Maternal Stabilization Pack',
                    pharmacyName: 'Chiremba Discreet SRHR Community Pharmacy (Samora Machel Ave, Harare)',
                    courierPin: '4829',
                    amountUsd: '5.00'
                };
            } else {
                this.aiPrompt = 'Adolescent anonymous user (19y): Unprotected intercourse 24 hours ago, also experiencing mild lower pelvic discomfort and requesting confidential STI & emergency contraception guidance.';
                this.aiGestWeeks = 0;
                this.aiSysBp = 118;
                this.aiDiaBp = 76;
                this.aiResult = {
                    riskBadge: 'TIME-SENSITIVE SRHR & STI PROTOCOL FLAGGED',
                    condition: 'Emergency Contraception (<72h Window) + Pelvic Infection / STI Rule-Out',
                    protocol: 'WHO Family Planning & Syndromic STI Management Guidelines',
                    reasoning: 'AI Clinical Assessment: Patient is within the optimal 72-hour window for Levonorgestrel 1.5mg emergency contraception (>85% efficacy). Concurrent lower pelvic discomfort warrants confidential tele-consultation and dual HIV/Syphilis + STI screening.',
                    doctorName: 'Dr. Rutendo Moyo (SRHR & Adolescent Friendly Specialist)',
                    doctorFacility: 'Harare Confidential Tele-Clinic • Zero-PII Session Linked',
                    rxCode: 'RX-SRHR-ANON-712',
                    rxItems: 'Levonorgestrel 1.5mg EC + OraQuick Dual Self-Test + Prophylactic Pack',
                    pharmacyName: 'Chiremba Discreet SRHR Community Pharmacy (Unmarked Parcel)',
                    courierPin: '7391',
                    amountUsd: '5.00'
                };
            }
        },
        runAiTriage() {
            this.aiAnalyzing = true;
            this.bookingConfirmed = false;
            this.ecocashPaid = false;
            setTimeout(() => {
                this.aiAnalyzing = false;
                this.aiStep = 2;
            }, 900);
        },
        confirmDoctorBooking() {
            this.bookingConfirmed = true;
            this.aiStep = 3;
        },
        payWithEcocash() {
            this.ecocashProcessing = true;
            setTimeout(() => {
                this.ecocashProcessing = false;
                this.ecocashPaid = true;
            }, 800);
        }
     }">

    {{-- Top Clinical Header, Quick-Exit & Language Bar --}}
    <div class="container mx-auto px-4 mb-6">
        <div class="bg-primary-600 dark:bg-primary-700 text-white rounded-xl shadow-lg p-5 flex flex-wrap items-center justify-between gap-4 border border-primary-500">
            <div class="flex items-center space-x-3">
                <span class="bg-white/20 p-3 rounded-lg text-white">
                    <i class="fas fa-robot text-xl"></i>
                </span>
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="bg-white/20 text-white text-[11px] font-bold uppercase px-2.5 py-0.5 rounded-full">UNICEF Venture Fund FemTech DPG</span>
                        <span class="bg-emerald-500 text-white text-[11px] font-bold uppercase px-2.5 py-0.5 rounded-full">AI Clinical Triage Engine Active</span>
                    </div>
                    <h1 class="text-lg md:text-xl font-bold text-white">{{ __('femtech.portal_title') }}</h1>
                    <p class="text-xs text-blue-100 mt-0.5">
                        <strong>Pillar 1:</strong> Healthcare Access for Women & Girls (Maternal, ANC/PNC, Newborn & Continuity of Care) • <strong>Pillar 2:</strong> Informed, Safe & Accessible Reproductive Health (SRHR)
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{-- Multilingual Switcher: English, ChiShona, isiNdebele --}}
                <div class="bg-primary-700 dark:bg-gray-800 rounded-lg p-1 flex items-center space-x-1 text-xs border border-white/20">
                    <a href="{{ route('femtech.hub', ['lang' => 'en']) }}" class="px-3 py-1.5 rounded font-semibold transition {{ $locale === 'en' ? 'bg-white text-primary-700 shadow-sm' : 'text-white hover:bg-primary-500' }}">English</a>
                    <a href="{{ route('femtech.hub', ['lang' => 'sn']) }}" class="px-3 py-1.5 rounded font-semibold transition {{ $locale === 'sn' ? 'bg-white text-primary-700 shadow-sm' : 'text-white hover:bg-primary-500' }}">ChiShona</a>
                    <a href="{{ route('femtech.hub', ['lang' => 'nr']) }}" class="px-3 py-1.5 rounded font-semibold transition {{ $locale === 'nr' ? 'bg-white text-primary-700 shadow-sm' : 'text-white hover:bg-primary-500' }}">isiNdebele</a>
                </div>

                {{-- Full Conversational AI Chat Shortcut --}}
                <a href="{{ route('patient.ai_assistant') }}" class="bg-white text-primary-700 hover:bg-blue-50 text-xs font-bold px-3.5 py-2 rounded-lg shadow-sm transition flex items-center">
                    <i class="fas fa-comments mr-1.5"></i> Full AI Chat
                </a>

                {{-- CHW Mode Shortcut --}}
                <a href="{{ route('chw.portal') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3.5 py-2 rounded-lg shadow-sm transition flex items-center">
                    <i class="fas fa-user-nurse mr-1.5"></i> CHW Offline Mode
                </a>

                {{-- Quick-Exit / Privacy Safety Button --}}
                <button type="button" onclick="triggerQuickExit()" class="bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-4 py-2 rounded-lg shadow transition flex items-center">
                    <i class="fas fa-eye-slash mr-1.5"></i> {{ __('femtech.quick_exit') }}
                </button>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4">
        {{-- =====================================================================
             LIVE AI CLINICAL TRIAGE -> DOCTOR BOOKING -> ECOCASH DISPATCH LOOP
             (Designed specifically for Act 2 [0:35 - 1:15] Live Demo Recording)
             ===================================================================== --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border-2 border-primary-500 p-6 mb-8">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-200 dark:border-gray-700 pb-4 mb-6">
                <div class="flex items-center space-x-3">
                    <div class="h-11 w-11 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow">
                        <i class="fas fa-brain text-lg"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-white">
                                Chiremba AI Clinical Triage, Specialist Routing & Discreet EcoCash Pharmacy Loop
                            </h2>
                            <span class="bg-green-100 text-green-800 text-[11px] font-bold px-2.5 py-0.5 rounded-full">
                                <i class="fas fa-user-secret mr-1"></i> Anonymous Session: ANON-ZW-8842
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5">
                            Real-time AI evaluation calibrated against WHO Maternal (ANC/PNC) & SRHR Clinical Protocols -> Verified Zimbabwean Specialist -> Discreet Community Pharmacy EcoCash Dispatch
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="loadPreset('maternal')" :class="aiMode === 'maternal' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200'" class="text-xs font-bold px-3 py-1.5 rounded-lg transition">
                        Pillar 1 Scenario: Pre-eclampsia AI Triage
                    </button>
                    <button type="button" @click="loadPreset('srhr')" :class="aiMode === 'srhr' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200'" class="text-xs font-bold px-3 py-1.5 rounded-lg transition">
                        Pillar 2 Scenario: Adolescent SRHR AI Triage
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- STEP 1 (0:35 - 0:50): AI SYMPTOM & VITALS TRIAGE INTAKE --}}
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700 p-5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="bg-primary-600 text-white text-xs font-bold px-2.5 py-1 rounded-md">STEP 1 • AI Triage Intake</span>
                            <span class="text-[11px] font-semibold text-primary-600 dark:text-primary-300"><i class="fas fa-shield-alt mr-1"></i> Zero-PII Anonymous</span>
                        </div>

                        <label class="block text-xs font-bold text-gray-800 dark:text-gray-100 mb-1">
                            Patient Symptoms & Narrative (English / ChiShona / isiNdebele)
                        </label>
                        <textarea x-model="aiPrompt" rows="3" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-2.5 text-xs mb-3"></textarea>

                        <div class="grid grid-cols-3 gap-2 mb-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-200 mb-1">Gest. Weeks</label>
                                <input type="number" x-model="aiGestWeeks" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-2 text-xs font-bold">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-200 mb-1">Systolic BP</label>
                                <input type="number" x-model="aiSysBp" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-2 text-xs font-bold">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-200 mb-1">Diastolic BP</label>
                                <input type="number" x-model="aiDiaBp" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-2 text-xs font-bold">
                            </div>
                        </div>
                    </div>

                    <button type="button" @click="runAiTriage()" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-4 rounded-xl shadow transition text-xs flex items-center justify-center">
                        <template x-if="!aiAnalyzing">
                            <span><i class="fas fa-robot mr-1.5"></i> Evaluate with AI Engine (WHO Protocol)</span>
                        </template>
                        <template x-if="aiAnalyzing">
                            <span><i class="fas fa-spinner fa-spin mr-1.5"></i> AI Evaluating Vitals & WHO Protocols...</span>
                        </template>
                    </button>
                </div>

                {{-- STEP 2 (0:50 - 1:02): ACUTE RISK FLAG & VERIFIED DOCTOR BOOKING --}}
                <div class="rounded-xl border-2 p-5 flex flex-col justify-between transition"
                     :class="aiStep >= 2 ? 'border-red-500 bg-white dark:bg-gray-800 shadow-md' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700'">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="bg-red-600 text-white text-xs font-bold px-2.5 py-1 rounded-md">STEP 2 • AI Risk Flag & Specialist</span>
                            <span class="text-[11px] font-bold text-red-600 uppercase" x-text="aiResult.riskBadge"></span>
                        </div>

                        <div class="p-3 rounded-lg bg-red-50 dark:bg-gray-700 border border-red-200 dark:border-red-500 mb-3">
                            <div class="text-xs font-bold text-gray-900 dark:text-white" x-text="aiResult.condition"></div>
                            <div class="text-[11px] font-semibold text-primary-600 dark:text-primary-300 mt-0.5" x-text="'Calibrated against: ' + aiResult.protocol"></div>
                            <p class="text-xs text-gray-700 dark:text-gray-200 mt-1.5 leading-relaxed" x-text="aiResult.reasoning"></p>
                        </div>

                        {{-- Matched Verified Zimbabwean Specialist Card --}}
                        <div class="p-3 rounded-lg bg-blue-50 dark:bg-gray-700 border border-blue-200 dark:border-gray-600 mb-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] font-bold uppercase text-primary-600 dark:text-primary-300">Priority Specialist Match</span>
                                    <h4 class="text-xs font-bold text-gray-900 dark:text-white" x-text="aiResult.doctorName"></h4>
                                    <p class="text-[11px] text-gray-600 dark:text-gray-300" x-text="aiResult.doctorFacility"></p>
                                </div>
                                <span class="bg-green-100 text-green-800 text-[10px] font-bold px-2 py-1 rounded">Online Now</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <template x-if="!bookingConfirmed">
                            <button type="button" @click="confirmDoctorBooking()" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-xl shadow transition text-xs flex items-center justify-center">
                                <i class="fas fa-video mr-1.5"></i> Book Direct Virtual Consultation Now
                            </button>
                        </template>
                        <template x-if="bookingConfirmed">
                            <div class="p-2.5 rounded-xl bg-green-100 text-green-900 text-xs font-bold text-center border border-green-300">
                                <i class="fas fa-check-circle mr-1"></i> Virtual Consultation Booked • E-Prescription Generated!
                            </div>
                        </template>
                    </div>
                </div>

                {{-- STEP 3 (1:02 - 1:15): DIGITAL E-PRESCRIPTION DISPATCH & ECOCASH CHECKOUT --}}
                <div class="rounded-xl border-2 p-5 flex flex-col justify-between transition"
                     :class="aiStep >= 3 ? 'border-emerald-500 bg-white dark:bg-gray-800 shadow-md' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700'">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="bg-emerald-600 text-white text-xs font-bold px-2.5 py-1 rounded-md">STEP 3 • E-Prescription & EcoCash</span>
                            <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400" x-text="aiResult.rxCode"></span>
                        </div>

                        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 mb-3 text-xs">
                            <div class="font-bold text-gray-900 dark:text-white mb-1">
                                <i class="fas fa-prescription-bottle-alt text-primary-500 mr-1"></i> Digital E-Prescription:
                            </div>
                            <p class="font-semibold text-primary-600 dark:text-primary-300" x-text="aiResult.rxItems"></p>
                            <p class="text-[11px] text-gray-600 dark:text-gray-300 mt-1">
                                <strong>Partner Pharmacy:</strong> <span x-text="aiResult.pharmacyName"></span>
                            </p>
                            <div class="mt-2 flex items-center justify-between bg-white dark:bg-gray-800 p-2 rounded border border-gray-200 dark:border-gray-600">
                                <span class="text-[11px] font-bold text-gray-800 dark:text-gray-100">
                                    <i class="fas fa-box text-emerald-600 mr-1"></i> Unmarked Discreet Parcel
                                </span>
                                <span class="bg-primary-100 text-primary-800 text-[11px] font-bold px-2 py-0.5 rounded" x-text="'Drop-Off PIN: ' + aiResult.courierPin"></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-200 mb-1">EcoCash Mobile No.</label>
                                <input type="text" x-model="ecocashPhone" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-2 text-xs font-bold">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-200 mb-1">Total Copay (USD)</label>
                                <input type="text" :value="'$' + aiResult.amountUsd + ' (EcoCash)'" readonly class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white p-2 text-xs font-bold">
                            </div>
                        </div>
                    </div>

                    <div>
                        <template x-if="!ecocashPaid">
                            <button type="button" @click="payWithEcocash()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-xl shadow transition text-xs flex items-center justify-center">
                                <template x-if="!ecocashProcessing">
                                    <span><i class="fas fa-mobile-alt mr-1.5"></i> Instant Checkout via EcoCash Mobile Money ($5.00)</span>
                                </template>
                                <template x-if="ecocashProcessing">
                                    <span><i class="fas fa-spinner fa-spin mr-1.5"></i> Sending EcoCash USSD Push to 0771234567...</span>
                                </template>
                            </button>
                        </template>
                        <template x-if="ecocashPaid">
                            <div class="p-2.5 rounded-xl bg-emerald-100 text-emerald-900 text-xs font-bold text-center border border-emerald-300">
                                <i class="fas fa-check-double mr-1"></i> EcoCash Paid (<span x-text="ecocashRef"></span>) • Discreet Courier Dispatched!
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- UNICEF Pillar Overview Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border-l-4 border-primary-500 shadow-sm">
                <div class="text-xs font-bold uppercase text-primary-600 dark:text-primary-400">Pillar 1 • Healthcare Access for Women & Girls</div>
                <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">
                    Maternal, antenatal/postnatal care, newborn health (First 1,000 Days EPI immunization & growth), maternal mental health (EPDS), CHW offline continuity of care, and HL7/FHIR R4 hospital interoperability.
                </p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border-l-4 border-emerald-500 shadow-sm">
                <div class="text-xs font-bold uppercase text-emerald-600 dark:text-emerald-400">Pillar 2 • Informed, Safe & Accessible Reproductive Health</div>
                <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">
                    Confidential SRHR consultations, overcoming stigma via zero-PII anonymous sessions, WHO-calibrated contraception & STI guidance, and unmarked PIN-verified e-pharmacy courier dispatch.
                </p>
            </div>
        </div>

        {{-- Module Navigation Tabs --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
            <button @click="activeTab = 'maternal'" :class="activeTab === 'maternal' ? 'bg-primary-600 text-white border-primary-600 shadow-md' : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'" class="p-3.5 rounded-xl font-semibold text-sm flex items-center justify-center space-x-2 border transition">
                <i class="fas fa-baby-carriage"></i>
                <span>Pillar 1A: Maternal & Newborn</span>
            </button>
            <button @click="activeTab = 'srhr'" :class="activeTab === 'srhr' ? 'bg-primary-600 text-white border-primary-600 shadow-md' : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'" class="p-3.5 rounded-xl font-semibold text-sm flex items-center justify-center space-x-2 border transition">
                <i class="fas fa-user-secret"></i>
                <span>Pillar 2: Confidential SRHR</span>
            </button>
            <button @click="activeTab = 'epds'" :class="activeTab === 'epds' ? 'bg-primary-600 text-white border-primary-600 shadow-md' : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'" class="p-3.5 rounded-xl font-semibold text-sm flex items-center justify-center space-x-2 border transition">
                <i class="fas fa-heart"></i>
                <span>Pillar 1B: Postnatal Mental Health</span>
            </button>
            <button @click="activeTab = 'fhir'" :class="activeTab === 'fhir' ? 'bg-primary-600 text-white border-primary-600 shadow-md' : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700'" class="p-3.5 rounded-xl font-semibold text-sm flex items-center justify-center space-x-2 border transition">
                <i class="fas fa-network-wired"></i>
                <span>Pillar 1C: Continuity & FHIR R4</span>
            </button>
        </div>

        {{-- TAB 1: PILLAR 1A - MATERNAL TRIAGE, VITALS & FIRST 1,000 DAYS NEWBORN HEALTH --}}
        <div x-show="activeTab === 'maternal'" class="space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Gestational Milestone & Danger-Sign Triage Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center">
                            <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                            {{ __('femtech.maternal_triage_title') }}
                        </h2>
                        <span class="text-xs bg-red-100 text-red-800 px-2.5 py-1 rounded-full font-semibold">Pillar 1 • WHO ANC</span>
                    </div>

                    <form action="{{ route('femtech.maternal.triage') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="locale" value="{{ $locale }}">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">{{ __('femtech.gestational_weeks_label') }} *</label>
                                <input type="number" name="gestational_weeks" min="1" max="45" value="{{ optional($maternalProfile)->gestational_weeks ?? 28 }}" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">Systolic BP (mmHg)</label>
                                <input type="number" name="systolic_bp" placeholder="e.g. 120" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">Diastolic BP (mmHg)</label>
                                <input type="number" name="diastolic_bp" placeholder="e.g. 80" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-2">{{ __('femtech.select_symptoms_prompt') }}</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach(__('femtech.symptoms') as $code => $label)
                                    <label class="flex items-start space-x-2 p-2.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 hover:bg-blue-50 dark:hover:bg-gray-600 cursor-pointer text-xs">
                                        <input type="checkbox" name="symptoms[]" value="{{ $code }}" class="mt-0.5 text-primary-600 rounded">
                                        <span class="text-gray-800 dark:text-gray-100 font-medium">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-4 rounded-lg shadow transition text-sm">
                            <i class="fas fa-stethoscope mr-1.5"></i> Run Clinical Danger-Sign Triage & Priority Referral
                        </button>
                    </form>

                    @if($triageLogs->isNotEmpty())
                        <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                            <h3 class="text-xs font-bold uppercase text-gray-500 dark:text-gray-400 mb-2">Recent Triage Assessments</h3>
                            <div class="space-y-2">
                                @foreach($triageLogs as $log)
                                    <div class="p-3.5 rounded-lg text-xs border {{ $log->urgent_referral_triggered ? 'bg-red-50 dark:bg-gray-700 border-red-200 dark:border-red-500 text-gray-900 dark:text-gray-100' : 'bg-green-50 dark:bg-gray-700 border-green-200 dark:border-green-500 text-gray-900 dark:text-gray-100' }}">
                                        <div class="flex justify-between items-center font-bold mb-1">
                                            <span>Week {{ $log->gestational_weeks }} • Risk: {{ strtoupper($log->risk_level) }}</span>
                                            @if($log->urgent_referral_triggered)
                                                <span class="bg-red-600 text-white px-2 py-0.5 rounded text-[11px]">URGENT REFERRAL</span>
                                            @endif
                                        </div>
                                        <p class="leading-relaxed text-gray-700 dark:text-gray-200">{{ $log->clinical_guidance }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Maternal Vitals Log & FHIR Export Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center">
                            <i class="fas fa-heartbeat text-primary-500 mr-2"></i>
                            Maternal Vitals & Continuity of Care Log
                        </h2>
                        <a href="{{ route('femtech.fhir.export') }}" target="_blank" class="text-xs bg-primary-600 hover:bg-primary-700 text-white px-3 py-1.5 rounded-lg font-semibold flex items-center shadow-sm">
                            <i class="fas fa-file-code mr-1.5"></i> Export FHIR R4 JSON
                        </a>
                    </div>

                    <form action="{{ route('femtech.maternal.vitals') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">Date *</label>
                                <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">Systolic BP *</label>
                                <input type="number" name="blood_pressure_systolic" placeholder="120" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">Diastolic BP *</label>
                                <input type="number" name="blood_pressure_diastolic" placeholder="80" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">Weight (kg)</label>
                                <input type="number" step="0.1" name="weight" placeholder="68.5" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">Glucose (mmol/L)</label>
                                <input type="number" step="0.1" name="glucose_level" placeholder="5.2" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">Fetal HR (bpm)</label>
                                <input type="number" name="fetal_heart_rate" placeholder="145" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2 text-sm">
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-4 rounded-lg shadow transition text-sm">
                            <i class="fas fa-plus-circle mr-1.5"></i> Record Maternal Vitals Entry
                        </button>
                    </form>

                    @if($vitals->isNotEmpty())
                        <div class="mt-6 overflow-x-auto">
                            <table class="w-full text-left text-xs text-gray-700 dark:text-gray-200">
                                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100 uppercase">
                                    <tr>
                                        <th class="p-2.5">Date</th>
                                        <th class="p-2.5">BP (mmHg)</th>
                                        <th class="p-2.5">Weight (kg)</th>
                                        <th class="p-2.5">Glucose</th>
                                        <th class="p-2.5">FHR</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($vitals as $v)
                                        <tr>
                                            <td class="p-2.5">{{ optional($v->date)->format('Y-m-d') }}</td>
                                            <td class="p-2.5 font-bold text-primary-600 dark:text-primary-400">{{ $v->blood_pressure ?: ($v->blood_pressure_systolic . '/' . $v->blood_pressure_diastolic) }}</td>
                                            <td class="p-2.5">{{ $v->weight ?? '-' }}</td>
                                            <td class="p-2.5">{{ $v->glucose_level ?? $v->blood_glucose_level ?? '-' }}</td>
                                            <td class="p-2.5">{{ $v->fetal_heart_rate ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- First 1,000 Days Vaccination & Growth Tracker --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center">
                            <i class="fas fa-syringe text-primary-500 mr-2"></i>
                            {{ __('femtech.immunization_title') }}
                        </h2>
                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5">Pillar 1 Newborn Continuity of Care: Automated EPI Vaccination Milestones (BCG, Polio, Pentavalent, Measles-Rubella, Vitamin A) & WHO MUAC Nutrition Check-ins</p>
                    </div>
                </div>

                <form action="{{ route('femtech.child.register') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-5 gap-3 mb-6 bg-gray-50 dark:bg-gray-700 p-4 rounded-xl border border-gray-200 dark:border-gray-600">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-800 dark:text-gray-100 mb-1">Infant Name *</label>
                        <input type="text" name="child_name" placeholder="e.g. Tariro Moyo" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-800 dark:text-gray-100 mb-1">Date of Birth *</label>
                        <input type="date" name="date_of_birth" value="{{ date('Y-m-d', strtotime('-6 weeks')) }}" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-800 dark:text-gray-100 mb-1">Birth Weight (kg)</label>
                        <input type="number" step="0.1" name="birth_weight_kg" placeholder="3.2" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-800 dark:text-gray-100 mb-1">MUAC (cm) Check</label>
                        <input type="number" step="0.1" name="muac_cm" placeholder="13.0" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-2 text-sm">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded-lg text-sm shadow-sm">
                            Generate EPI Tracker
                        </button>
                    </div>
                </form>

                @if($children->isNotEmpty())
                    <div class="space-y-6">
                        @foreach($children as $child)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 bg-white dark:bg-gray-800">
                                <h3 class="font-bold text-sm text-primary-600 dark:text-primary-400 mb-3 flex items-center">
                                    <i class="fas fa-baby mr-2"></i> {{ $child->child_name }} (DOB: {{ optional($child->date_of_birth)->format('d M Y') }})
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    @foreach($child->vaccineMilestones as $m)
                                        <div class="p-3.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-xs flex flex-col justify-between">
                                            <div>
                                                <div class="flex justify-between items-center font-bold text-gray-900 dark:text-white">
                                                    <span>{{ $m->vaccine_code }} (Wk {{ $m->target_age_weeks }})</span>
                                                    <span class="uppercase px-2 py-0.5 rounded text-[10px] font-bold {{ $m->status === 'due' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">{{ $m->status }}</span>
                                                </div>
                                                <p class="font-semibold text-gray-800 dark:text-gray-100 mt-1.5">{{ $m->vaccine_name }}</p>
                                                <p class="text-gray-600 dark:text-gray-300 mt-1">Due: {{ optional($m->due_date)->format('d M Y') }}</p>
                                                <p class="text-primary-600 dark:text-primary-300 mt-1.5 italic">{{ $m->nutrition_checkin_notes }}</p>
                                            </div>
                                            <form action="{{ route('femtech.child.reminder', $m->id) }}" method="POST" class="mt-3 pt-2 border-t border-gray-200 dark:border-gray-600">
                                                @csrf
                                                <button type="submit" class="text-xs text-primary-600 dark:text-primary-300 hover:underline font-bold flex items-center">
                                                    <i class="fas fa-sms mr-1.5"></i> Send SMS/WhatsApp Reminder
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- TAB 2: PILLAR 2 - INFORMED, SAFE & ACCESSIBLE REPRODUCTIVE HEALTH (SRHR) --}}
        <div x-show="activeTab === 'srhr'" class="grid grid-cols-1 lg:grid-cols-2 gap-8" style="display: none;">
            {{-- Plain-Language Reproductive Health Assistant --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                        <i class="fas fa-comments text-primary-500 mr-2"></i> Plain-Language Reproductive Health Assistant
                    </h2>
                    <form action="{{ route('femtech.srhr.anonymous') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-900 font-bold px-3 py-1.5 rounded-lg transition">
                            <i class="fas fa-user-secret mr-1"></i> Activate Anonymous Mode
                        </button>
                    </form>
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-300 mb-4">
                    <strong>Pillar 2 (Overcoming Stigma & Confidential Consultations):</strong> Ask sensitive questions regarding contraception, emergency contraception, menstrual health, fertility, or STI symptoms. Calibrated against WHO Family Planning Guidelines with zero real-world profile linking.
                </p>

                <form action="{{ route('femtech.srhr.ask') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="locale" value="{{ $locale }}">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">Topic Category</label>
                        <select name="topic_category" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2 text-sm">
                            <option value="emergency_contraception">Emergency Contraception (Morning-After Pill)</option>
                            <option value="contraception">Daily & Long-Acting Contraceptive Options / Side Effects</option>
                            <option value="menstrual_health">Menstrual Health, Cups & Cramp Relief</option>
                            <option value="sti_testing">STI Symptoms & Confidential Self-Testing Kits</option>
                            <option value="fertility">Fertility, Ovulation & Preconception Folate</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">Your Question (Anonymous)</label>
                        <textarea name="question" rows="3" required placeholder="e.g. How soon should I take emergency contraception and what side effects are normal?" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2.5 text-sm"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-4 rounded-lg shadow text-sm">
                        <i class="fas fa-lock mr-1.5"></i> Get Confidential WHO-Calibrated Guidance
                    </button>
                </form>

                @if($srhrHistory->isNotEmpty())
                    <div class="mt-6 space-y-3 border-t border-gray-200 dark:border-gray-700 pt-4">
                        <h3 class="text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Recent Confidential Guidance</h3>
                        @foreach($srhrHistory as $item)
                            <div class="p-3.5 bg-gray-50 dark:bg-gray-700 rounded-lg text-xs border border-gray-200 dark:border-gray-600">
                                <p class="font-bold text-gray-900 dark:text-white mb-1">Q: {{ $item->question }}</p>
                                <p class="text-gray-700 dark:text-gray-200 mb-1.5 leading-relaxed">{{ $item->guidance_response }}</p>
                                <span class="text-[11px] text-primary-600 dark:text-primary-300 font-semibold">Ref: {{ $item->who_guideline_ref }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Confidential Courier Packaging & SRHR Supply Dispatch --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
                    <i class="fas fa-box-open text-primary-500 mr-2"></i> Discreet SRHR Supply Dispatch (PIN-Verified Drop-Off)
                </h2>
                <p class="text-xs text-gray-600 dark:text-gray-300 mb-4">
                    {{ __('femtech.discreet_pin_notice') }}
                </p>

                <form action="{{ route('femtech.srhr.order') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">Select Confidential SRHR Supply *</label>
                        <select name="supply_name" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2.5 text-sm">
                            <option value="Levonorgestrel 1.5mg Emergency Contraceptive">Levonorgestrel 1.5mg Emergency Contraceptive ($5.00)</option>
                            <option value="OraQuick HIV & Syphilis Dual Self-Test Kit">OraQuick HIV & Syphilis Dual Self-Test Kit ($5.00)</option>
                            <option value="Medical-Grade Reusable Menstrual Cup">Medical-Grade Reusable Menstrual Cup ($5.00)</option>
                            <option value="Combined Oral Contraceptive 3-Month Pack">Combined Oral Contraceptive 3-Month Pack ($5.00)</option>
                            <option value="High-Sensitivity Early Pregnancy Test Twin Pack">High-Sensitivity Early Pregnancy Test Twin Pack ($5.00)</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">Quantity</label>
                            <input type="number" name="quantity" value="1" min="1" max="5" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">SMS PIN Contact Number *</label>
                            <input type="text" name="contact_phone" placeholder="0771234567" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">Drop-off Landmark / Community Pickup Point *</label>
                        <input type="text" name="dropoff_landmark" placeholder="e.g. Chitungwiza Unit L Shopping Centre Gate 2" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2 text-sm">
                    </div>

                    <div class="p-3 bg-green-50 dark:bg-gray-700 border border-green-200 dark:border-green-600 rounded-lg text-xs text-gray-900 dark:text-gray-100">
                        <i class="fas fa-check-circle text-green-600 mr-1"></i> <strong>Discreet Packaging Flag Locked ON:</strong> Unmarked plain parcel + 4-digit courier PIN verification.
                    </div>

                    <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-4 rounded-lg shadow text-sm">
                        <i class="fas fa-shipping-fast mr-1.5"></i> Dispatch Discreet Unmarked Order
                    </button>
                </form>
            </div>
        </div>

        {{-- TAB 3: PILLAR 1B - MATERNAL & POSTNATAL MENTAL HEALTH SCREENING (EPDS) --}}
        <div x-show="activeTab === 'epds'" class="grid grid-cols-1 lg:grid-cols-3 gap-8" style="display: none;">
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-1">
                    <i class="fas fa-brain text-primary-500 mr-2"></i> {{ __('femtech.epds_title') }}
                </h2>
                <p class="text-xs text-gray-600 dark:text-gray-300 mb-6">{{ __('femtech.epds_intro') }}</p>

                <form action="{{ route('femtech.epds.submit') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="locale" value="{{ $locale }}">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">Perinatal Stage</label>
                        <select name="perinatal_stage" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2 text-sm">
                            <option value="antenatal">Antenatal (Currently Pregnant)</option>
                            <option value="postpartum_0_6w">Early Postpartum (0 - 6 Weeks After Birth)</option>
                            <option value="postpartum_6w_12m">Extended Postpartum (6 Weeks - 12 Months After Birth)</option>
                        </select>
                    </div>

                    <div class="space-y-3">
                        @foreach($epdsQuestions as $qNum => $qData)
                            <div class="p-3.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
                                <label class="block text-xs font-bold text-gray-900 dark:text-white mb-2">
                                    {{ $qNum }}. {{ $qData['question'] }}
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($qData['options'] as $opt)
                                        <label class="flex items-center space-x-2 text-xs cursor-pointer">
                                            <input type="radio" name="responses[{{ $qNum }}]" value="{{ $opt['score'] }}" {{ $opt['score'] === 0 ? 'checked' : '' }} class="text-primary-600">
                                            <span class="text-gray-800 dark:text-gray-100">{{ $opt['label'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-4 rounded-lg shadow text-sm">
                        <i class="fas fa-check-double mr-1.5"></i> Calculate Confidential EPDS Screening Score
                    </button>
                </form>
            </div>

            {{-- Psychosocial Support & Certified Mental Health Counselors --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-2">
                    <i class="fas fa-hands-helping text-primary-500 mr-1.5"></i> Certified Perinatal Mental Health Support
                </h3>
                <p class="text-xs text-gray-600 dark:text-gray-300 mb-4">
                    {{ __('femtech.epds_support_message') }}
                </p>

                <div class="space-y-3">
                    @foreach($supportHotlines as $line)
                        <div class="p-3.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-xs">
                            <p class="font-bold text-gray-900 dark:text-white">{{ $line['name'] }}</p>
                            <p class="text-gray-600 dark:text-gray-300 mt-0.5">{{ $line['type'] }}</p>
                            <div class="mt-2.5 flex flex-wrap gap-2">
                                <span class="bg-white dark:bg-gray-800 px-2.5 py-1 rounded border border-gray-200 dark:border-gray-600 font-semibold text-gray-800 dark:text-gray-100">
                                    <i class="fas fa-phone-alt text-primary-600 mr-1"></i> {{ $line['phone'] }}
                                </span>
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded font-semibold">
                                    {{ $line['hours'] }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- TAB 4: PILLAR 1C - CONTINUITY OF CARE, HL7/FHIR R4 & LOW-BANDWIDTH GATEWAY --}}
        <div x-show="activeTab === 'fhir'" class="grid grid-cols-1 lg:grid-cols-2 gap-8" style="display: none;">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
                    <i class="fas fa-file-medical-alt text-primary-500 mr-2"></i> HL7 / FHIR R4 Continuity of Care Export
                </h2>
                <p class="text-xs text-gray-600 dark:text-gray-300 mb-4 leading-relaxed">
                    Standardizes maternal vitals, danger-sign triage conditions, infant EPI immunizations, EPDS mental health scores, and e-prescriptions into HL7 FHIR R4 JSON Bundles (`Patient`, `Observation`, `Condition`, `Immunization`, `MedicationRequest`) for seamless continuity of care across hospital EHRs and DHIS2.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('femtech.fhir.export') }}" target="_blank" class="bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold px-4 py-2.5 rounded-lg shadow-sm">
                        <i class="fas fa-download mr-1.5"></i> View Full HL7/FHIR R4 Bundle (JSON)
                    </a>
                    <a href="{{ route('femtech.fhir.export', ['anonymize' => 1]) }}" target="_blank" class="bg-gray-800 hover:bg-gray-700 text-white text-xs font-bold px-4 py-2.5 rounded-lg shadow-sm">
                        <i class="fas fa-user-shield mr-1.5"></i> Export De-Identified DHIS2 Bundle
                    </a>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
                    <i class="fas fa-broadcast-tower text-primary-500 mr-2"></i> Omnichannel Low-Bandwidth SMS / USSD / WhatsApp Gateway
                </h2>
                <p class="text-xs text-gray-600 dark:text-gray-300 mb-3">
                    Webhook endpoint: <code>POST /api/v1/femtech/webhook/omnichannel</code>
                </p>
                <div class="bg-gray-900 text-green-400 p-4 rounded-lg text-xs font-mono space-y-1">
                    <div># Example 2G SMS / USSD Maternal Triage Command:</div>
                    <div>"TRIAGE 32 HEADACHE VISION BP 155/100"</div>
                    <div class="text-gray-300">-> Flags Pre-eclampsia Risk & routes urgent specialist referral</div>
                    <div class="pt-2"># Example Confidential SRHR SMS Query:</div>
                    <div>"SRHR emergency contraception window"</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function triggerQuickExit() {
        try {
            sessionStorage.clear();
            localStorage.removeItem('srhr_anonymous_draft');
        } catch (e) {}
        window.location.replace("{{ route('femtech.quick_exit') }}");
    }
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            triggerQuickExit();
        }
    });
</script>
@endsection
