<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Doctors;
use App\Models\ProviderProfileView;
use Illuminate\Support\Facades\Cache;
use App\Models\Services;
use App\Models\Review;
use App\Models\BookAppointment;
use App\Models\Subscriber;
use App\Models\Settlement;
use App\Models\Subscription;
use App\Models\Setting;
use App\Models\ApImdUplod;
use App\Models\AppointmentMedicines;
use App\Services\WalletService;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Response;

class DoctorController extends Controller
{
    public function showsearchdoctor(Request $request)
    {
        $response = array("status" => "0", "register" => "Validation error");
        $rules = ['term' => 'required'];
        $messages = ['term.required' => "term is required"];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $message = implode(", ", $validator->messages()->all());
            $response['msg'] = $message;
        } else {
            $term = $request->get("term");
            $data = Doctors::where('is_approve', '1')
                ->where(function($q) use ($term) {
                    $q->where('name', 'like', '%' . $term . '%')
                      ->orWhere('email', 'like', '%' . $term . '%');
                })
                ->select("id", "name", "address", "image", "department_id", "accept_cod", "accept_digital_payments", "profile_type")
                ->paginate(10);
            if ($data && $data->count() > 0) {
                foreach ($data as $k) {
                    $dr = Services::find($k->department_id);
                    $k->department_name = $dr->name ?? "";
                    if (!empty($k->image) && $k->image !== 'default.png' && $k->image !== 'doctor_default.png' && $k->image !== 'defaultpharmacy.png') {
                        $k->image = str_replace('/public/upload/', '/upload/', asset('upload/doctors') . '/' . $k->image);
                    } else {
                        $k->image = "";
                    }
                    $k->accept_cod = (int)($k->accept_cod ?? 1);
                    $k->accept_digital_payments = (int)($k->accept_digital_payments ?? 1);
                    unset($k->department_id);
                }
                $response = array("status" => 1, "msg" => "Search Result", "data" => $data);
            } else {
                $response = array("status" => 0, "msg" => "No Result Found");
            }
        }
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    public function nearbydoctor(Request $request)
    {
        $lat = $request->get("lat");
        $lon = $request->get("lon");

        $query = DB::table("doctors")
            ->select(
                "doctors.id",
                "doctors.name",
                "doctors.address",
                "doctors.department_id",
                "doctors.image",
                "doctors.email",
                "doctors.phoneno",
                "doctors.profile_type",
                "doctors.consultation_fees",
                "doctors.working_time",
                "doctors.is_approve",
                "doctors.accept_cod",
                "doctors.accept_digital_payments"
            );

        if (!empty($lat) && !empty($lon)) {
            $query->addSelect(DB::raw("IF(doctors.lat IS NOT NULL AND doctors.lat != '', 6371 * acos(cos(radians(" . (float)$lat . ")) * cos(radians(doctors.lat)) * cos(radians(doctors.lon) - radians(" . (float)$lon . ")) + sin(radians(" . (float)$lat . ")) * sin(radians(doctors.lat))), 999999) AS distance"));
            $query->orderby('distance');
        } else {
            $query->addSelect(DB::raw("0 AS distance"));
            $query->orderby('doctors.id', 'DESC');
        }

        if ($request->has('type') && !empty($request->type)) {
            $query->where('doctors.profile_type', $request->type);
        }



        $perPage = (int)$request->get('per_page', ($request->get('type') == 2 ? 50 : 10));

        // First attempt: Get approved providers
        $approvedQuery = clone $query;
        $approvedQuery->where('doctors.is_approve', 1);
        $data = $approvedQuery->paginate($perPage);

        // Fallback attempt: If no approved providers exist, return available providers so app displays data
        if ($data->isEmpty()) {
            $data = $query->paginate($perPage);
        }

        if ($data->isNotEmpty()) {
            foreach ($data as $k) {
                $department = Services::find($k->department_id);
                $k->department_name = isset($department) ? $department->name : "";
                $imgName = !empty($k->image) ? $k->image : 'default.png';
                $k->image = str_replace('/public/upload/', '/upload/', asset("upload/doctors") . '/' . $imgName);
                $k->accept_cod = (int)($k->accept_cod ?? 1);
                $k->accept_digital_payments = (int)($k->accept_digital_payments ?? 1);
                if (isset($k->distance) && $k->distance < 999000) {
                    $k->distance_km = round((float)$k->distance, 1);
                    $k->distance_text = round((float)$k->distance, 1) . ' km away';
                } else {
                    $k->distance_km = null;
                    $k->distance_text = 'Distance unavailable';
                }
            }
            $response = array("status" => 1, "msg" => "Search Result", "data" => $data);
        } else {
            $response = array("status" => 0, "msg" => "No Result Found");
        }
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    public function getListOfDoctorBySpecialty(Request $request)
    {
        $validator = Validator::make($request->all(), ['department_id' => 'required']);
        if ($validator->fails()) {
            return response()->json(['success' => "0", 'register' => $validator->errors()->first()], 200);
        }

        $lat = $request->get("lat");
        $lon = $request->get("lon");
        $department_id = $request->get("department_id");
        $perPage = (int)$request->get('per_page', 50);

        $query = DB::table("doctors")
            ->select(
                "doctors.id",
                "doctors.name",
                "doctors.address",
                "doctors.department_id",
                "doctors.image",
                "doctors.email",
                "doctors.phoneno",
                "doctors.accept_cod",
                "doctors.accept_digital_payments"
            )
            ->where(function($q) use ($department_id) {
                $q->where('doctors.department_id', $department_id)
                  ->orWhereRaw("FIND_IN_SET(?, doctors.department_id)", [$department_id]);
            })
            ->where(function($q) {
                $q->where('doctors.profile_type', 1)
                  ->orWhereNull('doctors.profile_type');
            });

        if (!empty($lat) && !empty($lon) && (float)$lat != 0 && (float)$lon != 0) {
            $query->addSelect(DB::raw("IF(doctors.lat IS NOT NULL AND doctors.lat != '', 6371 * acos(cos(radians(" . (float)$lat . ")) * cos(radians(doctors.lat)) * cos(radians(doctors.lon) - radians(" . (float)$lon . ")) + sin(radians(" . (float)$lat . ")) * sin(radians(doctors.lat))), 999999) AS distance"));
            $query->orderby('distance');
        } else {
            $query->addSelect(DB::raw("0 AS distance"));
            $query->orderby('doctors.id', 'DESC');
        }

        // First attempt: Get approved providers
        $approvedQuery = clone $query;
        $approvedQuery->where('doctors.is_approve', 1);
        $data = $approvedQuery->paginate($perPage);

        // Fallback: Return all doctors in this specialty if none marked strictly approved
        if ($data->isEmpty()) {
            $data = $query->paginate($perPage);
        }

        if ($data->isNotEmpty()) {
            foreach ($data as $k) {
                $department = Services::find($k->department_id);
                $k->department_name = isset($department) ? $department->name : "";
                $imgName = !empty($k->image) ? $k->image : 'default.png';
                $k->image = str_replace('/public/upload/', '/upload/', asset("upload/doctors") . '/' . $imgName);
                $k->accept_cod = (int)($k->accept_cod ?? 1);
                $k->accept_digital_payments = (int)($k->accept_digital_payments ?? 1);
                if (isset($k->distance) && $k->distance > 0 && $k->distance < 999000) {
                    $k->distance_km = round((float)$k->distance, 1);
                    $k->distance_text = round((float)$k->distance, 1) . ' km away';
                } else {
                    $k->distance_km = null;
                    $k->distance_text = 'Distance unavailable';
                }
            }
            $response = array("success" => "1", "register" => "Doctor List", "data" => $data);
        } else {
            $response = array("success" => "0", "register" => "No Doctors Found in this Specialty");
        }

        return response()->json($response, 200);
    }

    public function getalldoctors()
    {
        $data = Doctors::take(26)->get();
        foreach ($data as $k) {
            $imgName = !empty($k->image) ? $k->image : 'default.png';
            $k->image = str_replace('/public/upload/', '/upload/', asset("upload/doctors") . '/' . $imgName);
        }
        return Response::json($data);
    }

    public function viewdoctor(Request $request)
    {
        $response = array("success" => "0", "register" => "Validation error");
        $rules = ['doctor_id' => 'required'];
        $messages = ['doctor_id.required' => "doctor_id is required"];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $message = implode(", ", $validator->messages()->all());
            $response['register'] = $message;
        } else {
            $getdetail = Doctors::find($request->get("doctor_id"));
            if (empty($getdetail)) {
                $response['success'] = "0";
                $response['register'] = "Doctor Not Found";
            } else {
                $getdepartment = Services::find($getdetail->department_id);
                $getdetail->department_name = $getdepartment ? $getdepartment->name : "";
                $getdetail->avgratting = Review::where('doc_id', $request->get("doctor_id"))->avg('rating');
                $getdetail->total_review = count(Review::where('doc_id', $request->get("doctor_id"))->get());
                $imgName = !empty($getdetail->image) ? $getdetail->image : 'default.png';
                $getdetail->image = str_replace('/public/upload/', '/upload/', asset('upload/doctors') . '/' . $imgName);
                
                // Check subscription requirements and active status cleanly
                $isSubscribed = $getdetail->isSubscribedOrGlobalDisabled();
                $subscriptionRequired = $getdetail->isSubscriptionRequired();

                $getdetail->is_subscribed = $isSubscribed;
                $getdetail->subscription_required = $subscriptionRequired;
                $getdetail->is_subscription = $isSubscribed ? "1" : "0";

                $getdetail->accept_cod = (int)($getdetail->accept_cod ?? 1);
                $getdetail->accept_digital_payments = (int)($getdetail->accept_digital_payments ?? 1);

                // Track visitor profile view
                $viewerIp = $request->ip() ?? '127.0.0.1';
                $viewerId = $request->get('user_id') ?? $request->get('patient_id') ?? null;
                $throttleKey = 'pview_' . $getdetail->id . '_' . md5($viewerIp . '_' . ($viewerId ?? 'guest'));

                if (!Cache::has($throttleKey)) {
                    Cache::put($throttleKey, true, 3600); // 1 hour session throttle
                    try {
                        ProviderProfileView::create([
                            'doctor_id' => $getdetail->id,
                            'viewer_id' => $viewerId,
                            'viewer_ip' => $viewerIp,
                            'platform' => $request->header('User-Agent') && str_contains(strtolower($request->header('User-Agent')), 'dart') ? 'mobile' : 'web',
                            'user_agent' => substr($request->header('User-Agent') ?? '', 0, 250),
                            'view_date' => date('Y-m-d'),
                        ]);
                        $getdetail->increment('profile_views');
                    } catch (\Exception $e) {
                        \Log::warning("Could not log provider profile view: " . $e->getMessage());
                    }
                }
                $getdetail->profile_views = (int)($getdetail->profile_views ?? 0);
                $getdetail->search_impressions = (int)($getdetail->search_impressions ?? 0);

                // Include System Settings for Flutter Mobile App
                $setting = Setting::first();
                $getdetail->payment_mode = $setting->payment_mode ?? 'platform_gateway';
                $getdetail->is_patient_wallet_enabled = (int)($setting->is_patient_patient_wallet_enabled ?? $setting->is_patient_wallet_enabled ?? 1);

                $response['success'] = "1";
                $response['register'] = "Doctor Get Successfully";
                $response['data'] = $getdetail;
            }
        }
        return json_encode($response, JSON_NUMERIC_CHECK);
    }

    public function doctoruappointment(Request $request)
    {
        $validator = Validator::make($request->all(), ['doctor_id' => 'required']);
        if ($validator->fails()) return response()->json(['success' => "0", 'message' => $validator->errors()->first()], 200);

        $data = BookAppointment::with('patientls')->where("doctor_id", $request->doctor_id)
            ->where("date", ">=", date('Y-m-d'))
            ->where("status", "!=", 4)
            ->orderBy('date', 'ASC')->paginate(10);

        foreach ($data as $item) {
            $patientName = $item->patientls->name ?? $item->name ?? 'Patient';
            if (empty($patientName) || strtolower(trim($patientName)) === 'patient') {
                $patientName = $item->patientls->name ?? 'Patient';
            }
            $item->name = $patientName;
            $item->user_name = $patientName;
            $item->patient_name = $patientName;
            
            $img = $item->patientls->profile_pic ?? 'default.png';
            $item->image = asset('upload/profile') . '/' . $img;
            $item->user_image = $item->image;
            $item->slot = $item->slot_name ?? ($item->preferred_session ?? '');
        }

        return response()->json(['success' => "1", 'data' => $data], 200);
    }

    public function doctorpastappointment(Request $request)
    {
        $validator = Validator::make($request->all(), ['doctor_id' => 'required']);
        if ($validator->fails()) return response()->json(['success' => "0", 'message' => $validator->errors()->first()], 200);

        $data = BookAppointment::with('patientls')->where("doctor_id", $request->doctor_id)
            ->where(function($q) {
                $q->where("date", "<", date('Y-m-d'))
                  ->orWhere("status", 4);
            })
            ->orderBy('date', 'DESC')->paginate(10);

        foreach ($data as $item) {
            $patientName = $item->patientls->name ?? $item->name ?? 'Patient';
            if (empty($patientName) || strtolower(trim($patientName)) === 'patient') {
                $patientName = $item->patientls->name ?? 'Patient';
            }
            $item->name = $patientName;
            $item->user_name = $patientName;
            $item->patient_name = $patientName;
            
            $img = $item->patientls->profile_pic ?? 'default.png';
            $item->image = asset('upload/profile') . '/' . $img;
            $item->user_image = $item->image;
            $item->slot = $item->slot_name ?? ($item->preferred_session ?? '');
        }

        return response()->json(['success' => "1", 'data' => $data], 200);
    }

    public function doctorallappointment(Request $request)
    {
        $validator = Validator::make($request->all(), ['doctor_id' => 'required']);
        if ($validator->fails()) return response()->json(['success' => "0", 'message' => $validator->errors()->first()], 200);

        $data = BookAppointment::with('patientls')
            ->where("doctor_id", $request->doctor_id)
            ->orderBy('date', 'DESC')
            ->paginate(10);

        foreach ($data as $item) {
            $patientName = $item->patientls->name ?? $item->name ?? 'Patient';
            if (empty($patientName) || strtolower(trim($patientName)) === 'patient') {
                $patientName = $item->patientls->name ?? 'Patient';
            }
            $item->name = $patientName;
            $item->user_name = $patientName;
            $item->patient_name = $patientName;
            
            $img = $item->patientls->profile_pic ?? 'default.png';
            $item->image = asset('upload/profile') . '/' . $img;
            $item->user_image = $item->image;
            $item->slot = $item->slot_name ?? ($item->preferred_session ?? '');
        }

        return response()->json(['success' => "1", 'data' => $data], 200);
    }

    public function doctoreditprofile(Request $request)
    {
        $doctor = Doctors::find($request->doctor_id);
        if (!$doctor) return response()->json(['success' => "0", 'message' => "Doctor not found"], 200);

        if ($request->has('name')) $doctor->name = $request->name;
        if ($request->has('phoneno')) $doctor->phoneno = $request->phoneno;
        if ($request->has('address')) $doctor->address = $request->address;
        if ($request->has('lat')) $doctor->lat = $request->lat;
        if ($request->has('lon')) $doctor->lon = $request->lon;
        if ($request->has('aboutus')) $doctor->aboutus = $request->aboutus;
        if ($request->has('services')) $doctor->services = $request->services;
        if ($request->has('healthcare')) $doctor->healthcare = $request->healthcare;
        if ($request->has('department_id') && !empty($request->department_id)) $doctor->department_id = $request->department_id;
        if ($request->has('working_time')) $doctor->working_time = $request->working_time;
        if ($request->has('consultation_fees')) $doctor->consultation_fees = $request->consultation_fees;
        if ($request->has('ecocash_number')) $doctor->ecocash_number = $request->ecocash_number;
        if ($request->has('ecocash_name')) $doctor->ecocash_name = $request->ecocash_name;
        if ($request->has('bank_name')) $doctor->bank_name = $request->bank_name;
        if ($request->has('bank_account_number')) $doctor->bank_account_number = $request->bank_account_number;
        if ($request->has('bank_account_name')) $doctor->bank_account_name = $request->bank_account_name;
        if ($request->has('innbucks_number')) $doctor->innbucks_number = $request->innbucks_number;
        if ($request->has('accept_cod')) $doctor->accept_cod = (int)$request->accept_cod;
        if ($request->has('accept_digital_payments')) $doctor->accept_digital_payments = (int)$request->accept_digital_payments;

        if ($request->has('medical_aids')) {
            $medAids = is_array($request->medical_aids) ? $request->medical_aids : json_decode($request->medical_aids, true);
            if (is_array($medAids)) {
                $societyIds = [];
                foreach ($medAids as $aid) {
                    if (is_numeric($aid)) {
                        $societyIds[] = (int)$aid;
                    } else {
                        $soc = \App\Models\MedicalAidSociety::where('name', 'LIKE', "%$aid%")->orWhere('code', 'LIKE', "%$aid%")->first();
                        if ($soc) $societyIds[] = $soc->id;
                    }
                }
                if (method_exists($doctor, 'medicalAids')) {
                    $doctor->medicalAids()->sync($societyIds);
                }
            }
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/doctors/'), $filename);
            $doctor->image = $filename;
        }
        $doctor->save();

        if ($request->has('timing')) {
            $timingData = json_decode($request->timing, true);
            if (isset($timingData['timing'])) {
                $setting = Setting::first();
                if ($setting && $setting->is_doctor_subscription_enabled && $doctor->hasFeature('max_time_slots')) {
                    $limit = (int)$doctor->getFeatureValue('max_time_slots', 0);
                    if ($limit > 0) {
                        $incomingSlotCount = 0;
                        foreach ($timingData['timing'] as $daySlots) {
                            if (is_array($daySlots)) {
                                foreach ($daySlots as $sl) {
                                    if (!empty($sl['start_time']) && !empty($sl['end_time']) && !empty($sl['duration'])) {
                                        $incomingSlotCount += count($this->getslotvalue($sl['start_time'], $sl['end_time'], $sl['duration']));
                                    }
                                }
                            }
                        }
                        if ($incomingSlotCount > $limit) {
                            return response()->json([
                                'success' => "0",
                                'message' => __("You have exceeded the maximum number of weekly time slots ({$limit}) allowed by your subscription plan.")
                            ], 200);
                        }
                    }
                }
                // Delete old schedules and slot timings for this doctor
                $removedata = \App\Models\Schedule::where("doctor_id", $doctor->id)->get();
                foreach ($removedata as $k) {
                    \App\Models\SlotTiming::where("schedule_id", $k->id)->delete();
                    $k->delete();
                }

                // Insert new schedules
                foreach ($timingData['timing'] as $dayId => $slots) {
                    if (is_array($slots)) {
                        foreach ($slots as $slot) {
                            if (!empty($slot['start_time']) && !empty($slot['end_time']) && !empty($slot['duration'])) {
                                $store = new \App\Models\Schedule();
                                $store->doctor_id = $doctor->id;
                                $store->day_id = (int)$dayId;
                                $store->start_time = $slot['start_time'];
                                $store->end_time = $slot['end_time'];
                                $store->duration = $slot['duration'];
                                $store->save();

                                $generatedSlots = $this->getslotvalue($slot['start_time'], $slot['end_time'], $slot['duration']);
                                foreach ($generatedSlots as $g) {
                                    $aslot = new \App\Models\SlotTiming();
                                    $aslot->schedule_id = $store->id;
                                    $aslot->slot = $g;
                                    $aslot->save();
                                }
                            }
                        }
                    }
                }
            }
        }

        // Refresh data to return
        $doctor = Doctors::find($request->doctor_id);
        $doctor->image = asset('upload/doctors') . '/' . ($doctor->image ?? 'default.png');
        
        $dr = Services::find($doctor->department_id);
        $doctor->department_name = $dr ? $dr->name : "";

        return response()->json(['success' => "1", 'message' => "Profile Updated", 'data' => $doctor], 200);
    }

    public function doctor_subscription_list(Request $request)
    {
        $userType = $request->input('user_type', 'doctor');

        $query = Subscription::with('features');
        
        // Match active plans
        $query->where(function($q) {
            $q->where('is_active', 1)
              ->orWhere('is_active', '1')
              ->orWhereNull('is_active');
        });

        // Match user type if specified, otherwise include all general plans
        if (!empty($userType)) {
            $query->where(function($q) use ($userType) {
                $q->where('user_type', $userType)
                  ->orWhere('user_type', 'all')
                  ->orWhereNull('user_type')
                  ->orWhere('user_type', '');
            });
        }

        $data = $query->orderBy('price', 'ASC')->get();
        
        // Map 'duration' to 'month' for backward compatibility with mobile app models
        $data->transform(function ($plan) {
            $plan->month = $plan->duration ?? 1;
            $doctorId = request()->input('doctor_id');
            $activeSub = (!empty($doctorId) && ($doc = Doctors::find($doctorId))) ? $doc->activeSubscription() : null;
            $plan->is_current_plan = ($activeSub && $activeSub->subscription_id == $plan->id) ? 1 : 0;
            return $plan;
        });

        return response()->json(['success' => "1", 'data' => $data], 200);
    }

    public function income_report(Request $request)
    {
        $validator = Validator::make($request->all(), ['doctor_id' => 'required']);
        if ($validator->fails()) return response()->json(['success' => "0", 'message' => $validator->errors()->first()], 200);

        $query = Settlement::where("doctor_id", $request->doctor_id);

        if ($request->has('duration')) {
            $duration = $request->duration;
            if ($duration == 'today') {
                $query->whereDate('created_at', date('Y-m-d'));
            } elseif ($duration == 'last 7 days') {
                $query->where('created_at', '>=', \Carbon\Carbon::now()->subDays(7));
            } elseif ($duration == 'last 30 days') {
                $query->where('created_at', '>=', \Carbon\Carbon::now()->subDays(30));
            } elseif (strpos($duration, ',') !== false) {
                $dates = explode(',', $duration);
                if (count($dates) == 2) {
                    $query->whereBetween('created_at', [
                        \Carbon\Carbon::parse($dates[0])->startOfDay(),
                        \Carbon\Carbon::parse($dates[1])->endOfDay()
                    ]);
                }
            }
        }

        $settlements = $query->orderBy('id', 'DESC')->get();
        $total_income = $settlements->sum('amount');
        
        // Format data to match Mobile App Model (IncomeData)
        $data = [
            'income_record' => $settlements,
            'total_income' => $total_income
        ];
        
        return response()->json(['success' => "1", 'data' => $data], 200);
    }

    public function appointment_detail(Request $request)
    {
        $validator = Validator::make($request->all(), ['id' => 'required']);
        if ($validator->fails()) return response()->json(['success' => "0", 'message' => $validator->errors()->first()], 200);

        $data = BookAppointment::with(['patientls'])->find($request->id);
        
        if($data){
             if($data->patientls){
                 $data->patientls->image = asset('upload/profile').'/'.($data->patientls->profile_pic ?? 'default.png');
                 $data->user_name = $data->patientls->name ?? "";
                 $data->user_image = $data->patientls->image;
             }
             if ($data->medical_aid_card_photo && strpos($data->medical_aid_card_photo, 'http') !== 0) {
                 $data->medical_aid_card_photo_url = asset('upload/medical_aid_cards/' . $data->medical_aid_card_photo);
             } else {
                 $data->medical_aid_card_photo_url = $data->medical_aid_card_photo;
             }

             $doc = Doctors::with('department')->find($data->doctor_id);
             $doctorData = null;
             if ($doc) {
                 $doctorData = [
                     'id' => $doc->id,
                     'name' => $doc->name,
                     'address' => $doc->address,
                     'image' => asset('upload/doctors/' . ($doc->image ?? 'default.png')),
                     'departmentls' => $doc->department ? ['name' => $doc->department->name] : null
                 ];
                 $data->doctor_name = $doc->name;
                 $data->doctor_image = asset('upload/doctors/' . ($doc->image ?? 'default.png'));
                 $data->doctor_address = $doc->address;
             }
             $data->slot = $data->slot_name ?? ($data->preferred_session ?? '');

             // Fetch prescription and image reports
             $appMedicines = AppointmentMedicines::where('appointment_id', $request->id)->first();
             $prescriptionData = null;
             if ($appMedicines && !empty($appMedicines->medicines)) {
                 $decoded = json_decode($appMedicines->medicines, true);
                 $prescriptionData = $decoded ?: ['medicine' => []];
             }

             $reportImages = ApImdUplod::where('appointment_id', $request->id)->get();
             foreach ($reportImages as $img) {
                 if (!empty($img->image)) {
                     if (strpos($img->image, 'http') === 0) {
                         $img->image_url = $img->image;
                     } elseif (file_exists(public_path('upload/ap_imd_up/' . $img->image))) {
                         $img->image_url = asset('upload/ap_imd_up/' . $img->image);
                     } elseif (file_exists(public_path('upload/ap_img_up/' . $img->image))) {
                         $img->image_url = asset('upload/ap_img_up/' . $img->image);
                     } else {
                         $img->image_url = asset('upload/ap_imd_up/' . $img->image);
                     }
                 }
             }


             return response()->json([
                 'success' => "1",
                 'data' => $data,
                 'doctor' => $doctorData,
                 'prescription' => $prescriptionData,
                 'image' => $reportImages
             ], 200);
        }
        
        return response()->json(['success' => "0", 'message' => "Appointment not found"], 200);
    }

    public function add_medicine_to_app(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointment_id' => 'required',
            'medicine_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => "0", 'msg' => $validator->errors()->first()], 200);
        }

        $appointmentId = $request->appointment_id;
        $rawMedicine = $request->medicine_id;
        $medicineJson = is_string($rawMedicine) ? $rawMedicine : json_encode($rawMedicine);

        $record = AppointmentMedicines::where('appointment_id', $appointmentId)->first();
        if (!$record) {
            $record = new AppointmentMedicines();
            $record->appointment_id = $appointmentId;
        }
        $record->medicines = $medicineJson;
        $record->save();

        return response()->json([
            'status' => "1",
            'success' => "1",
            'msg' => 'Prescription updated successfully',
            'data' => $record,
            'prescription' => json_decode($medicineJson, true)
        ], 200);
    }

    public function upload_image(Request $request)
    {
        $appointmentId = $request->input('appointment_id');
        $name = $request->input('name', 'Medical Document');

        if (empty($appointmentId)) {
            return response()->json(['status' => "0", 'msg' => 'appointment_id is required'], 200);
        }

        $dest1 = public_path('upload/ap_imd_up');
        $dest2 = public_path('upload/ap_img_up');
        if (!file_exists($dest1)) mkdir($dest1, 0777, true);
        if (!file_exists($dest2)) mkdir($dest2, 0777, true);

        $filename = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext = strtolower($file->getClientOriginalExtension() ?: 'png');
            $filename = time() . '_' . uniqid() . '.' . $ext;
            $file->move($dest1, $filename);
            @copy($dest1 . '/' . $filename, $dest2 . '/' . $filename);
        } elseif ($request->filled('image')) {
            $raw = $request->input('image');
            $ext = 'png';
            if (preg_match('/^data:image\/(\w+);base64,/', $raw, $matches)) {
                $ext = strtolower($matches[1]);
                $raw = substr($raw, strpos($raw, ',') + 1);
            }
            $decoded = base64_decode($raw);
            if ($decoded !== false && strlen($decoded) > 0) {
                $filename = time() . '_' . uniqid() . '.' . $ext;
                file_put_contents($dest1 . '/' . $filename, $decoded);
                @copy($dest1 . '/' . $filename, $dest2 . '/' . $filename);
            }
        }

        if (empty($filename)) {
            return response()->json(['status' => "0", 'msg' => 'Invalid or missing image data'], 200);
        }

        $upload = new ApImdUplod();
        $upload->appointment_id = $appointmentId;
        $upload->name = $name;
        $upload->image = $filename;
        $upload->save();

        return response()->json([
            'status' => "1",
            'success' => "1",
            'msg' => 'Report uploaded successfully',
            'data' => $upload,
            'image' => asset('upload/ap_imd_up/' . $filename),
        ], 200);
    }

    public function subscribeWithWallet(Request $request, WalletService $walletService, SubscriptionService $subscriptionService)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required',
            'plan_id' => 'required|exists:subscriptions,id'
        ]);

        if ($validator->fails()) return response()->json(['success' => "0", 'message' => $validator->errors()->first()], 200);

        $doctor = Doctors::with('wallet')->find($request->doctor_id);
        $plan = Subscription::find($request->plan_id);

        try {
            $walletService->debit($doctor, $plan->price, 'Subscription payment: ' . $plan->name, 'sub_' . $plan->id . '_' . time());
            $subscriptionService->subscribe($doctor, $plan);
            
            return response()->json(['success' => "1", 'register' => "Subscription activated successfully", 'message' => "Subscription activated successfully"], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => "0", 'message' => $e->getMessage()], 200);
        }
    }

    public function doctor_wallet(Request $request)
    {
        $validator = Validator::make($request->all(), ['doctor_id' => 'required|exists:doctors,id']);
        if ($validator->fails()) {
            return response()->json(['success' => "0", 'message' => $validator->errors()->first()], 400);
        }

        $doctor = Doctors::with('wallet.transactions')->find($request->doctor_id);

        if (!$doctor->wallet) {
            // Create a wallet if it doesn't exist
            $doctor->wallet()->create(['balance' => 0.00, 'ai_credits' => 0]);
            $doctor->load('wallet.transactions'); // Reload the relationship
        }

        return response()->json([
            'success' => "1",
            'balance' => $doctor->wallet->balance,
            'transactions' => $doctor->wallet->transactions()->latest()->get()
        ], 200);
    }

    public function getdoctorschedule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'nullable|date_format:Y-m-d'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => "0", 'register' => $validator->errors()->first()], 400);
        }

        $date = $request->has('date') ? $request->get("date") : date('Y-m-d');
        $day = date('N', strtotime($date)) - 1;
        $data = \App\Models\Schedule::with('getslotls')->where("doctor_id", $request->get("doctor_id"))->where("day_id", $day)->get();
        
        $main = [];
        if ($data->isNotEmpty()) {
            foreach ($data as $k) {
                $slotlist = [];
                $slotlist['title'] = $k->start_time . " - " . $k->end_time;
                $slotlist['slots'] = [];
                if ($k->getslotls->isNotEmpty()) {
                    foreach ($k->getslotls as $b) {
                        $getappointment = \App\Models\BookAppointment::where("date", $date)->where("slot_id", $b->id)->first();
                        $slotlist['slots'][] = ['id' => $b->id, 'name' => $b->slot, 'is_book' => $getappointment ? '1' : '0'];
                    }
                }
                $main[] = $slotlist;
            }
        }
        
        return response()->json(["success" => "1", "register" => "Get Schedule Successfully", "data" => $main], 200);
    }

    public function saveholiday(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'description' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => "0", 'msg' => $validator->errors()->first()], 200);
        }

        $data = [
            'doctor_id' => $request->doctor_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'description' => $request->description,
        ];

        if ($request->id && $request->id != 0) {
            DB::table('doctor_hoildays')->where('id', $request->id)->update($data);
        } else {
            DB::table('doctor_hoildays')->insert($data);
        }
        
        return response()->json(['success' => "1", 'msg' => 'Holiday saved successfully', 'data' => $data], 200);
    }

    public function deleteholiday(Request $request)
    {
        if ($request->id) {
            DB::table('doctor_hoildays')->where('id', $request->id)->delete();
            return response()->json(['success' => "1", 'msg' => 'Holiday deleted successfully'], 200);
        }
        return response()->json(['success' => "0", 'msg' => 'ID required'], 200);
    }

    public function getholiday(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => "0", 'msg' => $validator->errors()->first()], 200);
        }

        $holidays = \App\Models\Doctor_Hoilday::where('doctor_id', $request->doctor_id)->get();

        return response()->json(['success' => "1", 'msg' => 'Holidays retrieved successfully', 'data' => $holidays], 200);
    }

    public function checkholiday(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => "0", 'msg' => $validator->errors()->first()], 200);
        }

        $date = $request->date;
        $isHoliday = \App\Models\Doctor_Hoilday::where('doctor_id', $request->doctor_id)
            ->where(function ($query) use ($date) {
                $query->whereDate('start_date', '<=', $date)
                      ->whereDate('end_date', '>=', $date);
            })->exists();

        if ($isHoliday) {
            return response()->json(['success' => "0", 'msg' => 'Doctor is on holiday'], 200);
        }

        return response()->json(['success' => "1", 'msg' => 'Doctor is available'], 200);
    }

    public function getslotvalue($start_time, $end_time, $duration)
    {
        $duration = (int)$duration;
        $slots = [];
        if ($duration <= 0) {
            return $slots;
        }

        try {
            $start = new \DateTime($start_time);
            $end = new \DateTime($end_time);
            
            while ($start < $end) {
                $time1 = $start->format('H:i');
                $start->modify("+{$duration} minutes");
                if ($start <= $end) {
                    $time2 = $start->format('H:i');
                    $slots[] = "{$time1} - {$time2}";
                }
            }
        } catch (\Exception $e) {
            \Log::error("getslotvalue error: " . $e->getMessage());
        }

        return $slots;
    }

    public function getdurationoption($start_time, $end_time, $duration)
    {
        return $this->getslotvalue($start_time, $end_time, $duration);
    }

    public function appointmentstatuschange(Request $request)
    {
        $appId = $request->get('app_id') ?? $request->get('id');
        $status = $request->get('status');

        if (empty($appId) || !isset($status)) {
            return response()->json([
                'success' => "0",
                'register' => "app_id and status parameters are required."
            ], 200);
        }

        $appointment = BookAppointment::find($appId);
        if (!$appointment) {
            return response()->json([
                'success' => "0",
                'register' => "Appointment not found."
            ], 200);
        }

        try { $appointment = app(\App\Services\AppointmentService::class)->updateStatus($appId, (int)$status); } catch (\Exception $e) { $appointment->status = (int)$status; $appointment->save(); }
        $appointment->save();

        return response()->json([
            'success' => "1",
            'register' => "Appointment status updated successfully.",
            'data' => $appointment
        ], 200);
    }

    public function doctor_profile_stats(Request $request)
    {
        $doctorId = $request->get('doctor_id');
        if (empty($doctorId)) {
            return response()->json(['success' => "0", 'register' => 'doctor_id is required'], 200);
        }

        $doctor = Doctors::find($doctorId);
        if (!$doctor) {
            return response()->json(['success' => "0", 'register' => 'Doctor not found'], 200);
        }

        $today = date('Y-m-d');
        $weekAgo = date('Y-m-d', strtotime('-7 days'));
        $monthAgo = date('Y-m-d', strtotime('-30 days'));

        $totalViews = (int)($doctor->profile_views ?? 0);
        $searchImpressions = (int)($doctor->search_impressions ?? 0);

        $todayViews = ProviderProfileView::where('doctor_id', $doctorId)->where('view_date', $today)->count();
        $weekViews = ProviderProfileView::where('doctor_id', $doctorId)->where('view_date', '>=', $weekAgo)->count();
        $monthViews = ProviderProfileView::where('doctor_id', $doctorId)->where('view_date', '>=', $monthAgo)->count();

        // 7-day trend
        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $dayName = date('D', strtotime($d));
            $count = ProviderProfileView::where('doctor_id', $doctorId)->where('view_date', $d)->count();
            $trend[] = [
                'date' => $d,
                'day' => $dayName,
                'views' => $count
            ];
        }

        // Appointments & conversion
        $totalAppointments = BookAppointment::where('doctor_id', $doctorId)->count();
        $completedAppointments = BookAppointment::where('doctor_id', $doctorId)->where('status', 4)->count();
        $conversionRate = $totalViews > 0 ? round(($totalAppointments / $totalViews) * 100, 1) : 0;

        return response()->json([
            'success' => "1",
            'register' => 'Stats retrieved successfully',
            'data' => [
                'doctor_id' => (int)$doctorId,
                'doctor_name' => $doctor->name,
                'profile_views' => $totalViews,
                'today_views' => $todayViews,
                'week_views' => $weekViews,
                'month_views' => $monthViews,
                'search_impressions' => $searchImpressions,
                'total_appointments' => $totalAppointments,
                'completed_appointments' => $completedAppointments,
                'conversion_rate' => $conversionRate,
                'trend_7_days' => $trend,
            ]
        ], 200);
    }
}
