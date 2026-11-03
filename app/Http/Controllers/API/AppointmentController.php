<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\BookAppointment;
use App\Services\AppointmentService;
use App\Services\PaymentService;

class AppointmentController extends Controller
{
    public function bookappointment(Request $request, AppointmentService $appointmentService, PaymentService $paymentService)
    {
        $response = array("success" => "0", "register" => "Validation error");
        $rules = [
            'user_id' => 'required',
            'doctor_id' => 'required',
            'date' => 'required',
            'phone' => 'required',
            'user_description' => 'required',
            'consultation_fees' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $message = implode(", ", $validator->messages()->all());
            $response['register'] = $message;
        } else {
            try {
                $patient = Patient::find($request->get("user_id"));
                if (!$patient) {
                    return response()->json(["success" => "0", "register" => "User not found"], 200);
                }

                $bookingData = $request->all();
                if ($request->hasFile('medical_aid_card_photo')) {
                    $file = $request->file('medical_aid_card_photo');
                    $filename = time() . '_card.' . $file->getClientOriginalExtension();
                    $file->move(public_path('upload/medical_aid_cards/'), $filename);
                    $bookingData['medical_aid_card_photo'] = $filename;
                }

                // If ONLINE payment is selected (EcoCash or Paynow)
                $paymentType = strtoupper($request->input('payment_type', $request->input('payment_mode', 'WALLET')));
                $onlineGateway = strtoupper($request->input('online_gateway', 'ECOCASH'));

                if ($paymentType === 'ONLINE') {
                    $fee = (float)$request->input('consultation_fees', 0);
                    $reference = "appt_" . time() . "_" . rand(100, 999);

                    if ($onlineGateway === 'ECOCASH') {
                        $ecocashPhone = $request->input('ecocash_phone', $request->input('phone'));
                        $res = $paymentService->initiateMobilePayment($ecocashPhone, $fee, $reference, 'ecocash', $patient->email);
                        if (!$res['success']) {
                            return response()->json(["success" => "0", "register" => $res['message'] ?? 'EcoCash transaction failed to initiate.'], 200);
                        }
                        $pollUrl = $res['pollUrl'] ?? '';
                        $bookingData['proof_of_payment'] = "EcoCash Poll: " . $pollUrl;

                        \App\Models\PollUrl::create([
                            'reference' => $reference,
                            'poll_url' => $pollUrl,
                            'amount' => $fee,
                            'user_id' => $patient->id,
                            'role_id' => 1,
                            'status' => 'pending',
                            'meta_data' => json_encode($bookingData)
                        ]);

                        return response()->json([
                            "success" => "1",
                            "requires_payment" => true,
                            "online_gateway" => "ECOCASH",
                            "reference" => $reference,
                            "poll_url" => $pollUrl,
                            "instructions" => $res['instructions'] ?? 'Please enter your EcoCash PIN on your phone to complete payment.',
                            "register" => "EcoCash payment initiated. Please enter PIN on phone to complete booking."
                        ], 200);
                    } else {
                        $paynowEmail = $request->input('paynow_email', $patient->email);
                        $res = $paymentService->initiatePaynow($paynowEmail, $fee, $reference);
                        if (!$res['success']) {
                            return response()->json(["success" => "0", "register" => $res['message'] ?? 'Paynow gateway failed to initiate.'], 200);
                        }
                        $pollUrl = $res['pollUrl'] ?? '';
                        $redirectUrl = $res['redirectUrl'] ?? '';
                        $bookingData['proof_of_payment'] = "Paynow Redirect: " . $redirectUrl;

                        \App\Models\PollUrl::create([
                            'reference' => $reference,
                            'poll_url' => $pollUrl,
                            'amount' => $fee,
                            'user_id' => $patient->id,
                            'role_id' => 1,
                            'status' => 'pending',
                            'meta_data' => json_encode($bookingData)
                        ]);

                        return response()->json([
                            "success" => "1",
                            "requires_payment" => true,
                            "online_gateway" => "PAYNOW",
                            "reference" => $reference,
                            "poll_url" => $pollUrl,
                            "redirect_url" => $redirectUrl,
                            "register" => "Paynow payment initiated. Please complete payment in browser to book appointment."
                        ], 200);
                    }
                }

                $appointment = $appointmentService->bookAppointment($patient, $bookingData);

                $response['success'] = "1";
                $response['register'] = "Appointment Booked Successfully";
                $response['data'] = $appointment->id;
            } catch (\Exception $e) {
                $response['success'] = "0";
                $response['register'] = $e->getMessage();
            }
        }
        return response()->json($response, 200);
    }

    public function verifyAppointmentPayment(Request $request, AppointmentService $appointmentService, PaymentService $paymentService)
    {
        $validator = Validator::make($request->all(), [
            'reference' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => "0", 'register' => $validator->errors()->first()], 200);
        }

        $dbTx = \App\Models\PollUrl::where('reference', $request->reference)->first();
        if (!$dbTx) {
            return response()->json(['success' => "0", 'register' => "Payment transaction record not found."], 200);
        }

        if ($dbTx->status === 'completed') {
            $meta = json_decode($dbTx->meta_data, true);
            $apptId = $meta['appointment_id'] ?? null;
            return response()->json([
                'success' => "1",
                'status' => 'completed',
                'appointment_id' => $apptId,
                'register' => 'Payment verified and appointment booked successfully.'
            ], 200);
        }

        if ($dbTx->status === 'failed') {
            return response()->json([
                'success' => "0",
                'status' => 'failed',
                'register' => 'Payment failed or was cancelled.'
            ], 200);
        }

        // If EcoCash transaction, check status from DB/Webhook directly
        $isEcocash = (empty($dbTx->poll_url) || !str_starts_with(strtolower($dbTx->poll_url), 'http')) && (str_contains(strtolower($dbTx->poll_url ?? ''), 'ecocash') || str_contains(strtolower($dbTx->reference ?? ''), 'ec'));
        if ($isEcocash) {
            if ($dbTx->status === 'completed') {
                $meta = json_decode($dbTx->meta_data, true);
                $apptId = $meta['appointment_id'] ?? null;
                return response()->json([
                    'success' => "1",
                    'status' => 'completed',
                    'appointment_id' => $apptId,
                    'register' => 'Payment verified and appointment booked successfully.'
                ], 200);
            }
            if ($dbTx->status === 'failed') {
                return response()->json([
                    'success' => "0",
                    'status' => 'failed',
                    'register' => 'EcoCash payment failed or was cancelled.'
                ], 200);
            }
            return response()->json([
                'success' => "1",
                'status' => 'pending',
                'register' => 'Waiting for EcoCash USSD push confirmation on phone...'
            ], 200);
        }

        // Verify transaction via Paynow poll URL
        $check = $paymentService->checkTransactionStatus($dbTx->poll_url);

        if ($check['paid']) {
            $patient = Patient::find($dbTx->user_id);
            if (!$patient) {
                return response()->json(['success' => "0", 'register' => 'Patient user not found.'], 200);
            }

            $bookingData = json_decode($dbTx->meta_data, true) ?? [];
            $bookingData['proof_of_payment'] = "Verified Payment Ref: " . $dbTx->reference;

            $appointment = $appointmentService->bookAppointment($patient, $bookingData);

            $bookingData['appointment_id'] = $appointment->id;
            $dbTx->status = 'completed';
            $dbTx->meta_data = json_encode($bookingData);
            $dbTx->save();

            return response()->json([
                'success' => "1",
                'status' => 'completed',
                'appointment_id' => $appointment->id,
                'register' => 'Payment verified and appointment booked successfully.'
            ], 200);
        }

        if ($check['status'] === 'failed') {
            $dbTx->status = 'failed';
            $dbTx->save();
            return response()->json([
                'success' => "0",
                'status' => 'failed',
                'register' => 'Payment failed or was cancelled by user.'
            ], 200);
        }

        return response()->json([
            'success' => "1",
            'status' => 'pending',
            'register' => 'Payment is still pending verification.'
        ], 200);
    }

    public function getslotdata(Request $request)
    {
        $response = array("success" => "0", "register" => "Validation error");
        $rules = ['doctor_id' => 'required', 'date' => 'required'];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $message = implode(", ", $validator->messages()->all());
            $response['register'] = $message;
        } else {
            $day = date('N', strtotime($request->get("date"))) - 1;
            $data = Schedule::with('getslotls')->where("doctor_id", $request->get("doctor_id"))->where("day_id", $day)->get();
            $main = array();
            if (count($data) > 0) {
                foreach ($data as $k) {
                    $slotlist = array();
                    $slotlist['title'] = $k->start_time . " - " . $k->end_time;
                    if (count($k->getslotls) > 0) {
                        foreach ($k->getslotls as $b) {
                            $ka = array();
                            $getappointment = BookAppointment::where("date", $request->get("date"))->where("slot_id", $b->id)->first();
                            $ka['id'] = $b->id;
                            $ka['name'] = $b->slot;
                            $ka['is_book'] = $getappointment ? '1' : '0';
                            $slotlist['slottime'][] = $ka;
                        }
                    }
                    $main[] = $slotlist;
                }
            }

            // Fallback for Zimbabwean session-based booking if doctor hasn't defined fixed time slots
            if (empty($main)) {
                $main = [
                    [
                        'title' => 'Preferred Consultation Session',
                        'slottime' => [
                            ['id' => 101, 'name' => 'Morning (08:00 - 12:00)', 'is_book' => '0'],
                            ['id' => 102, 'name' => 'Afternoon (12:00 - 17:00)', 'is_book' => '0'],
                            ['id' => 103, 'name' => 'Evening (17:00 - 20:00)', 'is_book' => '0']
                        ]
                    ]
                ];
            }

            $response = ["success" => "1", "register" => "Get Slot Successfully", "data" => $main];
        }
        return response()->json($response, 200);
    }
}