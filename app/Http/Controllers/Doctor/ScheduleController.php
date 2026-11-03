<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Setting;
use App\Models\Doctors;
use App\Models\Schedule;
use App\Models\SlotTiming;
use App\Models\Doctor_Hoilday;
use DateTime;

class ScheduleController extends Controller
{
    public function doctortimingfront(Request $request)
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '2') {
            $setting = Setting::find(1);
            $doctordata = Doctors::find(Session::get("user_id"));
            $datats = Schedule::with('getslotls')->where("doctor_id", Session::get("user_id"))->get();
            foreach ($datats as $k) {
                $k->options = $this->getdurationoption($k->start_time, $k->end_time, $k->duration);
            }
            return view("user.doctor.doctortiming")->with("setting", $setting)->with("doctordata", $doctordata)->with("data", $datats);
        } else {
            return redirect()->route('doctor.login');
        }
    }

    public function updatedoctortiming(Request $request)
    {
        $arr = $request->get("arr");
        $setting = Setting::first();
        if ($setting && $setting->is_doctor_subscription_enabled) {
            $doctor = Doctors::find($request->get("doctor_id"));
            if ($doctor && $doctor->hasFeature('max_time_slots')) {
                $limit = (int) $doctor->getFeatureValue('max_time_slots', 0);
                $incomingSlotCount = 0;
                if (!empty($arr) && $limit > 0) {
                    foreach ($arr as $day) {
                        if (is_array($day) && !empty($day['start_time'])) {
                            $start_times = array_values($day['start_time']);
                            $end_times = array_values($day['end_time']);
                            $durations = array_values($day['duration']);
                            foreach ($start_times as $key => $start) {
                                if (!empty($start) && !empty($end_times[$key]) && !empty($durations[$key])) {
                                    try {
                                        $startTimeObj = new DateTime($start);
                                        $endTimeObj = new DateTime($end_times[$key]);
                                        $duration = (int)$durations[$key];
                                        if ($duration > 0 && $endTimeObj > $startTimeObj) {
                                            $minutes = ($endTimeObj->getTimestamp() - $startTimeObj->getTimestamp()) / 60;
                                            $incomingSlotCount += floor($minutes / $duration);
                                        }
                                    } catch (\Exception $e) {}
                                }
                            }
                        }
                    }
                    if ($incomingSlotCount > $limit) {
                        return redirect()->back()->with('message', __('You have exceeded the maximum number of time slots allowed by your subscription plan.'))->with('alert-class', 'alert-danger');
                    }
                }
            }
        }

        if (!empty($arr)) {
            $removedata = Schedule::where("doctor_id", $request->get("doctor_id"))->get();
            if (count($removedata) > 0) {
                foreach ($removedata as $k) {
                    SlotTiming::where("schedule_id", $k->id)->delete();
                    $k->delete();
                }
            }
            for ($i = 0; $i < 7; $i++) {
                if (isset($arr[$i]) && is_array($arr[$i]) && !empty($arr[$i]['start_time'])) {
                    $start_date = array_values($arr[$i]['start_time']);
                    $end_date = array_values($arr[$i]['end_time']);
                    $duration = array_values($arr[$i]['duration']);
                    for ($j = 0; $j < count($start_date); $j++) {
                        if (isset($start_date[$j]) && $start_date[$j] != "" && isset($end_date[$j]) && $end_date[$j] != "" && isset($duration[$j]) && $duration[$j] != "") {
                            $getslot = $this->getslotvalue($start_date[$j], $end_date[$j], $duration[$j]);
                            $store = new Schedule();
                            $store->doctor_id = $request->get("doctor_id");
                            $store->day_id = $i;
                            $store->start_time = $start_date[$j];
                            $store->end_time = $end_date[$j];
                            $store->duration = $duration[$j];
                            $store->save();
                            foreach ($getslot as $g) {
                                $aslot = new SlotTiming();
                                $aslot->schedule_id = $store->id;
                                $aslot->slot = $g;
                                $aslot->save();
                            }
                        }
                    }
                }
            }
            Session::flash('message', __("Schedule Save Successfully"));
            Session::flash('alert-class', 'alert-success');
            return redirect()->back();
        }
        return redirect()->back();
    }

    public function show_doctor_hoilday()
    {
        if (Session::get("user_id") != "" && Session::get("role_id") == '2') {
            $setting = Setting::find(1);
            $doctorhoilday = Doctor_Hoilday::where("doctor_id", Session::get("user_id"))->get();
            $doctordata = Doctors::with('department')->find(Session::get("user_id"));
            return view("user.doctor.doctor_hoilday", compact('setting', 'doctordata', 'doctorhoilday'));
        } else {
            return redirect()->route('doctor.login');
        }
    }

    public function show_post_my_hoilday(Request $request)
    {
        $store = new Doctor_Hoilday();
        $store->start_date = $request->get("start_date");
        $store->end_date = $request->get("end_date");
        $store->description = $request->get("description");
        $store->doctor_id = Session::get("user_id");
        $store->save();
        Session::flash('message', __("My Hoilday Add Successfully"));
        Session::flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    public function deletedoctorhoilday($id)
    {
        Doctor_Hoilday::where("id", $id)->delete();
        Session::flash('message', __("Hoilday Delete Successfully"));
        Session::flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    public function getslotvalue($start_time, $end_time, $duration)
    {
        $duration = (int)$duration;
        $slots = [];
        if ($duration <= 0) {
            return $slots;
        }

        try {
            $start = new DateTime($start_time);
            $end = new DateTime($end_time);

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

    public function generateslotfront(Request $request)
    {
        $slots = $this->getslotvalue(
            $request->get('start_time'),
            $request->get('end_time'),
            $request->get('duration')
        );
        return response()->json($slots);
    }

    public function findpossibletime(Request $request)
    {
        $slots = $this->getdurationoption(
            $request->get('start_time'),
            $request->get('end_time'),
            $request->get('duration', 15)
        );
        return response()->json($slots);
    }
}
