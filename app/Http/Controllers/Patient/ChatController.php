<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;
use App\Models\Patient;
use App\Models\Doctors;
use App\Models\BookAppointment;
use App\Models\Chat;
use App\Services\NotificationService;

class ChatController extends Controller
{
    /**
     * Displays the user chat interface for a specific appointment.
     */
    public function userchat($id)
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '1') {
            $setting = Setting::find(1);
            $userdata = Patient::with('wallet')->find(Session::get('user_id'));

            $apnt_details = BookAppointment::where('id', $id)->first();
            if (!$apnt_details) {
                return redirect()->back()->withErrors('Appointment not found.');
            }
            $doctor = Doctors::find($apnt_details->doctor_id);

            $chat_data = Chat::where('appointment_id', $id)
                ->orderBy('id', 'ASC')
                ->get();

            $ai_credits = $userdata->wallet->ai_credits ?? 0;

            return view("user.patient.chat")
                ->with("setting", $setting)
                ->with("userdata", $userdata)
                ->with("doctor", $doctor)
                ->with("chat_data", $chat_data)
                ->with("apnt_id", $id)
                ->with("ai_credits", $ai_credits);
        } else {
            return redirect()->route('login');
        }
    }

    /**
     * Retrieves chat data for a specific appointment for display.
     */
    public function get_user_chat_data($id)
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '1') {
            $chat_data = Chat::where('appointment_id', $id)->get();
            foreach ($chat_data as $value) {
                $value->position = ($value->sender_id == Session::get("user_id")) ? 'right' : 'left';
            }
            return response()->json($chat_data);
        } else {
            return redirect()->route('login');
        }
    }

    /**
     * Sends a text message from the user in a chat.
     */
    public function send_msg_user(Request $request, NotificationService $notificationService)
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '1') {
            $request->validate([
                'message' => 'nullable|string|max:1000',
                'apnt_id' => 'required|exists:book_appointment,id',
                'receiver_id' => 'required|exists:doctors,id',
            ]);

            $store = new Chat();
            $store->sender_id = Session::get("user_id");
            $store->receiver_id = $request->input('receiver_id');
            $store->appointment_id = $request->input('apnt_id');
            $store->message = $request->input('message');
            $store->save();

            $msg = __("New message received");
            $notificationService->sendNotification($msg, $request->input('receiver_id'), "doctor_id");

            return response()->json(['success' => true], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    /**
     * Sends a file from the user in a chat.
     */
    public function send_file_user(Request $request, NotificationService $notificationService)
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '1') {
            $request->validate([
                'file' => 'required|file|max:10240',
                'apnt_id' => 'required|exists:book_appointment,id',
                'receiver_id' => 'required|exists:doctors,id',
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

                $msg = __("New message received");
                $notificationService->sendNotification($msg, $request->input('receiver_id'), "doctor_id");

                return response()->json(['success' => true, 'file_path' => Storage::url($path)], 200);
            } else {
                return response()->json(['success' => false, 'error' => 'File not found'], 400);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
    }
}