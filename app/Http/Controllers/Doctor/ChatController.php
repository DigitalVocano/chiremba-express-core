<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Models\Setting;
use App\Models\Doctors;
use App\Models\BookAppointment;
use App\Models\Chat;
use App\Models\Patient;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    public function doctorchat($id)
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '2') {
            $setting = Setting::find(1);
            $doctordata = Doctors::find(Session::get('user_id'));

            $apnt_details = BookAppointment::where('id', $id)->first();
            $patient = Patient::find($apnt_details->user_id);

            $chat_data = Chat::where('appointment_id', $id)
                ->orderBy('id', 'DESC')
                ->get();

            return view("user.doctor.chat")
                ->with("setting", $setting)
                ->with("doctordata", $doctordata)
                ->with("patient", $patient)
                ->with("chat_data", $chat_data)
                ->with("apnt_id", $id);
        } else {
            return redirect()->route('doctor.login');
        }
    }

    public function get_doctor_chat_data($id)
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '2') {
            $chat_data = Chat::where('appointment_id', $id)->get();
            foreach ($chat_data as $key => $value) {
                $value->position = ($value->sender_id == Session::get("user_id")) ? 'right' : 'left';
            }
            return $chat_data;
        } else {
            return redirect()->route('doctor.login');
        }
    }

    public function send_msg_doctor(Request $request, NotificationService $notificationService)
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '2') {
            $request->validate([
                'message' => 'required|string|max:1000',
                'apnt_id' => 'required|exists:book_appointment,id',
                'receiver_id' => 'required|exists:patient,id',
            ]);

            $store = new Chat();
            $store->sender_id = Session::get("user_id");
            $store->receiver_id = $request->input('receiver_id');
            $store->appointment_id = $request->input('apnt_id');
            $store->message = $request->input('message');
            $store->save();

            $msg = __("New message receive");
            $notificationService->sendNotification($msg, $request->input('receiver_id'), "user_id", $request->input('apnt_id'));

            return response()->json(['success' => true], 200);
        }
        return response()->json(['success' => false], 401);
    }

    public function send_file_doctor(Request $request, NotificationService $notificationService)
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '2') {
            $request->validate([
                'file' => 'required|file|max:10240',
                'apnt_id' => 'required|exists:book_appointment,id',
                'receiver_id' => 'required|exists:patient,id',
            ]);

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension() ?: 'bin';
                $filename = time() . '_' . Str::random(10) . '.' . $extension;
                $path = $file->storeAs('upload/chat', $filename, 'public');

                $store = new Chat();
                $store->sender_id = Session::get("user_id");
                $store->receiver_id = $request->input('receiver_id');
                $store->appointment_id = $request->input('apnt_id');
                $store->message = $request->input('message', 'File attached');
                $store->msg_type = 'file';
                $store->file_path = $path;
                $store->original_file_name = $file->getClientOriginalName();
                $store->save();

                $msg = __("New message receive");
                $notificationService->sendNotification($msg, $request->input('receiver_id'), "user_id", $request->input('apnt_id'));

                return response()->json(['success' => true], 200);
            }
            return response()->json(['success' => false, 'error' => 'File not found'], 400);
        }
        return response()->json(['success' => false], 401);
    }
}