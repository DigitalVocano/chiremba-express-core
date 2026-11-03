<?php

namespace App\Services;

use App\Models\BookAppointment;
use App\Models\ChildProfile;
use App\Models\EpdsScreening;
use App\Models\HealthRecord;
use App\Models\MaternalTriageLog;
use App\Models\Patient;
use Illuminate\Support\Str;

class FhirExportService
{
    /**
     * Export a patient's maternal vitals, danger-sign triage logs, EPDS mental health screenings,
     * child immunizations, and e-prescriptions as an HL7 FHIR R4 (v4.0.1) Bundle JSON document
     * compatible with hospital EHRs and DHIS2 interoperability layers.
     */
    public function generatePatientFhirBundle(Patient $patient, bool $anonymize = false): array
    {
        $patientUuid = 'patient-' . ($anonymize ? substr(hash('sha256', (string) $patient->id), 0, 12) : $patient->id);
        $entries = [];

        // 1. FHIR Patient Resource
        $entries[] = [
            'fullUrl' => "urn:uuid:{$patientUuid}",
            'resource' => [
                'resourceType' => 'Patient',
                'id' => $patientUuid,
                'meta' => [
                    'profile' => ['http://hl7.org/fhir/StructureDefinition/Patient'],
                ],
                'identifier' => [
                    [
                        'system' => 'https://chiremba.co.zw/fhir/patient-id',
                        'value' => $patientUuid,
                    ],
                ],
                'active' => true,
                'name' => [
                    [
                        'use' => $anonymize ? 'anonymous' : 'official',
                        'text' => $anonymize ? 'Anonymized Maternal Patient' : $patient->name,
                    ],
                ],
                'telecom' => $anonymize ? [] : array_values(array_filter([
                    $patient->phone ? ['system' => 'phone', 'value' => $patient->phone, 'use' => 'mobile'] : null,
                    $patient->email ? ['system' => 'email', 'value' => $patient->email] : null,
                ])),
                'gender' => 'female',
            ],
        ];

        // 2. FHIR Observation Resources from Maternal Vitals Log (HealthRecord)
        $vitals = HealthRecord::where('patient_id', $patient->id)->orderBy('date', 'desc')->limit(25)->get();
        foreach ($vitals as $vital) {
            $obsId = 'obs-vital-' . $vital->id;
            $effectiveDate = $vital->date ? $vital->date->format('Y-m-d') : now()->format('Y-m-d');

            // Parse Blood Pressure (either from systolic/diastolic fields or "120/80" string)
            $sys = $vital->blood_pressure_systolic;
            $dia = $vital->blood_pressure_diastolic;
            if ((!$sys || !$dia) && !empty($vital->blood_pressure) && strpos($vital->blood_pressure, '/') !== false) {
                [$parsedSys, $parsedDia] = array_map('intval', explode('/', $vital->blood_pressure, 2));
                $sys = $sys ?: $parsedSys;
                $dia = $dia ?: $parsedDia;
            }

            if ($sys && $dia) {
                $entries[] = [
                    'fullUrl' => "urn:uuid:{$obsId}-bp",
                    'resource' => [
                        'resourceType' => 'Observation',
                        'id' => "{$obsId}-bp",
                        'status' => 'final',
                        'category' => [[
                            'coding' => [[
                                'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                                'code' => 'vital-signs',
                                'display' => 'Vital Signs',
                            ]],
                        ]],
                        'code' => [
                            'coding' => [[
                                'system' => 'http://loinc.org',
                                'code' => '85354-9',
                                'display' => 'Blood pressure panel with all children optional',
                            ]],
                        ],
                        'subject' => ['reference' => "Patient/{$patientUuid}"],
                        'effectiveDateTime' => $effectiveDate,
                        'component' => [
                            [
                                'code' => [
                                    'coding' => [[
                                        'system' => 'http://loinc.org',
                                        'code' => '8480-6',
                                        'display' => 'Systolic blood pressure',
                                    ]],
                                ],
                                'valueQuantity' => [
                                    'value' => (int) $sys,
                                    'unit' => 'mmHg',
                                    'system' => 'http://unitsofmeasure.org',
                                    'code' => 'mm[Hg]',
                                ],
                            ],
                            [
                                'code' => [
                                    'coding' => [[
                                        'system' => 'http://loinc.org',
                                        'code' => '8462-4',
                                        'display' => 'Diastolic blood pressure',
                                    ]],
                                ],
                                'valueQuantity' => [
                                    'value' => (int) $dia,
                                    'unit' => 'mmHg',
                                    'system' => 'http://unitsofmeasure.org',
                                    'code' => 'mm[Hg]',
                                ],
                            ],
                        ],
                    ],
                ];
            }

            if ($vital->weight) {
                $entries[] = [
                    'fullUrl' => "urn:uuid:{$obsId}-weight",
                    'resource' => [
                        'resourceType' => 'Observation',
                        'id' => "{$obsId}-weight",
                        'status' => 'final',
                        'code' => [
                            'coding' => [[
                                'system' => 'http://loinc.org',
                                'code' => '29463-7',
                                'display' => 'Body weight',
                            ]],
                        ],
                        'subject' => ['reference' => "Patient/{$patientUuid}"],
                        'effectiveDateTime' => $effectiveDate,
                        'valueQuantity' => [
                            'value' => (float) $vital->weight,
                            'unit' => 'kg',
                            'system' => 'http://unitsofmeasure.org',
                            'code' => 'kg',
                        ],
                    ],
                ];
            }

            $glucose = $vital->glucose_level ?? $vital->blood_glucose_level;
            if ($glucose) {
                $entries[] = [
                    'fullUrl' => "urn:uuid:{$obsId}-glucose",
                    'resource' => [
                        'resourceType' => 'Observation',
                        'id' => "{$obsId}-glucose",
                        'status' => 'final',
                        'code' => [
                            'coding' => [[
                                'system' => 'http://loinc.org',
                                'code' => '2339-0',
                                'display' => 'Glucose [Mass/volume] in Blood',
                            ]],
                        ],
                        'subject' => ['reference' => "Patient/{$patientUuid}"],
                        'effectiveDateTime' => $effectiveDate,
                        'valueQuantity' => [
                            'value' => (float) $glucose,
                            'unit' => 'mmol/L',
                            'system' => 'http://unitsofmeasure.org',
                            'code' => 'mmol/L',
                        ],
                    ],
                ];
            }
        }

        // 3. FHIR Condition / Observation Resources from Maternal Danger-Sign Triage Logs
        $triageLogs = MaternalTriageLog::where('patient_id', $patient->id)->orderBy('created_at', 'desc')->limit(10)->get();
        foreach ($triageLogs as $triage) {
            $triageId = 'cond-triage-' . $triage->id;
            $entries[] = [
                'fullUrl' => "urn:uuid:{$triageId}",
                'resource' => [
                    'resourceType' => 'Condition',
                    'id' => $triageId,
                    'clinicalStatus' => [
                        'coding' => [[
                            'system' => 'http://terminology.hl7.org/CodeSystem/condition-clinical',
                            'code' => 'active',
                        ]],
                    ],
                    'severity' => [
                        'coding' => [[
                            'system' => 'http://snomed.info/sct',
                            'display' => strtoupper($triage->risk_level),
                        ]],
                    ],
                    'code' => [
                        'text' => !empty($triage->flagged_conditions)
                            ? implode('; ', (array) $triage->flagged_conditions)
                            : 'Routine Antenatal Milestone Triage (' . $triage->gestational_weeks . ' weeks)',
                    ],
                    'subject' => ['reference' => "Patient/{$patientUuid}"],
                    'recordedDate' => optional($triage->created_at)->toIso8601String() ?? now()->toIso8601String(),
                    'note' => [
                        ['text' => $triage->clinical_guidance],
                    ],
                ],
            ];
        }

        // 4. FHIR Observation Resources from EPDS Maternal Mental Health Screenings
        $epdsRecords = EpdsScreening::where('patient_id', $patient->id)->orderBy('created_at', 'desc')->limit(5)->get();
        foreach ($epdsRecords as $epds) {
            $epdsId = 'obs-epds-' . $epds->id;
            $entries[] = [
                'fullUrl' => "urn:uuid:{$epdsId}",
                'resource' => [
                    'resourceType' => 'Observation',
                    'id' => $epdsId,
                    'status' => 'final',
                    'code' => [
                        'coding' => [[
                            'system' => 'http://loinc.org',
                            'code' => '75275-8',
                            'display' => 'Edinburgh Postnatal Depression Scale [EPDS] total score',
                        ]],
                    ],
                    'subject' => ['reference' => "Patient/{$patientUuid}"],
                    'effectiveDateTime' => optional($epds->created_at)->toIso8601String() ?? now()->toIso8601String(),
                    'valueInteger' => (int) $epds->total_score,
                    'interpretation' => [[
                        'text' => $epds->risk_severity,
                    ]],
                ],
            ];
        }

        // 5. FHIR MedicationRequest Resources from Consultations & E-Prescriptions
        $appointments = BookAppointment::with(['appointmentMedicines', 'doctorls'])
            ->where('user_id', $patient->id)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        foreach ($appointments as $apt) {
            foreach ($apt->appointmentMedicines as $med) {
                $medId = 'medreq-' . $med->id;
                $entries[] = [
                    'fullUrl' => "urn:uuid:{$medId}",
                    'resource' => [
                        'resourceType' => 'MedicationRequest',
                        'id' => $medId,
                        'status' => 'active',
                        'intent' => 'order',
                        'medicationCodeableConcept' => [
                            'text' => $med->medicine_name ?: ('Medicine ID #' . $med->medicine_id),
                        ],
                        'subject' => ['reference' => "Patient/{$patientUuid}"],
                        'authoredOn' => $apt->date ?: now()->format('Y-m-d'),
                        'requester' => [
                            'display' => optional($apt->doctorls)->name ?: 'Chiremba Medical Practitioner',
                        ],
                        'dosageInstruction' => [
                            [
                                'text' => trim(($med->dosage ?? '') . ' ' . ($med->frequency ?? '') . ' ' . ($med->duration ?? '') . ' ' . ($med->instructions ?? '')),
                            ],
                        ],
                    ],
                ];
            }
        }

        // 6. FHIR Immunization Resources for Children (First 1,000 Days)
        $children = ChildProfile::with('vaccineMilestones')->where('mother_patient_id', $patient->id)->get();
        foreach ($children as $child) {
            foreach ($child->vaccineMilestones as $milestone) {
                $immId = 'imm-' . $milestone->id;
                $entries[] = [
                    'fullUrl' => "urn:uuid:{$immId}",
                    'resource' => [
                        'resourceType' => 'Immunization',
                        'id' => $immId,
                        'status' => $milestone->status === 'completed' ? 'completed' : 'not-done',
                        'vaccineCode' => [
                            'coding' => [[
                                'system' => 'http://hl7.org/fhir/sid/cvx',
                                'code' => $milestone->vaccine_code,
                                'display' => $milestone->vaccine_name,
                            ]],
                        ],
                        'patient' => [
                            'reference' => "Patient/{$patientUuid}",
                            'display' => $anonymize ? 'Infant of ' . $patientUuid : $child->child_name,
                        ],
                        'occurrenceDateTime' => ($milestone->administered_date ?: $milestone->due_date)->format('Y-m-d'),
                    ],
                ];
            }
        }

        return [
            'resourceType' => 'Bundle',
            'id' => 'chiremba-fhir-bundle-' . Str::uuid()->toString(),
            'meta' => [
                'lastUpdated' => now()->toIso8601String(),
                'profile' => ['http://hl7.org/fhir/R4/StructureDefinition/Bundle'],
                'tag' => [
                    [
                        'system' => 'https://chiremba.co.zw/fhir/interoperability',
                        'code' => 'DHIS2-FHIR-R4-MATERNAL-CHILD',
                        'display' => 'UNICEF Femtech Digital Public Good Interoperability Layer',
                    ],
                ],
            ],
            'type' => 'collection',
            'timestamp' => now()->toIso8601String(),
            'total' => count($entries),
            'entry' => $entries,
        ];
    }
}

