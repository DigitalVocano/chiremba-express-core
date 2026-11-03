<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Session;
use validate;
use Sentinel;
use DB;
use App\Models\Doctors;
use App\Models\Setting;
use App\Models\BookAppointment;
use App\Models\Patient;
use DataTables;

class AppointmentController extends Controller
{
    
    public function showappointment(){
         $setting = Setting::find(1);
         return view("admin.appointment.default", compact('setting'));
    }
   
    public function appointmenttable(){
        $book = BookAppointment::with(['doctorls', 'patientls'])
                                ->where('book_appointment.is_completed','1')
                                ->leftJoin('doctors', 'book_appointment.doctor_id', '=', 'doctors.id')
                                ->leftJoin('patient', 'book_appointment.user_id', '=', 'patient.id')
                                ->select('book_appointment.*', 'doctors.name as doctor_name_joined', 'patient.name as patient_name_joined');

        return DataTables::of($book)
            ->editColumn('id', function ($book) {
                return $book->id;
            })
            ->filterColumn('doctor_name', function($query, $keyword) {
                 $query->whereRaw("LOWER(doctors.name) LIKE ?", ["%".strtolower($keyword)."%"]);
            })
            ->filterColumn('patient_name', function($query, $keyword) {
                 $query->whereRaw("LOWER(patient.name) LIKE ?", ["%".strtolower($keyword)."%"]);
            })
            ->editColumn('doctor_name', function ($book) {
                return isset($book->doctorls) ? $book->doctorls->name : "";
            })
            ->editColumn('patient_name', function ($book) {
                return isset($book->patientls) ? $book->patientls->name : "";
            })  
            ->editColumn('date', function ($book) {
                return $book->date." ".$book->slot_name;
            })  
            ->editColumn('phone', function ($book) {
                return $book->phone;
            })  
            ->editColumn('user_description', function ($book) {
                return isset($book->user_description) ? $book->user_description : "";
            })  
            ->editColumn('status', function ($book) {
                $statusText = '';
                $badgeClass = '';
                switch ($book->status) {
                    case '1': $statusText = __("Received"); $badgeClass = 'bg-blue-100 text-blue-800'; break;
                    case '2': $statusText = __("Approved"); $badgeClass = 'bg-green-100 text-green-800'; break;
                    case '3': $statusText = __("In Process"); $badgeClass = 'bg-indigo-100 text-indigo-800'; break;
                    case '4': $statusText = __("Completed"); $badgeClass = 'bg-purple-100 text-purple-800'; break;
                    case '5': $statusText = __("Rejected"); $badgeClass = 'bg-red-100 text-red-800'; break;
                    case '6': $statusText = __("Refunded"); $badgeClass = 'bg-gray-100 text-gray-800'; break;
                    default: $statusText = __("Absent"); $badgeClass = 'bg-yellow-100 text-yellow-800'; break;
                }
                return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' . $badgeClass . ' dark:bg-opacity-40 dark:text-white">' . $statusText . '</span>';
            }) 
            
            ->editColumn('action', function ($book) {
                $actions = '';
                $approveUrl = route('admin.appointment.changestatus', ['id' => $book->id, 'status_id' => 2]);
                $rejectUrl = route('admin.appointment.changestatus', ['id' => $book->id, 'status_id' => 5]);
                $completeUrl = route('admin.appointment.changestatus', ['id' => $book->id, 'status_id' => 4]);
                $refundUrl = route('admin.appointment.refund', ['id' => $book->id]);

                if (in_array($book->status, [1, 3])) { // Received or In Process
                    $actions .= '<a href="' . $approveUrl . '" class="inline-flex items-center px-3 py-1.5 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">Approve</a>';
                }
                if (in_array($book->status, [1, 2, 3])) { // Received, Approved, or In Process
                    $actions .= '<a href="' . $rejectUrl . '" class="ml-2 inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">Reject</a>';
                }
                if ($book->status == 2) { // Approved
                    $actions .= '<a href="' . $completeUrl . '" class="ml-2 inline-flex items-center px-3 py-1.5 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700">Complete</a>';
                }
                if ($book->status == 5) { // Rejected
                    $actions .= '<a href="' . $refundUrl . '" class="inline-flex items-center px-3 py-1.5 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600">Refund</a>';
                }
                return '<div class="flex items-center">' . $actions . '</div>';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function latsrappointmenttable(){
       $book = BookAppointment::with(['doctorls','patientls'])
                                ->where('book_appointment.is_completed','1')
                                ->where("book_appointment.date", date("Y-m-d"))
                                ->leftJoin('doctors', 'book_appointment.doctor_id', '=', 'doctors.id')
                                ->leftJoin('patient', 'book_appointment.user_id', '=', 'patient.id')
                                ->select('book_appointment.*', 'doctors.name as doctor_name_joined', 'patient.name as patient_name_joined');

           return DataTables::of($book)
            ->editColumn('id', function ($book) {
                return $book->id;
            })
            ->filterColumn('doctor_name', function($query, $keyword) {
                 $query->whereRaw("LOWER(doctors.name) LIKE ?", ["%".strtolower($keyword)."%"]);
            })
            ->filterColumn('patient_name', function($query, $keyword) {
                 $query->whereRaw("LOWER(patient.name) LIKE ?", ["%".strtolower($keyword)."%"]);
            })
            ->editColumn('doctor_name', function ($book) {
                return isset($book->doctorls)?$book->doctorls->name:"";
            })
            ->editColumn('patient_name', function ($book) {
                return isset($book->patientls)?$book->patientls->name:"";
            })  
            ->editColumn('date', function ($book) {
                return $book->date." ".$book->slot_name;
            })  
            ->editColumn('phone', function ($book) {
                return $book->phone;
            })  
            ->editColumn('user_description', function ($book) {
                return isset($book->user_description)?$book->user_description:"";
            })  
            ->editColumn('status', function ($book) {
                $statusText = '';
                $badgeClass = '';
                switch ($book->status) {
                    case '1': $statusText = __("Received"); $badgeClass = 'bg-blue-100 text-blue-800'; break;
                    case '2': $statusText = __("Approved"); $badgeClass = 'bg-green-100 text-green-800'; break;
                    case '3': $statusText = __("In Process"); $badgeClass = 'bg-indigo-100 text-indigo-800'; break;
                    case '4': $statusText = __("Completed"); $badgeClass = 'bg-purple-100 text-purple-800'; break;
                    case '5': $statusText = __("Rejected"); $badgeClass = 'bg-red-100 text-red-800'; break;
                    case '6': $statusText = __("Refunded"); $badgeClass = 'bg-gray-100 text-gray-800'; break;
                    default: $statusText = __("Absent"); $badgeClass = 'bg-yellow-100 text-yellow-800'; break;
                }
                return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' . $badgeClass . ' dark:bg-opacity-40 dark:text-white">' . $statusText . '</span>';
            }) 
            ->rawColumns(['status'])
            ->make(true);
    }

    public function changeappstatus($id,$status){
        try { $data = app(\App\Services\AppointmentService::class)->updateStatus($id, $status); } catch (\Exception $e) { $data=BookAppointment::find($id); if ($data) { $data->status=$status; $data->save(); } }
        $data->status=$status;
        $data->save();
        $msg = '';
        switch ($status) {
            case 2: $msg = __('Appointment Approved Successfully'); break;
            case 3: $msg = __('Appointment set to In Process'); break;
            case 4: $msg = __('Appointment marked as Completed'); break;
            case 5: $msg = __('Appointment Rejected Successfully'); break;
            default: $msg = __('Appointment status updated'); break;
        }

        Session::flash('message',$msg); 
        Session::flash('alert-class', 'alert-success');
        return redirect("admin/appointment");
    }

    public function show_refundappointment($id){            
            $data=BookAppointment::find($id);
            if($data){
                $result = $this->refunded_order_amount($data->payment_mode,$data->transaction_id,$data->consultation_fees);
                $data->status = 6;
                $data->save();
                $msg = __("Refund Successfully");
                Session::flash('message',$msg); 
                Session::flash('alert-class', 'alert-success');
                return redirect()->back();
            }else{
                Session::flash('message',__("something getting wrong")); 
                Session::flash('alert-class', 'alert-danger');
                return redirect()->back();
            }            
    }

     public function refunded_order_amount($method,$token,$amount){
       
        if($method=="braintree"){
                $gateway = new \Braintree\Gateway([
                    'environment' => env('BRAINTREE_ENV'),
                    'merchantId' => env('BRAINTREE_MERCHANT_ID'),
                    'publicKey' => env('BRAINTREE_PUBLIC_KEY'),
                    'privateKey' => env('BRAINTREE_PRIVATE_KEY')
                 ]);
                 $result = $gateway->transaction()->refund($token);
                 if($result->success==true){
                    return 1;
                 }else{
                    return 0;
                 }
        }else if($method=="stripe"){
                \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                $refund = \Stripe\Refund::create([
                    'charge' => $token,
                    'amount' => (int)($amount * 100),  // For 10 $
                ]);

        }else{
            return 1;
        }
    }

     public function notification($act){
      $data=array();
      if($act==1){
         $result=$this->haveOrdersNotification();
           $orderdata=$this->haveOrdersdata();
            if(isset($result)){
               $data = array(
                      "status" => http_response_code(),
                      "request" => "success",
                      "response" => array(
                      "message" => __("Request Completed Successfully"),
                      "total" => $result,
                      "orderdata"=>$orderdata
               )
             );
           }
           $updatenotify=$this->updatenotify();

      }
      else{
           $result=$this->haveOrdersNotification();
           $orderdata=$this->haveOrdersdata();
            if(isset($result)){
               $data = array(
                      "status" => http_response_code(),
                      "request" => "success",
                      "response" => array(
                      "message" => __("Request Completed Successfully"),
                      "total" => $result,
                      "orderdata"=>$orderdata
               )
             );
           }
       }
       return $data;
     }

     public function haveOrdersNotification(){
        $order=BookAppointment::where("notify",'1')->get();
        return count($order);
     }
      public function haveOrdersdata(){
        $order=BookAppointment::where("notify",'1')->get();
        return count($order);
     }

     public function updatenotify(){
      $order=BookAppointment::where("notify",'1')->get();
      foreach ($order as $k) {
         $k->notify='0';
         $k->save();
      }
      return "done";
     }
}