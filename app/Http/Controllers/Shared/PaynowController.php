<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session; // <-- Add this
use Illuminate\Support\Str; // <-- Add this if not already present

class PaynowController extends Controller
{
 
    public function paynoww(){
       return view('paynow');
    }

    public function make_payment(Request $request){
        try {
            // NOTE: This is an example. Replace with your actual logic for getting amount and email.
            $amount = 10.00; 
            $email = 'freedhleza@gmail.com';
            $transactionId = 'KUMBA-' . Str::uuid(); // Create a unique reference for your system

            $paynow = $this->paynow(); // Assuming $this->paynow() initializes the Paynow object
            $payment = $paynow->createPayment($transactionId, $email);
            $payment->add('Wallet Top-up', $amount);
            
            $response = $paynow->send($payment);
            
            if ($response->success()) {
              $link = $response->redirectUrl();
              $pollUrl = $response->pollUrl();

              // **THE FIX: Store data permanently in the session**
              Session::put('pollUrl', $pollUrl);
              Session::put('transactionId', $transactionId);

              return redirect()->to($link);

            } else {
                 // Handle initiation failure
                 return redirect()->to('/patient_subscription_plan')->with('message', 'Failed to initiate payment with Paynow.');
            }
        } catch (\Exception $e) {
            \Log::error("Paynow make_payment error: " . $e->getMessage());
            return redirect()->to('/patient_subscription_plan')->with('message', $e->getMessage());
        }
    }
}