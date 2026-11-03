<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use Session;
use validate;
use Sentinel;
use DB;
use App\Models\Services;
use App\Models\SlotTiming;
use App\Models\Schedule;
use App\Models\Complement_settlement;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Patient;
use App\Models\BookAppointment;
use App\Models\Settlement;
use DataTables;
use App\Models\TokenData;
use App\Models\User;
use App\Models\Contact;
use App\Models\Newsletter;
use App\Models\Doctors;
use App\Models\Subscriber;
use App\Models\Subscription;

class PaymentController extends Controller
{
    
   public function show_pending_payment(){
       
       return view("admin.payment.pending");
   }
   
public function show_news()
{
    $departments = \App\Models\Services::all();
    $doctorSubscriptions = \App\Models\Subscription::whereIn('user_type', ['doctor', 'pharmacy'])->where('is_active', 1)->get();
    $patientSubscriptions = \App\Models\Subscription::where('user_type', 'patient')->where('is_active', 1)->get();

    return view("admin.news", [
        'departments' => $departments,
        'doctorSubscriptions' => $doctorSubscriptions,
        'patientSubscriptions' => $patientSubscriptions,
    ]);
}
 

public function sendnews(Request $request)
{
    $msg = $request->get("content");
    $targetAudience = $request->input('target_audience', 'doctors');
    $subscriptionTypes = $request->input('subscription'); // Ensure this is an array

    $recipients = collect();

    if ($targetAudience === 'doctors') {
        $departments = $request->input('department'); // Ensure this is an array
        $doctors = \App\Models\Doctors::where('profile_type', 1)
            ->when(!empty($departments), function ($query) use ($departments) {
                $query->whereIn('department_id', $departments);
            })
            ->get();

        foreach ($doctors as $doctor) {
            $subscriber = \App\Models\Subscriber::where('subscriber_id', $doctor->id)
                ->where('subscriber_type', \App\Models\Doctors::class)
                ->whereIn('subscription_id', $subscriptionTypes)
                ->first();
            if ($subscriber) {
                $recipients->push($doctor);
            }
        }
    } elseif ($targetAudience === 'pharmacies') {
        $pharmacies = \App\Models\Doctors::where('profile_type', 2)->get();

        foreach ($pharmacies as $pharmacy) {
            $subscriber = Subscriber::where('subscriber_id', $pharmacy->id)
                ->where('subscriber_type', \App\Models\Doctors::class)
                ->whereIn('subscription_id', $subscriptionTypes)
                ->first();
            if ($subscriber) {
                $recipients->push($pharmacy);
            }
        }
    } else { // targetAudience === 'patients'
        $patients = Patient::when(!empty($subscriptionTypes), function ($query) use ($subscriptionTypes) {
            $query->whereIn('subscription_id', $subscriptionTypes);
        })->get();

        $recipients = $recipients->merge($patients);
    }

    // Send news to subscribed users
    $subscribers = Newsletter::all();
    foreach ($subscribers as $subscriber) {
        $data = [
            'email' => $subscriber->email,
            'msg' => $msg
        ];
        try {
            Mail::send('email.news', ['user' => $data], function ($message) use ($data) {
                $message->to($data['email'], 'customer')->subject(__('messages.site_name'));
            });
        } catch (\Exception $e) {
            // Handle email sending error (e.g., log the error)
        }
    }

    // Send news to selected recipients (doctors, pharmacies, or patients)
    foreach ($recipients as $recipient) {
        $data = [
            'email' => $recipient->email,
            'msg' => $msg
        ];
        try {
            Mail::send('email.news', ['user' => $data], function ($message) use ($data) {
                $message->to($data['email'], 'recipient')->subject(__('messages.site_name'));
            });
        } catch (\Exception $e) {
            // Handle email sending error (e.g., log the error)
        }
    }

    Session::flash('message', __('messages.News Send Successfully'));
    Session::flash('alert-class', 'alert-success');
    return redirect()->back();
}
  
public function show_contact_list()
{
    return view('admin.contact');
}


     
   public function contact_list_table(){
            $book = Contact::all();
       
           return DataTables::of($book)
           ->editColumn('id', function ($book) {
                return $book->id;
            })
            ->editColumn('name', function ($book) {
                return $book->name;
            })
            ->editColumn('email', function ($book) {
                return $book->email;
            })
            ->editColumn('phone', function ($book) {
                return $book->phone;
            })
            ->editColumn('subject', function ($book) {
                return $book->subject;
            })
            ->editColumn('message', function ($book) {
                return $book->message;
            })
            
            ->addColumn('action', function ($book) {
                return '<button class="inline-flex items-center px-3 py-1.5 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">View</button>';
            })
            ->rawColumns(['action'])
            ->make(true);
   }
   
   public function show_pendingpaymenttable(){
        $book =DB::table('settlement')->select( DB::raw('DISTINCT(doctor_id) as doctor_id '),DB::raw('sum(amount) as amount'),DB::raw('count(*) as total_booking'), DB::raw('MAX(payment_date) as latest_date') )->where("status",'0')->groupBy('doctor_id')->get();
       
           return DataTables::of($book)
           
            ->editColumn('doctor_name', function ($book) {
                return Doctors::find($book->doctor_id)?Doctors::find($book->doctor_id)->name:'';
            })
            ->editColumn('date', function ($book) {
                return \Carbon\Carbon::parse($book->latest_date)->format('Y-m-d');
            })
            ->editColumn('amount', function ($book) {
                return $book->amount;
            })  
            ->editColumn('total_booking', function ($book) {
                return $book->total_booking;
            }) 
           
            ->make(true);
   }
   
   public function show_payamount($doc_id){
        $book =DB::table('settlement')->select( DB::raw('DISTINCT(doctor_id) as doctor_id '),DB::raw('sum(amount) as amount'),DB::raw('count(*) as total_booking') )->where("status",'0')->whereMonth("payment_date","<=",9)->where("doctor_id",$doc_id)->groupBy('doctor_id')->first();
        $book->name = Doctors::find($book->doctor_id)?Doctors::find($book->doctor_id)->name:'';
        return view("admin.payment.addpay")->with("book",$book);
   }
   
   public function updatepayment(Request $request){
        $store = new Complement_settlement();
        $store->translation_id = $request->get("translation_id");
        $store->amount = $request->get("amount");
        $store->date = now();
        $store->doctor_id = $request->get("doctor_id");
        $store->save();
        DB::table('settlement')->where("status",'0')->where("doctor_id",$request->get("doctor_id"))->update(["status"=>1,"settlement_id"=>$store->id]);
        
        Session::flash('message',__("Payment Store Successfully")); 
        Session::flash('alert-class', 'alert-success');
        return redirect('admin/pending_payment');
   }
   
   public function complete_payment(){
       return view("admin.payment.complete");
   }
   
   public function show_completepaymenttable(){
        $book =Complement_settlement::all();
       
           return DataTables::of($book)
           
            ->editColumn('doctor_name', function ($book) {
                return Doctors::find($book->doctor_id)?Doctors::find($book->doctor_id)->name:'';
            })
            ->editColumn('amount', function ($book) {
                return $book->amount; // You can format this with currency if needed
            })  
            ->editColumn('date', function ($book) {
                return \Carbon\Carbon::parse($book->date)->format('Y-m-d');
            })  
            ->editColumn('transaction_id', function ($book) {
                return $book->translation_id;
            })
           
            ->make(true);
   }

}
