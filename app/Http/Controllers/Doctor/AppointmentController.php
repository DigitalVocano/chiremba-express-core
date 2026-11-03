<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use App\Models\Setting;
use App\Models\Doctors;
use App\Models\BookAppointment;
use App\Models\Medicines;
use App\Models\AppointmentMedicines;
use App\Models\ApImdUplod;
use App\Models\Patient;
use App\Models\AiReportDiscussion;
use App\Services\AppointmentService;

class AppointmentController extends Controller
{
    public function doctorappointment(Request $request)
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '2') {
            $setting = Setting::find(1);
            $doctordata = Doctors::find(Session::get("user_id"));
            $query = BookAppointment::with('patientls')->where("doctor_id", Session::get("user_id"));

            if ($request->has('status') && $request->status !== null && $request->status !== '') {
                $query->where('status', $request->status);
            }

            $appointmentdata = $query->orderby("id", "DESC")->paginate(15);
            $medi = Medicines::get();

            return view("user.doctor.appointmentlist", compact('setting', 'doctordata', 'appointmentdata', 'medi'));
        } else {
            return redirect()->route('doctor.login');
        }
    }

    public function changeappointmentdoctor($status, $id, AppointmentService $appointmentService)
    {
        try {
            $appointment = $appointmentService->updateStatus($id, $status);
            try {
                $user = $appointment->patientls;
                $msg = __("Your appointment status has been updated.");
                $user->msg = $msg;
                Mail::send('email.Ordermsg', ['user' => $user], function ($message) use ($user) {
                    $message->to($user->email, $user->name)->subject(__('KumbaCare'));
                });
            } catch (\Exception $e) {}

            Session::flash('message', __('Appointment status updated successfully.'));
            Session::flash('alert-class', 'alert-success');
            return redirect()->back();
        } catch (\Exception $e) {
            Session::flash('message', $e->getMessage());
            Session::flash('alert-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function appointment_detail($id)
    {
        $medi = Medicines::get();
        $setting = Setting::find(1);
        $apoid = $id;
        $app_medicine = AppointmentMedicines::where('appointment_id', $id)->get();
        $appointmentdata = BookAppointment::with('patientls')->find($id);
        $img = ApImdUplod::where('appointment_id', $id)->get();

        return view("user.doctor.prescription", compact('setting', 'medi', 'apoid', 'appointmentdata', 'app_medicine', 'img'));
    }

    public function save_prescription(Request $request)
    {
        if ($request->hasFile('report_img')) {
            $file = $request->file('report_img');
            $extension = $file->getClientOriginalExtension() ?: 'png';
            $picture = time() . '.' . $extension;
            $file->move(public_path('/upload/ap_imd_up/'), $picture);

            $data = new ApImdUplod();
            $data->appointment_id = $request->id;
            $data->name = $request->name;
            $data->image = $picture;
            $data->ai_analysis = $request->input('ai_analysis', null);
            $data->doctor_notes = $request->input('doctor_notes', null);
            $data->is_verified = $request->has('ai_analysis') ? 1 : 0;
            $data->save();

            if ($data->is_verified && $data->ai_analysis) {
                AiReportDiscussion::create(['report_id' => $data->id, 'role' => 'ai', 'content' => $data->ai_analysis]);
            }
            Session::flash('message', __('Report uploaded successfully.'));
            Session::flash('alert-class', 'alert-success');
        } elseif ($request->has('medicine')) {
            $data = new AppointmentMedicines();
            $data->appointment_id = $request->id;
            $data->medicines = json_encode(['medicine' => $request->input('medicine')]);
            $data->save();

            // Notify patient when a prescription is added
            $appointment = BookAppointment::find($request->id);
            if ($appointment && $appointment->user_id) {
                app(\App\Services\NotificationService::class)->sendNotification(
                    __("A new prescription has been added to your appointment."),
                    $appointment->user_id,
                    'user_id',
                    $appointment->id,
                    ['type' => 'user_id', 'receiver_type' => 'user_id', 'order_id' => (string)$appointment->id]
                );
            }

            Session::flash('message', __('Prescription saved successfully.'));
            Session::flash('alert-class', 'alert-success');
        }
        return redirect()->back();
    }

    public function getmedicines(Request $request)
    {
        $search = $request->get('q') ?? $request->get('term') ?? '';
        $medicines = Medicines::when($search, function ($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%');
        })->limit(50)->get();

        return response()->json($medicines);
    }

    public function backtoappointment()
    {
        return redirect()->route('doctorappointment');
    }

    public function edit_prescription(Request $request)
    {
        $id = $request->get('id');
        $prescription = AppointmentMedicines::find($id);
        if ($prescription && $request->has('medicine')) {
            $prescription->medicines = json_encode(['medicine' => $request->input('medicine')]);
            $prescription->save();
            Session::flash('message', __('Prescription updated successfully.'));
            Session::flash('alert-class', 'alert-success');
        }
        return redirect()->back();
    }

    public function get_user_appointment1($id)
    {
        $appointment = BookAppointment::with(['doctorls', 'patientls', 'appointmentMedicines'])->find($id);
        if (!$appointment) {
            return response()->json(['error' => 'Appointment not found'], 404);
        }
        return response()->json($appointment);
    }

    public function show_complete_doctor_appointment(Request $request, AppointmentService $appointmentService)
    {
        $getapp = BookAppointment::with('doctorls.department', 'patientls')->find($request->get("id"));
        if ($getapp) {
            $getapp->status = 4;
            if ($request->hasFile('prescription')) {
                $file = $request->file('prescription');
                $extension = $file->getClientOriginalExtension() ?: 'png';
                $picture = time() . '.' . $extension;
                $file->move(public_path('/upload/prescription/'), $picture);
                $getapp->prescription_file = $picture;
            }
            $getapp->save();
            $appointmentService->updateStatus($getapp->id, 4);

            try {
                $msg = __('order.complete1') . ' ' . $getapp->doctorls->name . " " . __('order.is completed');
                $user = Patient::find($getapp->user_id);
                $user->msg = $msg;
                Mail::send('email.Ordermsg', ['user' => $user], function ($message) use ($user) {
                    $message->to($user->email, $user->name)->subject(__('KumbaCare'));
                });
            } catch (\Exception $e) {}
            Session::flash('message', $msg);
            Session::flash('alert-class', 'alert-success');
            return redirect()->back();
        } else {
            Session::flash('message', __('Appointment Not Found'));
            Session::flash('alert-class', 'alert-danger');
            return redirect()->back();
        }
    }

    public function delete_prescription($id)
    {
        $presc = AppointmentMedicines::find($id);
        if ($presc) {
            $presc->delete();
        }
        return redirect()->back();
    }

    public function delete_report($id)
    {
        $data = ApImdUplod::find($id);
        if ($data) {
            $image_path = public_path('upload/ap_imd_up') . '/' . $data->image;
            if (File::exists($image_path)) File::delete($image_path);
            $data->delete();
        }
        return redirect()->back();
    }

    public function joinVideoCall($id)
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '2') {
            $appointment = BookAppointment::with('patientls')->find($id);
            if (!$appointment || $appointment->doctor_id != Session::get("user_id")) {
                return redirect()->back()->with('error', 'Appointment not found or access denied.');
            }
            $setting = Setting::find(1);
            return view('user.doctor.video_call', compact('appointment', 'setting'));
        } else {
            return redirect()->route('doctor.login');
        }
    }
}
