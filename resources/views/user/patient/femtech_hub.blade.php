@extends('user.layout')
@section('title', __('femtech.portal_title') . ' | Chiremba Express')

@section('content')
<div class="bg-gray-50 dark:bg-gray-900 min-h-screen py-8" x-data="{ activeTab: 'maternal' }">
    {{-- Top Privacy, Quick-Exit & Language Bar --}}
    <div class="container mx-auto px-4 mb-6">
        <div class="bg-purple-900 text-white rounded-xl shadow-lg p-4 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
                <span class="bg-purple-700 p-2.5 rounded-lg">
                    <i class="fas fa-shield-alt text-pink-300 text-xl"></i>
                </span>
                <div>
                    <h1 class="text-lg md:text-xl font-bold">{{ __('femtech.portal_title') }}</h1>
                    <p class="text-xs text-purple-200">
                        @if($anonToken)
                            <i class="fas fa-user-secret text-green-300 mr-1"></i> {{ __('femtech.anonymous_mode_active') }} ({{ Session::get('srhr_alias_code', 'ANON') }})
                        @else
                            WHO-Calibrated Maternal Triage • First 1,000 Days • Confidential SRHR • EPDS Mental Health • HL7/FHIR R4
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{-- Multilingual Switcher: English, ChiShona, isiNdebele --}}
                <div class="bg-purple-800 rounded-lg p-1 flex items-center space-x-1 text-xs">
                    <a href="{{ route('femtech.hub', ['lang' => 'en']) }}" class="px-2.5 py-1.5 rounded {{ $locale === 'en' ? 'bg-white text-purple-900 font-bold' : 'text-purple-200 hover:text-white' }}">English</a>
                    <a href="{{ route('femtech.hub', ['lang' => 'sn']) }}" class="px-2.5 py-1.5 rounded {{ $locale === 'sn' ? 'bg-white text-purple-900 font-bold' : 'text-purple-200 hover:text-white' }}">ChiShona</a>
                    <a href="{{ route('femtech.hub', ['lang' => 'nr']) }}" class="px-2.5 py-1.5 rounded {{ $locale === 'nr' ? 'bg-white text-purple-900 font-bold' : 'text-purple-200 hover:text-white' }}">isiNdebele</a>
                </div>

                {{-- CHW Mode Shortcut --}}
                <a href="{{ route('chw.portal') }}" class="bg-teal-600 hover:bg-teal-500 text-white text-xs font-semibold px-3 py-2 rounded-lg transition flex items-center">
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
        {{-- Pillar Navigation Tabs --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
            <button @click="activeTab = 'maternal'" :class="activeTab === 'maternal' ? 'bg-primary-600 text-white shadow-md' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200'" class="p-3.5 rounded-xl font-semibold text-sm flex items-center justify-center space-x-2 border border-gray-200 dark:border-gray-700 transition">
                <i class="fas fa-baby-carriage"></i>
                <span>1. Maternal & First 1,000 Days</span>
            </button>
            <button @click="activeTab = 'srhr'" :class="activeTab === 'srhr' ? 'bg-purple-600 text-white shadow-md' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200'" class="p-3.5 rounded-xl font-semibold text-sm flex items-center justify-center space-x-2 border border-gray-200 dark:border-gray-700 transition">
                <i class="fas fa-user-secret"></i>
                <span>2. Confidential SRHR Portal</span>
            </button>
            <button @click="activeTab = 'epds'" :class="activeTab === 'epds' ? 'bg-pink-600 text-white shadow-md' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200'" class="p-3.5 rounded-xl font-semibold text-sm flex items-center justify-center space-x-2 border border-gray-200 dark:border-gray-700 transition">
                <i class="fas fa-heart"></i>
                <span>3. Maternal Mental Health (EPDS)</span>
            </button>
            <button @click="activeTab = 'fhir'" :class="activeTab === 'fhir' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200'" class="p-3.5 rounded-xl font-semibold text-sm flex items-center justify-center space-x-2 border border-gray-200 dark:border-gray-700 transition">
                <i class="fas fa-network-wired"></i>
                <span>4. HL7/FHIR & Low-Bandwidth</span>
            </button>
        </div>

        {{-- TAB 1: MATERNAL TRIAGE, VITALS & FIRST 1,000 DAYS --}}
        <div x-show="activeTab === 'maternal'" class="space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Gestational Milestone & Danger-Sign Triage Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white flex items-center">
                            <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                            {{ __('femtech.maternal_triage_title') }}
                        </h2>
                        <span class="text-xs bg-red-100 text-red-800 px-2.5 py-1 rounded-full font-semibold">WHO ANC Protocol</span>
                    </div>

                    <form action="{{ route('femtech.maternal.triage') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="locale" value="{{ $locale }}">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">{{ __('femtech.gestational_weeks_label') }} *</label>
                                <input type="number" name="gestational_weeks" min="1" max="45" value="{{ optional($maternalProfile)->gestational_weeks ?? 28 }}" required class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white p-2 text-sm border">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Systolic BP (mmHg)</label>
                                <input type="number" name="systolic_bp" placeholder="e.g. 120" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white p-2 text-sm border">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Diastolic BP (mmHg)</label>
                                <input type="number" name="diastolic_bp" placeholder="e.g. 80" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white p-2 text-sm border">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-2">{{ __('femtech.select_symptoms_prompt') }}</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach(__('femtech.symptoms') as $code => $label)
                                    <label class="flex items-start space-x-2 p-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-red-50 dark:hover:bg-gray-700 cursor-pointer text-xs">
                                        <input type="checkbox" name="symptoms[]" value="{{ $code }}" class="mt-0.5 text-red-600 rounded">
                                        <span class="text-gray-700 dark:text-gray-200">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-4 rounded-lg shadow transition text-sm">
                            <i class="fas fa-stethoscope mr-1.5"></i> Run Clinical Danger-Sign Triage & Priority Referral
                        </button>
                    </form>

                    @if($triageLogs->isNotEmpty())
                        <div class="mt-6 border-t pt-4">
                            <h3 class="text-xs font-bold uppercase text-gray-500 mb-2">Recent Triage Assessments</h3>
                            <div class="space-y-2">
                                @foreach($triageLogs as $log)
                                    <div class="p-3 rounded-lg text-xs {{ $log->urgent_referral_triggered ? 'bg-red-50 border border-red-200 text-red-900' : 'bg-green-50 border border-green-200 text-green-900' }}">
                                        <div class="flex justify-between font-bold mb-1">
                                            <span>Week {{ $log->gestational_weeks }} • Risk: {{ strtoupper($log->risk_level) }}</span>
                                            @if($log->urgent_referral_triggered)
                                                <span class="bg-red-600 text-white px-2 py-0.5 rounded">URGENT REFERRAL</span>
                                            @endif
                                        </div>
                                        <p>{{ $log->clinical_guidance }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Maternal Vitals Log & FHIR Export Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white flex items-center">
                            <i class="fas fa-heartbeat text-primary-500 mr-2"></i>
                            Maternal Vitals Log (HL7/FHIR Ready)
                        </h2>
                        <a href="{{ route('femtech.fhir.export') }}" target="_blank" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg font-semibold flex items-center">
                            <i class="fas fa-file-code mr-1.5"></i> Export FHIR R4 JSON
                        </a>
                    </div>

                    <form action="{{ route('femtech.maternal.vitals') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Date *</label>
                                <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full rounded-lg border p-2 text-sm dark:bg-gray-700 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Systolic BP *</label>
                                <input type="number" name="blood_pressure_systolic" placeholder="120" required class="w-full rounded-lg border p-2 text-sm dark:bg-gray-700 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Diastolic BP *</label>
                                <input type="number" name="blood_pressure_diastolic" placeholder="80" required class="w-full rounded-lg border p-2 text-sm dark:bg-gray-700 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Weight (kg)</label>
                                <input type="number" step="0.1" name="weight" placeholder="68.5" class="w-full rounded-lg border p-2 text-sm dark:bg-gray-700 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Glucose (mmol/L)</label>
                                <input type="number" step="0.1" name="glucose_level" placeholder="5.2" class="w-full rounded-lg border p-2 text-sm dark:bg-gray-700 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Fetal HR (bpm)</label>
                                <input type="number" name="fetal_heart_rate" placeholder="145" class="w-full rounded-lg border p-2 text-sm dark:bg-gray-700 dark:text-white">
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-2.5 px-4 rounded-lg shadow transition text-sm">
                            <i class="fas fa-plus-circle mr-1.5"></i> Record Maternal Vitals Entry
                        </button>
                    </form>

                    @if($vitals->isNotEmpty())
                        <div class="mt-6 overflow-x-auto">
                            <table class="w-full text-left text-xs text-gray-600 dark:text-gray-300">
                                <thead class="bg-gray-100 dark:bg-gray-700 uppercase">
                                    <tr>
                                        <th class="p-2">Date</th>
                                        <th class="p-2">BP (mmHg)</th>
                                        <th class="p-2">Weight (kg)</th>
                                        <th class="p-2">Glucose</th>
                                        <th class="p-2">FHR</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @foreach($vitals as $v)
                                        <tr>
                                            <td class="p-2">{{ optional($v->date)->format('Y-m-d') }}</td>
                                            <td class="p-2 font-semibold">{{ $v->blood_pressure ?: ($v->blood_pressure_systolic . '/' . $v->blood_pressure_diastolic) }}</td>
                                            <td class="p-2">{{ $v->weight ?? '-' }}</td>
                                            <td class="p-2">{{ $v->glucose_level ?? $v->blood_glucose_level ?? '-' }}</td>
                                            <td class="p-2">{{ $v->fetal_heart_rate ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- First 1,000 Days Vaccination & Growth Tracker --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-100 dark:border-gray-700">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white flex items-center">
                            <i class="fas fa-syringe text-teal-600 mr-2"></i>
                            {{ __('femtech.immunization_title') }}
                        </h2>
                        <p class="text-xs text-gray-500">Automated EPI Vaccination Milestones (BCG, Polio, Pentavalent, Measles-Rubella, Vitamin A) & WHO MUAC Nutrition Check-ins</p>
                    </div>
                </div>

                <form action="{{ route('femtech.child.register') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-5 gap-3 mb-6 bg-teal-50 dark:bg-gray-700/50 p-4 rounded-xl">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">Infant Name *</label>
                        <input type="text" name="child_name" placeholder="e.g. Tariro Moyo" required class="w-full rounded-lg border p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">Date of Birth *</label>
                        <input type="date" name="date_of_birth" value="{{ date('Y-m-d', strtotime('-6 weeks')) }}" required class="w-full rounded-lg border p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">Birth Weight (kg)</label>
                        <input type="number" step="0.1" name="birth_weight_kg" placeholder="3.2" class="w-full rounded-lg border p-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">MUAC (cm) Check</label>
                        <input type="number" step="0.1" name="muac_cm" placeholder="13.0" class="w-full rounded-lg border p-2 text-sm">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded-lg text-sm">
                            Generate EPI Tracker
                        </button>
                    </div>
                </form>

                @if($children->isNotEmpty())
                    <div class="space-y-6">
                        @foreach($children as $child)
                            <div class="border rounded-xl p-4">
                                <h3 class="font-bold text-sm text-teal-800 dark:text-teal-300 mb-3">
                                    <i class="fas fa-baby mr-1"></i> {{ $child->child_name }} (DOB: {{ optional($child->date_of_birth)->format('d M Y') }})
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    @foreach($child->vaccineMilestones as $m)
                                        <div class="p-3 rounded-lg border {{ $m->status === 'due' ? 'border-amber-300 bg-amber-50' : 'border-gray-200 bg-gray-50' }} text-xs flex flex-col justify-between">
                                            <div>
                                                <div class="flex justify-between items-center font-bold text-gray-800">
                                                    <span>{{ $m->vaccine_code }} (Wk {{ $m->target_age_weeks }})</span>
                                                    <span class="uppercase px-2 py-0.5 rounded bg-white border">{{ $m->status }}</span>
                                                </div>
                                                <p class="font-medium mt-1">{{ $m->vaccine_name }}</p>
                                                <p class="text-gray-500 mt-1">Due: {{ optional($m->due_date)->format('d M Y') }}</p>
                                                <p class="text-teal-700 mt-1 italic">{{ $m->nutrition_checkin_notes }}</p>
                                            </div>
                                            <form action="{{ route('femtech.child.reminder', $m->id) }}" method="POST" class="mt-2">
                                                @csrf
                                                <button type="submit" class="text-xs text-teal-700 hover:underline font-semibold">
                                                    <i class="fas fa-sms mr-1"></i> Send SMS/WhatsApp Reminder
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

        {{-- TAB 2: CONFIDENTIAL SRHR PORTAL & DISCREET COURIER PACKAGING --}}
        <div x-show="activeTab === 'srhr'" class="grid grid-cols-1 lg:grid-cols-2 gap-8" style="display: none;">
            {{-- Plain-Language Reproductive Health Assistant --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-purple-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-purple-900 dark:text-purple-300">
                        <i class="fas fa-comments mr-2"></i> Plain-Language Reproductive Health Assistant
                    </h2>
                    <form action="{{ route('femtech.srhr.anonymous') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-xs bg-purple-100 hover:bg-purple-200 text-purple-900 font-bold px-3 py-1.5 rounded-lg">
                            <i class="fas fa-user-secret mr-1"></i> New Anonymous Session
                        </button>
                    </form>
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-300 mb-4">
                    Ask sensitive questions regarding contraception, emergency contraception, menstrual health, fertility, or STI symptoms. Calibrated against WHO Family Planning Guidelines with zero real-world profile linking.
                </p>

                <form action="{{ route('femtech.srhr.ask') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="locale" value="{{ $locale }}">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Topic Category</label>
                        <select name="topic_category" class="w-full rounded-lg border p-2 text-sm dark:bg-gray-700 dark:text-white">
                            <option value="emergency_contraception">Emergency Contraception (Morning-After Pill)</option>
                            <option value="contraception">Daily & Long-Acting Contraceptive Options / Side Effects</option>
                            <option value="menstrual_health">Menstrual Health, Cups & Cramp Relief</option>
                            <option value="sti_testing">STI Symptoms & Confidential Self-Testing Kits</option>
                            <option value="fertility">Fertility, Ovulation & Preconception Folate</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Your Question (Anonymous)</label>
                        <textarea name="question" rows="3" required placeholder="e.g. How soon should I take emergency contraception and what side effects are normal?" class="w-full rounded-lg border p-2.5 text-sm dark:bg-gray-700 dark:text-white"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2.5 px-4 rounded-lg shadow text-sm">
                        <i class="fas fa-lock mr-1.5"></i> Get Confidential WHO-Calibrated Guidance
                    </button>
                </form>

                @if($srhrHistory->isNotEmpty())
                    <div class="mt-6 space-y-3 border-t pt-4">
                        <h3 class="text-xs font-bold uppercase text-gray-500">Recent Confidential Guidance</h3>
                        @foreach($srhrHistory as $item)
                            <div class="p-3 bg-purple-50 dark:bg-gray-700 rounded-lg text-xs">
                                <p class="font-bold text-purple-900 dark:text-purple-200 mb-1">Q: {{ $item->question }}</p>
                                <p class="text-gray-700 dark:text-gray-200 mb-1">{{ $item->guidance_response }}</p>
                                <span class="text-[11px] text-purple-600 font-semibold">Ref: {{ $item->who_guideline_ref }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Confidential Courier Packaging & SRHR Supply Dispatch --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-purple-200 dark:border-gray-700">
                <h2 class="text-lg font-bold text-purple-900 dark:text-purple-300 mb-2">
                    <i class="fas fa-box-open mr-2"></i> Discreet SRHR Supply Dispatch (PIN-Verified Drop-Off)
                </h2>
                <p class="text-xs text-gray-600 dark:text-gray-300 mb-4">
                    {{ __('femtech.discreet_pin_notice') }}
                </p>

                <form action="{{ route('femtech.srhr.order') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Select Confidential SRHR Supply *</label>
                        <select name="supply_name" class="w-full rounded-lg border p-2.5 text-sm dark:bg-gray-700 dark:text-white">
                            <option value="Levonorgestrel 1.5mg Emergency Contraceptive">Levonorgestrel 1.5mg Emergency Contraceptive ($5.00)</option>
                            <option value="OraQuick HIV & Syphilis Dual Self-Test Kit">OraQuick HIV & Syphilis Dual Self-Test Kit ($5.00)</option>
                            <option value="Medical-Grade Reusable Menstrual Cup">Medical-Grade Reusable Menstrual Cup ($5.00)</option>
                            <option value="Combined Oral Contraceptive 3-Month Pack">Combined Oral Contraceptive 3-Month Pack ($5.00)</option>
                            <option value="High-Sensitivity Early Pregnancy Test Twin Pack">High-Sensitivity Early Pregnancy Test Twin Pack ($5.00)</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Quantity</label>
                            <input type="number" name="quantity" value="1" min="1" max="5" class="w-full rounded-lg border p-2 text-sm dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">SMS PIN Contact Number *</label>
                            <input type="text" name="contact_phone" placeholder="0771234567" required class="w-full rounded-lg border p-2 text-sm dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Drop-off Landmark / Community Pickup Point *</label>
                        <input type="text" name="dropoff_landmark" placeholder="e.g. Chitungwiza Unit L Shopping Centre Gate 2" required class="w-full rounded-lg border p-2 text-sm dark:bg-gray-700 dark:text-white">
                    </div>

                    <div class="p-3 bg-green-50 border border-green-200 rounded-lg text-xs text-green-900">
                        <i class="fas fa-check-circle mr-1"></i> <strong>Discreet Packaging Flag Locked ON:</strong> Unmarked plain parcel + 4-digit courier PIN verification.
                    </div>

                    <button type="submit" class="w-full bg-purple-700 hover:bg-purple-800 text-white font-bold py-2.5 px-4 rounded-lg shadow text-sm">
                        <i class="fas fa-shipping-fast mr-1.5"></i> Dispatch Discreet Unmarked Order
                    </button>
                </form>
            </div>
        </div>

        {{-- TAB 3: MATERNAL MENTAL HEALTH SCREENING (EPDS) --}}
        <div x-show="activeTab === 'epds'" class="grid grid-cols-1 lg:grid-cols-3 gap-8" style="display: none;">
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-pink-200 dark:border-gray-700">
                <h2 class="text-lg font-bold text-pink-800 dark:text-pink-300 mb-1">
                    <i class="fas fa-brain mr-2"></i> {{ __('femtech.epds_title') }}
                </h2>
                <p class="text-xs text-gray-600 dark:text-gray-300 mb-6">{{ __('femtech.epds_intro') }}</p>

                <form action="{{ route('femtech.epds.submit') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="locale" value="{{ $locale }}">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Perinatal Stage</label>
                        <select name="perinatal_stage" class="w-full rounded-lg border p-2 text-sm dark:bg-gray-700 dark:text-white">
                            <option value="antenatal">Antenatal (Currently Pregnant)</option>
                            <option value="postpartum_0_6w">Early Postpartum (0 - 6 Weeks After Birth)</option>
                            <option value="postpartum_6w_12m">Extended Postpartum (6 Weeks - 12 Months After Birth)</option>
                        </select>
                    </div>

                    <div class="space-y-3">
                        @foreach($epdsQuestions as $qNum => $qData)
                            <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/40">
                                <label class="block text-xs font-bold text-gray-800 dark:text-white mb-2">
                                    {{ $qNum }}. {{ $qData['question'] }}
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($qData['options'] as $opt)
                                        <label class="flex items-center space-x-2 text-xs cursor-pointer">
                                            <input type="radio" name="responses[{{ $qNum }}]" value="{{ $opt['score'] }}" {{ $opt['score'] === 0 ? 'checked' : '' }} class="text-pink-600">
                                            <span class="text-gray-700 dark:text-gray-200">{{ $opt['label'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="submit" class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 px-4 rounded-lg shadow text-sm">
                        <i class="fas fa-check-double mr-1.5"></i> Calculate Confidential EPDS Screening Score
                    </button>
                </form>
            </div>

            {{-- Psychosocial Support & Certified Mental Health Counselors --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-pink-200 dark:border-gray-700">
                <h3 class="text-base font-bold text-pink-900 dark:text-pink-300 mb-2">
                    <i class="fas fa-hands-helping mr-1.5"></i> Certified Perinatal Mental Health Support
                </h3>
                <p class="text-xs text-gray-600 dark:text-gray-300 mb-4">
                    {{ __('femtech.epds_support_message') }}
                </p>

                <div class="space-y-3">
                    @foreach($supportHotlines as $line)
                        <div class="p-3.5 rounded-xl border border-pink-100 bg-pink-50/70 dark:bg-gray-700 text-xs">
                            <p class="font-bold text-pink-900 dark:text-pink-200">{{ $line['name'] }}</p>
                            <p class="text-gray-600 dark:text-gray-300 mt-0.5">{{ $line['type'] }}</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <span class="bg-white px-2.5 py-1 rounded border font-semibold text-gray-800">
                                    <i class="fas fa-phone-alt text-pink-600 mr-1"></i> {{ $line['phone'] }}
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

        {{-- TAB 4: HL7/FHIR R4 INTEROPERABILITY & OMNICHANNEL LOW-BANDWIDTH GATEWAY --}}
        <div x-show="activeTab === 'fhir'" class="grid grid-cols-1 lg:grid-cols-2 gap-8" style="display: none;">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-indigo-200 dark:border-gray-700">
                <h2 class="text-lg font-bold text-indigo-900 dark:text-indigo-300 mb-2">
                    <i class="fas fa-file-medical-alt mr-2"></i> HL7 / FHIR R4 Diagnostic & Maternal Export
                </h2>
                <p class="text-xs text-gray-600 dark:text-gray-300 mb-4">
                    Standardizes maternal vitals, danger-sign triage conditions, infant EPI immunizations, EPDS mental health scores, and e-prescriptions into HL7 FHIR R4 JSON Bundles (`Patient`, `Observation`, `Condition`, `Immunization`, `MedicationRequest`) for seamless integration with hospital EHRs and DHIS2.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('femtech.fhir.export') }}" target="_blank" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-4 py-2.5 rounded-lg">
                        <i class="fas fa-download mr-1.5"></i> View Full HL7/FHIR R4 Bundle (JSON)
                    </a>
                    <a href="{{ route('femtech.fhir.export', ['anonymize' => 1]) }}" target="_blank" class="bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold px-4 py-2.5 rounded-lg">
                        <i class="fas fa-user-shield mr-1.5"></i> Export De-Identified DHIS2 Bundle
                    </a>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-indigo-200 dark:border-gray-700">
                <h2 class="text-lg font-bold text-indigo-900 dark:text-indigo-300 mb-2">
                    <i class="fas fa-broadcast-tower mr-2"></i> Omnichannel Low-Bandwidth SMS / USSD / WhatsApp Gateway
                </h2>
                <p class="text-xs text-gray-600 dark:text-gray-300 mb-3">
                    Webhook endpoint: <code>POST /api/v1/femtech/webhook/omnichannel</code>
                </p>
                <div class="bg-gray-900 text-green-400 p-3 rounded-lg text-xs font-mono space-y-1">
                    <div># Example 2G SMS / USSD Maternal Triage Command:</div>
                    <div>"TRIAGE 32 HEADACHE VISION BP 155/100"</div>
                    <div class="text-gray-400">-> Flags Pre-eclampsia Risk & routes urgent specialist referral</div>
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
        window.location.replace('https://www.weather.com');
    }
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            triggerQuickExit();
        }
    });
</script>
@endsection

