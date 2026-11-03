<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Auth;
use Sentinel;
use App\Models\PaymentGatewayDetail;
use App\Models\Seller;
use App\Models\WalletTransaction;
use App\Models\Setting;
use DataTables;
class PaymentSettingController extends Controller
{
    //

    public function show_payment_setting(){
    	 if(Session::get("payment_next")==""){
    	 	Session::put("payment_next",'paynow');
    	 }
    	 $arr = array();
    	 $data = PaymentGatewayDetail::all();
    	 foreach ($data as $k) {
    	 	$arr[$k->gateway_name."_".$k->key] = $k->value;
    	 }
    	 return view("admin.paymentsetting")->with("arr",$arr);
    }



    public function show_update_gateway(Request $request){
    	//dd($request->all());
    	$next = 'paynow';
    	if($request->get("payment_gateway")=="paynow"){
            // Assuming Paynow settings are managed here
            $this->admin_update_payment_key("paynow","integration_id",$request->get("integration_id"));
            $this->admin_update_payment_key("paynow","integration_key",$request->get("integration_key"));
            $this->admin_update_payment_key("paynow","is_active",$request->get("is_active"));
            $next = 'paynow'; // Stay on Paynow tab or redirect as needed
        }
        
        if($request->get("payment_gateway")=="ecocash"){
            $this->admin_update_payment_key("ecocash","username",$request->get("username"));
            $this->admin_update_payment_key("ecocash","password",$request->get("password"));
            $this->admin_update_payment_key("ecocash","merchant_code",$request->get("merchant_code"));
            $this->admin_update_payment_key("ecocash","merchant_pin",$request->get("merchant_pin"));
            $this->admin_update_payment_key("ecocash","merchant_number",$request->get("merchant_number"));
            $this->admin_update_payment_key("ecocash","endpoint",$request->get("endpoint"));
            $this->admin_update_payment_key("ecocash","is_active",$request->get("is_active"));
            $next = 'ecocash';
        }

    	Session::flash('message',__("Payment Key Update Successfully"));
    	Session::put("payment_next",$next);
        Session::flash('alert-class', 'alert-success');
        return redirect()->route('payment-setting');
    }


    public function admin_update_payment_key($payment_key,$key,$value){
    	$data = PaymentGatewayDetail::where("gateway_name",$payment_key)->where("key",$key)->first();
    	if($data){
    		$data->value = $value;
    		$data->save();
    	}else{
    		$data = new PaymentGatewayDetail();
    		$data->gateway_name = $payment_key;
    		$data->key = $key;
    		$data->value = $value;
    		$data->save();
    	}
    	return 1;
    }

    public function showPaynowHistory()
    {
        return view("admin.paynow_history");
    }

    public function paynowHistoryTable()
    {
        $transactions = WalletTransaction::with('wallet.owner')->where(function ($query) {
            $query->where('description', 'like', '%Paynow%')
                  ->orWhere('reference', 'like', 'paynow_%');
        });


        return DataTables::of($transactions)
            ->editColumn('owner', function ($transaction) {
                if ($transaction->wallet && $transaction->wallet->owner) {
                    return $transaction->wallet->owner->name . ' (' . class_basename($transaction->wallet->owner_type) . ')';
                }
                return 'N/A';
            })
            ->editColumn('amount', function ($transaction) {
                $setting = Setting::first();
                $currency = $setting->currency ?? '$';
                return $currency . number_format($transaction->amount, 2);
            })
            ->editColumn('description', function ($transaction) {
                return $transaction->description;
            })
            ->editColumn('reference', function ($transaction) {
                return $transaction->reference;
            })
            ->editColumn('created_at', function ($transaction) {
                return $transaction->created_at->format('Y-m-d H:i:s');
            })
            ->rawColumns([])
            ->make(true);
    }



    public function overWriteEnvFile($type, $val)
    {
            $path = base_path('.env');
            if (file_exists($path)) {
                $val = '"'.trim($val).'"';
                if(is_numeric(strpos(file_get_contents($path), $type)) && strpos(file_get_contents($path), $type) >= 0){
                    file_put_contents($path, str_replace(
                        $type.'="'.env($type).'"', $type.'='.$val, file_get_contents($path)
                    ));
                }
                else{
                    file_put_contents($path, file_get_contents($path)."\r\n".$type.'='.$val);
                }
            }
        return 1;
    }
}
