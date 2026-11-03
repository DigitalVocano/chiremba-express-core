<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BookAppointment;
use App\Models\Patient;
use App\Models\Services;
use App\Models\HealthRecord;
use Illuminate\Support\Facades\Validator;
use App\Models\Banner;

class PatientController extends Controller
{
    public function usersupcomingappointment(Request $request)
    {
        $validator = Validator::make($request->all(), ['user_id' => 'required']);
        if ($validator->fails()) return response()->json(['success' => "0", 'message' => $validator->errors()->first()], 200);

        $data = BookAppointment::with('doctorls')->where("user_id", $request->user_id)
            ->where("date", ">=", date('Y-m-d'))
            ->where("status", "!=", 4) // Not completed
            ->orderBy('date', 'ASC')->paginate(10);

        foreach ($data as $k) {
            $k->doctor_name = $k->doctorls->name ?? "";
            $k->doctor_image = asset('upload/doctors') . '/' . ($k->doctorls->image ?? 'default.png');
            $k->department_name = Services::find($k->doctorls->department_id)->name ?? "";
            $k->doctor_id = $k->doctor_id ?? ($k->doctorls->id ?? null);
            $k->consultation_fee = (string)($k->doctorls->consultation_fee ?? "0");
        }

        return response()->json(['success' => "1", 'data' => $data], 200);
    }

    public function userspastappointment(Request $request)
    {
        $validator = Validator::make($request->all(), ['user_id' => 'required']);
        if ($validator->fails()) return response()->json(['success' => "0", 'message' => $validator->errors()->first()], 200);

        $data = BookAppointment::with('doctorls')
            ->where("user_id", $request->user_id)
            ->orderBy('id', 'DESC')
            ->paginate(10);

        foreach ($data as $k) {
            $doctorName = $k->doctorls->name ?? "Doctor";
            $doctorImage = asset('upload/doctors') . '/' . ($k->doctorls->image ?? 'default.png');
            $doctorAddress = $k->doctorls->address ?? "";
            $departmentName = Services::find($k->doctorls->department_id)->name ?? "";

            $k->name = $doctorName;
            $k->doctor_name = $doctorName;
            $k->image = $doctorImage;
            $k->doctor_image = $doctorImage;
            $k->address = $doctorAddress;
            $k->slot = $k->slot_name ?? ($k->preferred_session ?? "");
            $k->department_name = $departmentName;
            $k->doctor_id = $k->doctor_id ?? ($k->doctorls->id ?? null);
            $k->consultation_fee = (string)($k->doctorls->consultation_fee ?? "0");
        }

        return response()->json(['success' => "1", 'data' => $data], 200);
    }

    public function usereditprofile(Request $request)
    {
        $userId = $request->get('user_id') ?? $request->get('id');
        $user = Patient::find($userId);
        if (!$user) return response()->json(['success' => "0", 'message' => "User not found"], 200);

        if ($request->has('name')) $user->name = $request->name;
        if ($request->has('phone')) $user->phone = $request->phone;
        if ($request->has('email')) $user->email = $request->email;
        if ($request->has('password')) $user->password = bcrypt($request->password);
        
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/profile/'), $filename);
            $user->profile_pic = $filename;
        }
        $user->save();

        return response()->json(['success' => "1", 'status' => 1, 'message' => "Profile Updated", 'data' => $user], 200);
    }

    public function getUserHealthRecords(Request $request)
    {
        $validator = Validator::make($request->all(), ['user_id' => 'required']);
        if ($validator->fails()) return response()->json(['success' => "0", 'message' => $validator->errors()->first()], 200);

        $records = HealthRecord::where('user_id', $request->user_id)->orderBy('created_at', 'DESC')->get();

        return response()->json(['success' => "1", 'data' => $records], 200);
    }

    public function data_list(Request $request)
    {
        $response = array("success" => "0", "register" => "Validation error");
        
        // Fetch Banners
        $banner = Banner::all();
        foreach ($banner as $k) {
            $imgUrl = asset('upload/banner') . '/' . $k->image;
            $k->image = str_replace('/public/upload/', '/upload/', $imgUrl);
        }

        // Fetch Specialties
        $speciality = Services::select('id', 'name', 'icon')->get();
        foreach ($speciality as $s) {
            $iconUrl = asset('upload/services') . '/' . $s->icon;
            $s->icon = str_replace('/public/upload/', '/upload/', $iconUrl);
            $s->total_doctors = \App\Models\Doctors::where("department_id", $s->id)->where('profile_type', 1)->where('is_approve', 1)->count();
        }

        // Fetch Appointments if user_id is present
        $appointment = [];
        if ($request->has('user_id')) {
            $appointment = BookAppointment::with('doctorls')->where("user_id", $request->user_id)
                ->where("date", ">=", date('Y-m-d'))
                ->where("status", "!=", 4)
                ->orderBy('date', 'ASC')->get();
        }

        $response['success'] = "1";
        $response['register'] = "Data List";
        $response['data'] = array("banner" => $banner, "speciality" => $speciality, "appointment" => $appointment);
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    public function reportspam(Request $request)
    {
        $userId = $request->get('user_id');
        $title = $request->get('title');
        $description = $request->get('description');

        // Log or save issue/spam report if model exists
        \Illuminate\Support\Facades\Log::info("Spam report submitted by user {$userId}: {$title} - {$description}");
        try {
            $report = new \App\Models\Reportspam();
            $report->user_id = $userId;
            $report->title = $title ?: 'General Issue';
            $report->description = $description ?: '';
            $report->save();
        } catch (\Exception $e) {}
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('mobile_diagnostic_logs')) {
                $patient = $userId ? Patient::find($userId) : null;
                \App\Models\MobileDiagnosticLog::create([
                    'category' => 'bug_report',
                    'level' => 'error',
                    'platform' => $request->get('platform', 'mobile'),
                    'os_version' => $request->get('os_version'),
                    'device_model' => $request->get('device_model'),
                    'app_version' => $request->get('app_version'),
                    'user_role' => $request->get('user_role', 'patient'),
                    'user_id' => $userId,
                    'user_name' => $patient ? $patient->name : $request->get('user_name'),
                    'fcm_token' => $request->get('fcm_token'),
                    'apns_token' => $request->get('apns_token'),
                    'permission_status' => $request->get('permission_status'),
                    'title' => $title ?: 'User Bug Report',
                    'message' => $description ?: '',
                    'metadata' => $request->get('metadata'),
                    'ip_address' => $request->ip(),
                ]);
            }
        } catch (\Exception $e) {}

        return response()->json([
            'success' => "1",
            'status' => 1,
            'register' => 'Issue reported successfully. Our support team will investigate.',
            'message' => 'Issue reported successfully'
        ], 200);
    }

    public function getWallet(Request $request)
    {
        $validator = Validator::make($request->all(), ['user_id' => 'required|exists:patient,id']);
        if ($validator->fails()) {
            return response()->json(['success' => "0", 'message' => $validator->errors()->first()], 400);
        }

        $patient = Patient::with('wallet.transactions')->find($request->user_id);

        if (!$patient->wallet) {
            $patient->wallet()->create(['balance' => 0.00, 'ai_credits' => 0]);
            $patient->load('wallet.transactions');
        }

        return response()->json([
            'success' => "1",
            'balance' => $patient->wallet->balance,
            'transactions' => $patient->wallet->transactions()->latest()->get()
        ], 200);
    }

    public function getPatientDetail(Request $request)
    {
        $userId = $request->get('user_id');
        if (!$userId) {
            return response()->json(['success' => "0", 'message' => 'user_id required'], 400);
        }
        $patient = Patient::find($userId);
        if (!$patient) {
            return response()->json(['success' => "0", 'message' => 'Patient not found'], 404);
        }

        $image = $patient->profile_pic;
        $imageUrl = '';
        if ($image && $image != 'default.png') {
            $imageUrl = asset('upload/profile') . '/' . $image;
        }

        return response()->json([
            'success' => "1",
            'data' => [
                'id' => $patient->id,
                'name' => $patient->name,
                'email' => $patient->email,
                'phone' => $patient->phone,
                'profile_pic' => $patient->profile_pic,
                'image_url' => $imageUrl,
            ]
        ], 200);
    }
}
