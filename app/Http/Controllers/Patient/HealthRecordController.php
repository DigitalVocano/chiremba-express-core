<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Models\Patient;
use App\Models\Setting;
use App\Models\HealthRecord;
use App\Models\MedicalRecord;
use App\Models\BookAppointment;
use App\Models\AppointmentMedicines;
use App\Models\Medicines;
use App\Models\ApImdUplod;
use PDF;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class HealthRecordController extends Controller
{
    public function addHealthRecordManual(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'blood_pressure' => 'nullable|string',
            'weight' => 'nullable|numeric',
            'temperature' => 'nullable|numeric',
            'blood_glucose_level' => 'nullable|integer',
            'patient_id' => 'required|exists:patient,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $healthRecord = new HealthRecord();
        $healthRecord->patient_id = $request->input('patient_id');
        $healthRecord->date = $request->input('date');
        $healthRecord->blood_pressure = $request->input('blood_pressure');
        $healthRecord->weight = $request->input('weight');
        $healthRecord->temperature = $request->input('temperature');
        $healthRecord->blood_glucose_level = $request->input('blood_glucose_level');
        $healthRecord->save();

        return response()->json(['message' => 'Health record added successfully', 'data' => $healthRecord], 201);
    }

    public function addHealthRecordSmartwatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'heart_rate' => 'nullable|integer',
            'sleep_data' => 'nullable|json',
            'steps' => 'nullable|integer',
            'patient_id' => 'required|exists:patient,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $healthRecord = new HealthRecord();
        $healthRecord->patient_id = $request->input('patient_id');
        $healthRecord->date = $request->input('date');
        $healthRecord->heart_rate = $request->input('heart_rate');
        $healthRecord->sleep_data = $request->input('sleep_data');
        $healthRecord->steps = $request->input('steps');
        $healthRecord->save();

        return response()->json(['message' => 'Health record added successfully', 'data' => $healthRecord], 201);
    }

    public function addHealthRecord(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'blood_pressure' => 'nullable|string',
            'weight' => 'nullable|numeric',
        ]);

        $setting = Setting::first();
        if ($setting && $setting->is_patient_subscription_enabled) {
            $patient = Patient::find(Session::get('user_id'));
            if ($patient && $patient->hasFeature('limit_health_data')) {
                $limit = (int) $patient->getFeatureValue('limit_health_data', 0);
                $currentCount = HealthRecord::where('patient_id', $patient->id)->count();

                if ($limit > 0 && $currentCount >= $limit) {
                    return redirect()->route('health.tracking')->with('error', __('You have reached the maximum number of health data entries allowed by your subscription plan.'));
                }
            }
        }

        HealthRecord::create([
            'patient_id' => Session::get('user_id'),
            'date' => $request->date,
            'blood_pressure' => $request->blood_pressure,
            'weight' => $request->weight,
            'temperature' => $request->temperature,
            'glucose_level' => $request->glucose_level,
        ]);

        return redirect()->route('health.tracking')->with('success', 'Health record added successfully!');
    }

    public function showHealthTracking(Request $request)
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '1') {
            $setting = Setting::find(1);
            $userdata = Patient::find(Session::get("user_id"));

            $healthRecords = HealthRecord::where('patient_id', $userdata->id);
            if ($request->has('date_filter')) {
                $healthRecords->where('date', $request->date_filter);
            }
            if ($request->has('type_filter')) {
                $healthRecords->whereNotNull($request->type_filter);
            }

            $perPage = $request->has('total_filter') ? $request->total_filter : 10;
            $healthRecords = $healthRecords->orderBy('date', 'desc')->paginate($perPage);

            return view("user.patient.health-tracking", compact('setting', 'userdata', 'healthRecords'));
        } else {
            return redirect()->route('login');
        }
    }

    public function exportHealthRecordsToPDF(Request $request)
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '1') {
            $user = Patient::find(Session::get("user_id"));
            $healthRecords = HealthRecord::where('patient_id', $user->id)->get();

            $html = '<table style="border-collapse: collapse; width: 100%;"><thead><tr>';
            $html .= '<th style="border: 1px solid black; padding: 8px;">' . __('Date') . '</th>';
            $html .= '<th style="border: 1px solid black; padding: 8px;">' . __('Blood Pressure') . '</th>';
            $html .= '<th style="border: 1px solid black; padding: 8px;">' . __('Weight') . '</th>';
            $html .= '<th style="border: 1px solid black; padding: 8px;">' . __('Temperature') . '</th>';
            $html .= '<th style="border: 1px solid black; padding: 8px;">' . __('Glucose Level') . '</th>';
            $html .= '</tr></thead><tbody>';

            foreach ($healthRecords as $record) {
                $html .= '<tr>';
                $html .= '<td style="border: 1px solid black; padding: 8px;">' . $record->date->format('Y-m-d') . '</td>';
                $html .= '<td style="border: 1px solid black; padding: 8px;">' . $record->blood_pressure . '</td>';
                $html .= '<td style="border: 1px solid black; padding: 8px;">' . $record->weight . '</td>';
                $html .= '<td style="border: 1px solid black; padding: 8px;">' . $record->temperature . '</td>';
                $html .= '<td style="border: 1px solid black; padding: 8px;">' . $record->glucose_level . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';

            $pdf = PDF::loadHTML($html);
            $pdf->setPaper('A4', 'landscape');
            return $pdf->stream('health_records.pdf');
        } else {
            return redirect()->route('login');
        }
    }

    public function showMedicalRecordsWallet(Request $request)
    {
        $userId = Session::get("user_id") ?: $request->input('user_id');

        if ($userId) {
            $setting = Setting::find(1);
            $userdata = Patient::find($userId);
            $medicalRecords = MedicalRecord::where('patient_id', $userdata->id)->get();
            $bookdata = BookAppointment::with('doctorls')->where("user_id", $userId)->get();
            
            if ($request->wantsJson()) {
                foreach ($medicalRecords as $m) {
                    $path = $m->document_path ?? '';
                    if (!empty($path) && !str_starts_with($path, 'http')) {
                        $path = ltrim($path, '/');
                        if (!str_starts_with($path, 'upload/')) {
                            $path = 'upload/' . $path;
                        }
                        $m->document_url = asset($path);
                        $m->document_path = asset($path);
                    } else {
                        $m->document_url = $path;
                    }
                }
                return response()->json(['success' => "1", 'data' => $medicalRecords, 'appointments' => $bookdata], 200);
            }

            return view("user.patient.medical-records-wallet", [
                "setting" => $setting,
                "userdata" => $userdata,
                "medicalRecords" => $medicalRecords,
                "bookdata" => $bookdata,
            ]);
        } else {
            return redirect()->route('login');
        }
    }

    public function uploadMedicalRecord(Request $request)
    {
        $request->validate([
            'document_name' => 'required_if:record_type,image,pdf,e-prescription',
            'document' => 'required_if:record_type,image,pdf',
            'record_type' => 'required',
            'appointment_id' => 'required_if:record_type,appointment,e-prescription',
            'visibility' => 'required',
        ]);

        $userId = Session::get("user_id") ?: $request->input('user_id');

        if ($userId) {
            $setting = Setting::first();
            if ($setting && $setting->is_patient_subscription_enabled) {
                $patient = Patient::find($userId);
                if ($patient && $patient->hasFeature('limit_medical_records')) {
                    $limit = (int) $patient->getFeatureValue('limit_medical_records', 0);
                    $currentCount = MedicalRecord::where('patient_id', $patient->id)->count();
                    if ($limit > 0 && $currentCount >= $limit) {
                        return redirect()->back()->with('error', __('You have reached the maximum number of medical records allowed.'));
                    }
                }
            }

            $recordType = $request->input('record_type');

            if ($recordType === 'appointment') {
                $appointment = BookAppointment::find($request->input('appointment_id'));
                if ($appointment) {
                    $documentName = $appointment->doctorls->name . ' - ' . $appointment->date . ' ' . $appointment->slot_name;
                    $documentPath = $appointment->prescription_file;
                    $uploadFolder = 'upload/prescription/';

                    if (empty($documentPath)) {
                        $otherFile = ApImdUplod::where('appointment_id', $appointment->id)->latest()->first();
                        if ($otherFile) {
                            $documentPath = $otherFile->image;
                            $uploadFolder = 'upload/ap_imd_up/';
                        }
                    }

                    if (empty($documentPath)) {
                        return redirect()->back()->with('error', 'No prescription or report file found.');
                    }
                } else {
                    return redirect()->back()->with('error', 'Appointment not found.');
                }

                MedicalRecord::create([
                    'patient_id' => $userId,
                    'document_name' => $documentName,
                    'document_path' => $uploadFolder . $documentPath,
                    'record_type' => $recordType,
                    'visibility' => $request->input('visibility'),
                ]);
                
                if ($request->wantsJson()) {
                    return response()->json(['success' => "1", 'message' => 'Appointment file uploaded successfully!'], 200);
                }
                return redirect()->route('medical.records.wallet')->with('success', 'Appointment file uploaded successfully!');
            }
            
            // Logic for custom uploads (Image/PDF)
            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('upload/medical_records/'), $filename);

                MedicalRecord::create([
                    'patient_id' => $userId,
                    'document_name' => $request->input('document_name'),
                    'document_path' => 'upload/medical_records/' . $filename,
                    'record_type' => $recordType,
                    'visibility' => $request->input('visibility'),
                ]);
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => "1", 'message' => 'Document uploaded successfully!'], 200);
            }
            return redirect()->route('medical.records.wallet')->with('success', 'Document uploaded successfully!');
        } else {
            if ($request->wantsJson()) {
                return response()->json(['success' => "0", 'message' => 'Unauthorized'], 401);
            }
            return redirect()->route('login');
        }
    }

    /**
     * Dedicated method to render the Patient History for the Doctor's Mobile App WebView.
     * Bypasses web session checks because the Flutter WebView operates independently of the API tokens.
     */
    public function doctorViewPatientHistory(Request $request, $id)
    {
        $setting = Setting::find(1);
        $userdata = Patient::find($id);
        if(!$userdata){
             return response("Patient not found", 404);
        }
        $medicalRecords = MedicalRecord::where('patient_id', $userdata->id)->get();
        $bookdata = BookAppointment::with('doctorls')->where("user_id", $id)->get();
        
        return view("user.patient.medical-records-wallet", [
            "setting" => $setting,
            "userdata" => $userdata,
            "medicalRecords" => $medicalRecords,
            "bookdata" => $bookdata,
            "is_doctor_view" => true // Useful flag if you want to hide buttons in the blade file later
        ]);
    }

    public function deleteMedicalRecord(Request $request, $id = null)
    {
        $recordId = $id ?: $request->input('id');
        $record = MedicalRecord::find($recordId);
        if (!$record) {
            if ($request->wantsJson()) {
                return response()->json(['success' => "0", 'message' => 'Record not found'], 404);
            }
            return redirect()->back()->with('error', 'Record not found');
        }
        if ($record->document_path && file_exists(public_path($record->document_path))) {
            @unlink(public_path($record->document_path));
        }
        $record->delete();
        if ($request->wantsJson()) {
            return response()->json(['success' => "1", 'message' => 'Record deleted successfully'], 200);
        }
        return redirect()->back()->with('success', 'Record deleted successfully');
    }

    public function updateMedicalRecord(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'document_name' => 'required',
        ]);
        $record = MedicalRecord::find($request->id);
        if (!$record) {
            return response()->json(['success' => "0", 'message' => 'Record not found'], 404);
        }
        $record->document_name = $request->document_name;
        if ($request->has('record_type')) {
            $record->record_type = $request->record_type;
        }
        if ($request->has('visibility')) {
            $record->visibility = $request->visibility;
        }
        $record->save();
        return response()->json(['success' => "1", 'message' => 'Record updated successfully', 'data' => $record], 200);
    }
}