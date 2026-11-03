<?php

namespace App\Services;

use App\Models\BookAppointment;
use App\Models\ChildProfile;
use App\Models\ChildVaccineMilestone;
use App\Models\Doctors;
use App\Models\MaternalProfile;
use App\Models\MaternalTriageLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MaternalTriageService
{
    /**
     * Evaluate gestational age, maternal danger-sign symptoms, and vitals against
     * WHO Antenatal Care (ANC) clinical guidelines.
     *
     * @param array $input
     * @return array
     */
    public function evaluateDangerSigns(array $input): array
    {
        $weeks = (int) ($input['gestational_weeks'] ?? 20);
        $symptoms = array_values(array_unique((array) ($input['symptoms'] ?? [])));
        $systolic = isset($input['systolic_bp']) && $input['systolic_bp'] !== '' ? (int) $input['systolic_bp'] : null;
        $diastolic = isset($input['diastolic_bp']) && $input['diastolic_bp'] !== '' ? (int) $input['diastolic_bp'] : null;
        $temp = isset($input['temperature']) && $input['temperature'] !== '' ? (float) $input['temperature'] : null;
        $glucose = isset($input['blood_glucose_mmol']) && $input['blood_glucose_mmol'] !== '' ? (float) $input['blood_glucose_mmol'] : null;
        $locale = $input['locale'] ?? App::getLocale() ?: 'en';

        $flaggedConditions = [];
        $riskLevel = 'low';
        $urgentReferral = false;

        $hasHeadache = in_array('severe_headache', $symptoms, true);
        $hasBlurredVision = in_array('blurred_vision', $symptoms, true);
        $hasSwelling = in_array('sudden_swelling', $symptoms, true);
        $hasBleeding = in_array('vaginal_bleeding', $symptoms, true);
        $hasAbdominalPain = in_array('severe_abdominal_pain', $symptoms, true);
        $hasReducedMovement = in_array('reduced_fetal_movement', $symptoms, true);
        $hasFever = in_array('high_fever', $symptoms, true);
        $hasConvulsions = in_array('convulsions', $symptoms, true);
        $hasWaterBreaking = in_array('water_breaking_early', $symptoms, true);

        // 1. Eclampsia / Severe Pre-eclampsia Rule (WHO Maternal Danger Priority 1)
        $isSevereBp = ($systolic !== null && $systolic >= 160) || ($diastolic !== null && $diastolic >= 110);
        $isElevatedBp = ($systolic !== null && $systolic >= 140) || ($diastolic !== null && $diastolic >= 90);
        $preeclampsiaSymptomCount = ($hasHeadache ? 1 : 0) + ($hasBlurredVision ? 1 : 0) + ($hasSwelling ? 1 : 0);

        if ($hasConvulsions) {
            $flaggedConditions[] = 'Eclampsia / Maternal Seizure Emergency';
            $riskLevel = 'critical';
            $urgentReferral = true;
        } elseif ($weeks >= 20 && ($isSevereBp || ($isElevatedBp && $preeclampsiaSymptomCount >= 1) || $preeclampsiaSymptomCount >= 2)) {
            $flaggedConditions[] = 'Pre-eclampsia Risk (Hypertensive Disorder of Pregnancy)';
            $riskLevel = $isSevereBp || ($hasHeadache && $hasBlurredVision) ? 'critical' : 'high';
            $urgentReferral = true;
        } elseif ($isElevatedBp) {
            $flaggedConditions[] = 'Gestational / Antenatal Hypertension';
            $riskLevel = $this->escalateRisk($riskLevel, 'high');
            $urgentReferral = true;
        } elseif ($preeclampsiaSymptomCount === 1 && $weeks >= 20) {
            $flaggedConditions[] = 'Possible Hypertensive Warning Sign (Monitor BP)';
            $riskLevel = $this->escalateRisk($riskLevel, 'moderate');
        }

        // 2. Haemorrhage / Miscarriage / Ectopic / Placental Abruption Rule
        if ($hasBleeding) {
            if ($weeks < 20) {
                $flaggedConditions[] = $hasAbdominalPain
                    ? 'Threatened Miscarriage or Ectopic Pregnancy Risk'
                    : 'Early Pregnancy Threatened Miscarriage Risk';
                $riskLevel = $this->escalateRisk($riskLevel, $hasAbdominalPain ? 'critical' : 'high');
            } else {
                $flaggedConditions[] = $hasAbdominalPain
                    ? 'Antepartum Haemorrhage / Placental Abruption Risk'
                    : 'Antepartum Haemorrhage (Placenta Praevia / Spotting Risk)';
                $riskLevel = $this->escalateRisk($riskLevel, 'critical');
            }
            $urgentReferral = true;
        } elseif ($hasAbdominalPain) {
            $flaggedConditions[] = 'Acute Maternal Abdominal / Epigastric Pain';
            $riskLevel = $this->escalateRisk($riskLevel, 'high');
            $urgentReferral = true;
        }

        // 3. Fetal Compromise Rule
        if ($hasReducedMovement && $weeks >= 20) {
            $flaggedConditions[] = 'Reduced Fetal Movement (Fetal Distress Risk)';
            $riskLevel = $this->escalateRisk($riskLevel, 'high');
            $urgentReferral = true;
        }

        // 4. Preterm Premature Rupture of Membranes (PPROM)
        if ($hasWaterBreaking && $weeks < 37) {
            $flaggedConditions[] = 'Preterm Premature Rupture of Membranes (PPROM)';
            $riskLevel = $this->escalateRisk($riskLevel, 'critical');
            $urgentReferral = true;
        }

        // 5. Maternal Fever / Sepsis / Malaria in Pregnancy Rule
        if ($hasFever || ($temp !== null && $temp >= 38.0)) {
            $flaggedConditions[] = 'Maternal Pyrexia / Systemic Infection Risk';
            $riskLevel = $this->escalateRisk($riskLevel, 'high');
            $urgentReferral = true;
        }

        // 6. Hyperglycemia / Gestational Diabetes Rule
        if ($glucose !== null && $glucose >= 7.8) {
            $flaggedConditions[] = 'Gestational Hyperglycemia / Diabetes Risk';
            $riskLevel = $this->escalateRisk($riskLevel, 'moderate');
        }

        App::setLocale($locale);
        if ($urgentReferral) {
            $guidance = __('femtech.triage_urgent_referral') . ' Flagged: ' . implode(', ', $flaggedConditions) . '.';
        } elseif ($riskLevel === 'moderate') {
            $guidance = __('femtech.triage_moderate_warning') . ' Flagged: ' . implode(', ', $flaggedConditions) . '.';
        } else {
            $guidance = __('femtech.triage_low_reassurance');
        }

        return [
            'gestational_weeks' => $weeks,
            'symptoms' => $symptoms,
            'systolic_bp' => $systolic,
            'diastolic_bp' => $diastolic,
            'temperature' => $temp,
            'blood_glucose_mmol' => $glucose,
            'risk_level' => $riskLevel,
            'flagged_conditions' => $flaggedConditions,
            'clinical_guidance' => $guidance,
            'urgent_referral_triggered' => $urgentReferral,
            'locale' => $locale,
        ];
    }

    /**
     * Record a maternal triage assessment and automatically create an urgent referral
     * appointment if danger signs are flagged.
     */
    public function recordTriageAndRoute(array $input): MaternalTriageLog
    {
        $evaluation = $this->evaluateDangerSigns($input);
        $referralAppointmentId = null;

        if ($evaluation['urgent_referral_triggered'] && !empty($input['patient_id'])) {
            $specialist = Doctors::where('profile_type', '1')->orderBy('id')->first();
            if ($specialist) {
                try {
                    $appointment = new BookAppointment();
                    $appointment->doctor_id = $specialist->id;
                    $appointment->user_id = $input['patient_id'];
                    if (Schema::hasColumn('book_appointment', 'name')) {
                        $appointment->name = $input['patient_name'] ?? 'Urgent Maternal Triage Patient';
                    }
                    if (Schema::hasColumn('book_appointment', 'phone')) {
                        $appointment->phone = $input['patient_phone'] ?? '';
                    }
                    $appointment->date = Carbon::today()->format('Y-m-d');
                    if (Schema::hasColumn('book_appointment', 'slot_name')) {
                        $appointment->slot_name = 'URGENT MATERNAL TRIAGE REFERRAL';
                    }
                    if (Schema::hasColumn('book_appointment', 'user_description')) {
                        $appointment->user_description = '[URGENT MATERNAL DANGER SIGN] Weeks: ' . $evaluation['gestational_weeks']
                            . ' | Conditions: ' . implode(', ', $evaluation['flagged_conditions']);
                    }
                    if (Schema::hasColumn('book_appointment', 'status')) {
                        $appointment->status = '1';
                    }
                    $appointment->save();

                    $referralAppointmentId = $appointment->id;
                } catch (\Throwable $e) {
                    Log::warning('Urgent referral appointment fallback: ' . $e->getMessage());
                }
            }
        }

        if (!empty($input['maternal_profile_id'])) {
            MaternalProfile::where('id', $input['maternal_profile_id'])->update([
                'gestational_weeks' => $evaluation['gestational_weeks'],
                'high_risk_flag' => $evaluation['urgent_referral_triggered'],
                'risk_factors' => json_encode($evaluation['flagged_conditions']),
            ]);
        }

        return MaternalTriageLog::create([
            'patient_id' => $input['patient_id'] ?? null,
            'maternal_profile_id' => $input['maternal_profile_id'] ?? null,
            'recorded_by_chw_id' => $input['recorded_by_chw_id'] ?? null,
            'anonymous_session_token' => $input['anonymous_session_token'] ?? null,
            'gestational_weeks' => $evaluation['gestational_weeks'],
            'symptoms' => $evaluation['symptoms'],
            'systolic_bp' => $evaluation['systolic_bp'],
            'diastolic_bp' => $evaluation['diastolic_bp'],
            'temperature' => $evaluation['temperature'],
            'weight_kg' => $input['weight_kg'] ?? null,
            'blood_glucose_mmol' => $evaluation['blood_glucose_mmol'],
            'risk_level' => $evaluation['risk_level'],
            'flagged_conditions' => $evaluation['flagged_conditions'],
            'clinical_guidance' => $evaluation['clinical_guidance'],
            'urgent_referral_triggered' => $evaluation['urgent_referral_triggered'],
            'referral_appointment_id' => $referralAppointmentId,
            'channel' => $input['channel'] ?? 'web',
            'locale' => $evaluation['locale'],
        ]);
    }

    /**
     * Generate the WHO / Zimbabwe EPI First 1,000 Days Immunization & Nutrition Schedule
     * for a registered infant.
     */
    public function generateFirst1000DaysSchedule(ChildProfile $child): array
    {
        $dob = Carbon::parse($child->date_of_birth);
        $today = Carbon::today();

        $epiMilestones = [
            ['code' => 'BCG', 'name' => 'BCG (Tuberculosis Birth Dose)', 'weeks' => 0, 'nutrition' => 'Initiate exclusive breastfeeding within 1 hour of birth (Colostrum).'],
            ['code' => 'OPV_0', 'name' => 'Oral Polio Vaccine (OPV 0 Birth Dose)', 'weeks' => 0, 'nutrition' => 'Exclusive breastfeeding on demand day and night.'],
            ['code' => 'PENTA_1', 'name' => 'Pentavalent 1 (DTP-HepB-Hib) + OPV 1 + PCV 1 + Rotavirus 1', 'weeks' => 6, 'nutrition' => '6-Week Postnatal Check: Continue exclusive breastfeeding; check infant weight gain.'],
            ['code' => 'PENTA_2', 'name' => 'Pentavalent 2 + OPV 2 + PCV 2 + Rotavirus 2', 'weeks' => 10, 'nutrition' => '10-Week Growth Check: Exclusive breastfeeding only (no water or porridge yet).'],
            ['code' => 'PENTA_3', 'name' => 'Pentavalent 3 + OPV 3 + PCV 3 + IPV', 'weeks' => 14, 'nutrition' => '14-Week Growth Check: Monitor weight-for-age curve.'],
            ['code' => 'VITAMIN_A_6M', 'name' => 'Vitamin A Supplementation (100,000 IU) & 6-Month Nutrition Check', 'weeks' => 26, 'nutrition' => 'Introduce nutrient-dense complementary foods (fortified Mahewu/porridge, mashed protein/iron-rich foods) while continuing breastfeeding. Perform MUAC screening.'],
            ['code' => 'MEASLES_RUBELLA_1', 'name' => 'Measles-Rubella 1 (MR 1) + Typhoid Conjugate Vaccine (TCV)', 'weeks' => 39, 'nutrition' => '9-Month Check: 3-4 nutrient-rich meals + 1-2 snacks daily alongside breastfeeding.'],
            ['code' => 'VITAMIN_A_12M', 'name' => '12-Month Vitamin A (200,000 IU) & De-worming Check-in', 'weeks' => 52, 'nutrition' => '1-Year Milestone: Diverse family diet, iron/vitamin A rich foods, MUAC & height check.'],
            ['code' => 'MEASLES_RUBELLA_2', 'name' => 'Measles-Rubella 2 (MR 2) + DTP Booster (18 Months)', 'weeks' => 78, 'nutrition' => '18-Month Check: Continue breastfeeding up to 2 years and beyond; monitor cognitive & motor milestones.'],
        ];

        $created = [];
        foreach ($epiMilestones as $item) {
            $dueDate = $dob->copy()->addWeeks($item['weeks']);
            $status = 'upcoming';
            if ($dueDate->isPast() && !$dueDate->isToday()) {
                $status = 'due';
            } elseif ($dueDate->isToday()) {
                $status = 'due';
            }

            $milestone = ChildVaccineMilestone::firstOrCreate(
                [
                    'child_profile_id' => $child->id,
                    'vaccine_code' => $item['code'],
                ],
                [
                    'vaccine_name' => $item['name'],
                    'target_age_weeks' => $item['weeks'],
                    'due_date' => $dueDate->format('Y-m-d'),
                    'status' => $status,
                    'nutrition_checkin_notes' => $item['nutrition'],
                ]
            );
            $created[] = $milestone;
        }

        return $created;
    }

    /**
     * Evaluate child Mid-Upper Arm Circumference (MUAC) against WHO Malnutrition standards.
     */
    public function evaluateChildNutrition(?float $muacCm): array
    {
        if ($muacCm === null) {
            return ['status' => 'unrecorded', 'message' => 'Record MUAC (cm) for infants aged 6-59 months.'];
        }

        if ($muacCm < 11.5) {
            return [
                'status' => 'SAM',
                'risk_level' => 'critical',
                'message' => __('femtech.nutrition_sam'),
            ];
        }

        if ($muacCm < 12.5) {
            return [
                'status' => 'MAM',
                'risk_level' => 'high',
                'message' => __('femtech.nutrition_mam'),
            ];
        }

        return [
            'status' => 'NORMAL',
            'risk_level' => 'low',
            'message' => __('femtech.nutrition_normal'),
        ];
    }

    /**
     * Dispatch an automated SMS/WhatsApp immunization reminder for due milestones.
     */
    public function sendImmunizationReminder(ChildVaccineMilestone $milestone): bool
    {
        $child = $milestone->childProfile;
        if (!$child) {
            return false;
        }

        $phone = $child->guardian_phone ?: optional($child->mother)->phone;
        $message = "Chiremba First 1,000 Days Reminder: {$child->child_name} is due for {$milestone->vaccine_name} (Scheduled: {$milestone->due_date->format('d M Y')}). Nutrition tip: {$milestone->nutrition_checkin_notes}";

        $sent = false;
        if (!empty($phone)) {
            try {
                $sent = app(ZimflowService::class)->sendSessionMessage($phone, $message);
            } catch (\Throwable $e) {
                Log::warning("Immunization reminder external dispatch skipped: " . $e->getMessage());
            }
        }

        $milestone->reminder_sent_at = now();
        $milestone->save();

        return $sent;
    }

    private function escalateRisk(string $current, string $candidate): string
    {
        $weights = ['low' => 1, 'moderate' => 2, 'high' => 3, 'critical' => 4];
        return ($weights[$candidate] ?? 1) > ($weights[$current] ?? 1) ? $candidate : $current;
    }
}

