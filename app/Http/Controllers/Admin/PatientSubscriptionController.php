<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use Session;
use validate;
use Sentinel;
use DB;
use App\Models\PatientSubscription;
use App\Models\PatientSubscriber;
use App\Models\Setting;
use App\Models\Patient;
use Mail;
use DateTime;
use DateInterval;
use DataTables;
class PatientSubscriptionController extends Controller
{
    
   public function show_UserSubscription(){
       return view("admin.UserSubscription.default");
   }
   
 
    public function Usersubscriber_doc(){
       return view("admin.UserSubscription.subscriber_doc");
   }
   
   public function show_UserSubscriptiontable(){
           $UserSubscription = UserSubscription::all();
       
           return DataTables::of($UserSubscription)
           
            ->editColumn('month', function ($UserSubscription) {
                return $UserSubscription->month;
            })
            ->editColumn('amount', function ($UserSubscription) {
                $getcurreny = explode("-",Setting::find(1)->currency);
                
                return $getcurreny[1].$UserSubscription->price;
            })  
            ->editColumn('action', function ($UserSubscription) {
                  $payurl=url('admin/edit_UserSubscription_price',array('id'=>$UserSubscription->id));
                return '<a href="'.$payurl.'" class=" btn btn-success" style="color:white !important" >'.__("Edit").'</a>';
            }) 
            ->make(true);
    }
    
    
    public function show_subscribetable(){
           $UserSubscription = UserSubscriber::wherenotnull('user_id')->get();
           $data =array();
           foreach($UserSubscription as $s){
               $ls = Patient::find($s->user_id);
               if(isset($ls)){
                   $data[]=$s;
               }
           }
           $UserSubscription = $data;
           return DataTables::of($UserSubscription)
           
            ->editColumn('id', function ($UserSubscription) {
                return $UserSubscription->id;
            })
            ->editColumn('user_id', function ($UserSubscription) {
                // return $UserSubscription->user_id;
                return Patient::find($UserSubscription->user_id)?Patient::find($UserSubscription->user_id)->name:'';
            })
            ->editColumn('payment_type', function ($UserSubscription) {
                
                if($UserSubscription->payment_type == 1){
                    return  __("Braintree");
                }
                if($UserSubscription->payment_type == 2){
                     return __("Bank Deposit");
                }else if($UserSubscription->payment_type == 3){
                    return __("Razorpay");
                }else if($UserSubscription->payment_type == 4){
                    return __("Paystack");
                }else if($UserSubscription->payment_type == 5){
                    return __("Stripe");
                }
            })
           ->editColumn('amount', function ($UserSubscription) {
                $getcurreny = explode("-",Setting::find(1)->currency);
                return $getcurreny[1].$UserSubscription->amount;
            }) 
           /* ->editColumn('transaction_id', function ($UserSubscription) {
                return $UserSubscription->transaction_id;
            })
            ->editColumn('description', function ($UserSubscription) {
                return $UserSubscription->description;
            })
            ->editColumn('UserSubscription_id', function ($UserSubscription) {
                return $UserSubscription->UserSubscription_id;
            })*/
            ->editColumn('status', function ($UserSubscription) {
                // return $UserSubscription->status;
                if($UserSubscription->status == 1){
                    return __("Not Active");
                }
                if($UserSubscription->status == 2){
                     return __("Active");
                }
                 if($UserSubscription->status == 3){
                     return __("Expired");
                }
                if($UserSubscription->status == 4){
                     return "Reject";
                }
                if($UserSubscription->status == 6){
                     return "Disable";
                }
            })
            /*->editColumn('date', function ($UserSubscription) {
                return $UserSubscription->date;
            })*/
          
            ->editColumn('deposit_image', function ($UserSubscription) {
                if($UserSubscription->deposit_image != "" && $UserSubscription->deposit_image != null){
                    return asset("upload/bank_receipt").'/'.$UserSubscription->deposit_image;
                }else{
                   // return asset("upload/bank_receipt/images.png");
                }
                
            })
             
            ->editColumn('action', function ($UserSubscription) {
                
               $view = __("View");
                $payurl=url('admin/view_UserSubscription_price',array('id'=>$UserSubscription->id));
                // return '<a href="'.$payurl.'" class=" btn btn-success" style="color:white !important" >'.$view.'</a>';
                $bt="";
                $ex="";
                $rx =""; 
                $active =""; 
                $disable =""; 
                if($UserSubscription->status == 2)
                {
                    $disable ='<a onclick="disable_order('.$UserSubscription->id.',6)" rel="tooltip"  class="btn btn-sm btn-warning" >Disable</a>';
                }
                if($UserSubscription->status == 6)
                {
                    $active ='<a onclick="active_order('.$UserSubscription->id.',2)" rel="tooltip" class="btn btn-sm btn-success" >Active</a>';
                }
                return '<a href="'.$payurl.'" class=" btn btn-sm btn-success" style="color:white !important" >'.$view.'</a> '.$disable.$active; 
                
            }) 
            ->make(true);
    }
    
     public function disable_order(Request $request)
    {
      $data = UserSubscriber::find($request->get("record_id"));
        $data->status = $request->get("status");
        $data->save();
    }

    public function active_order(Request $request)
    {
      $data = UserSubscriber::find($request->get("record_id"));
        $data->status = $request->get("status");
        $data->save();
    }
    
    public function view_UserSubscription_price($id)
    {
       $data = UserSubscriber::find($id);
       $data->user_id = Users::find($data->user_id)?Users::find($data->user_id)->name:'';
       $data->UserSubscription_id = UserSubscription::find($data->UserSubscription_id)?UserSubscription::find($data->UserSubscription_id)->month:'';
       return view("admin.UserSubscription.view_subscriber",compact("data","id"));
    }
   
   public function edit_UserSubscription_price($id){
       $data = UserSubscription::find($id);
       
       return view("admin.UserSubscription.save",compact("data","id"));
   }
   
   public function update_subscriptio_price(Request $request){
        $store = UserSubscription::find($request->get("id"));
     	if($store){
     	    $store->price=$request->get("price");
         	$store->save();
         	Session::flash('message',__("UserSubscription Price Update Successfully")); 
            Session::flash('alert-class', 'alert-success');
            return redirect()->route("UserSubscription");
     	}else{
     	    	Session::flash('message',__("something getting wrong"));
                Session::flash('alert-class', 'alert-danger');
                return redirect()->route("UserSubscription");
     	}
     	
   }
   
   


}
