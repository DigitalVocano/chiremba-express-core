<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use DateTime;
use DateTimeZone;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Paynow\Payments\Paynow;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * The single instance of the settings model.
     * @var Setting|null
     */
    protected static $settings_instance = null;

    public function __construct()
    {
        $this->setApplicationTimezone();
    }

    /**
     * Sets the application's default timezone based on the admin setting.
     * This method is memoized to only run once per request.
     */
    protected function setApplicationTimezone()
    {
        // Run this logic only once
        if (self::$settings_instance === null) {
            try {
                self::$settings_instance = Setting::first();
                if (self::$settings_instance && self::$settings_instance->timezone) {
                    if (in_array(self::$settings_instance->timezone, timezone_identifiers_list())) {
                        config(['app.timezone' => self::$settings_instance->timezone]);
                        date_default_timezone_set(self::$settings_instance->timezone);
                    } else {
                        Log::warning('Invalid timezone identifier in settings: ' . self::$settings_instance->timezone);
                    }
                }
            } catch (\Exception $e) {
                // This can happen if the database is not yet migrated.
                // Log the error but don't crash the application.
                Log::error('Could not set timezone from database: ' . $e->getMessage());
            }
        }
    }

    public function paynow($pollUrl="") {
        \Log::info('Paynow: Initializing Paynow object.');
        $integrationId = env('PAYNOW_INTEGRATION_ID');
        $integrationKey = env('PAYNOW_INTEGRATION_KEY');
        $checkUrl = env('PAYNOW_CHECK_URL');
        $resultUrl = env('PAYNOW_RESULT_URL');

        \Log::info('Paynow: ENV PAYNOW_INTEGRATION_ID: ' . $integrationId);
        \Log::info('Paynow: ENV PAYNOW_INTEGRATION_KEY: ' . $integrationKey);
        \Log::info('Paynow: ENV PAYNOW_CHECK_URL: ' . $checkUrl);
        \Log::info('Paynow: ENV PAYNOW_RESULT_URL: ' . $resultUrl);

        // Ensure values are not null/empty, or cast them appropriately
        $integrationId = (int)$integrationId; // Cast to int
        $integrationKey = (string)$integrationKey; // Cast to string

        try {
        $paynow = new \Paynow\Payments\Paynow(
            $integrationId,
            $integrationKey,
            $checkUrl, // Your callback URL for Paynow
            $resultUrl // Your result URL for Paynow
        );
        \Log::info('Paynow: Paynow object initialized successfully.');
        return $paynow;
        } catch (\Exception $e) {
            \Log::error('Paynow: Failed to create Paynow object: ' . $e->getMessage());
            throw $e; // Re-throw to be caught by calling method
        }
    }

    /**
     * Determines the correct redirect route for wallet-related actions based on context and user role.
     *
     * @param array|null $topUpData Data stored in session for the wallet top-up.
     * @param int $roleId The role ID of the current user (1=Patient, 2=Doctor, 3=Pharmacy).
     * @return array An associative array containing 'route' (route name) and 'params' (route parameters).
     */
    protected function getWalletRedirectRoute($topUpData, $roleId)
    {
        $redirectRouteName = 'userdashboard'; // Default fallback route name
        $redirectParams = [];

        if ($topUpData) {
            $context = $topUpData['context'] ?? 'general_topup';

            switch ($context) {
                case 'general_topup':
                    if ($roleId == 1) { // Patient
                        $redirectRouteName = 'medical.wallet';
                    } elseif ($roleId == 2) { // Doctor
                        $redirectRouteName = 'doctor.wallet';
                    } elseif ($roleId == 3) { // Pharmacy
                        $redirectRouteName = 'pharmacy.wallet';
                    }
                    break;
                case 'patient_subscription_topup':
                    $redirectRouteName = 'patient_subscription_plan';
                    break;
                case 'appointment_topup':
                    // For appointment top-up, we might want to return to the doctor's profile or user dashboard
                    $redirectRouteName = 'userdashboard'; // Fallback for now, can be refined
                    break;
                case 'doctor_subscription_topup':
                    $redirectRouteName = 'doctor_subscription_plan';
                    break;
                case 'pharmacy_order_topup':
                    $redirectRouteName = 'cart.view';
                    break;
            }
        }
        return ['route' => $redirectRouteName, 'params' => $redirectParams];
    }

    /**
     * Helper to redirect with an error message based on the context.
     *
     * @param string $errorMessage
     * @param array|null $topUpData
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function _redirectWithError($errorMessage, $topUpData = null)
    {
        $roleId = \Session::get('role_id'); // Get current user's role

        if ($topUpData === null) {
            $topUpData = \Session::get('wallet_topup_data');
        }

        $redirectInfo = $this->getWalletRedirectRoute($topUpData, $roleId);
        $redirectRouteName = $redirectInfo['route'];
        $redirectParams = $redirectInfo['params'];

        // Clear session data related to the top-up attempt
        \Session::forget(['wallet_topup_data', 'paypal_payment_id', 'appointment_data_for_topup', 'pharmacy_order_data_for_topup', 'pollUrl', 'transactionId']);

        // Flash the message
        \Session::flash('message', $errorMessage);
        \Session::flash('alert-class', 'alert-danger');

        // Redirect to the determined route
        return redirect()->route($redirectRouteName, $redirectParams);
    }

    public function create_session(){
         $application_id = 7844;
        $auth_key = "Cxjzf67FXkmuDPv";
        $authSecret = "6bA7jYbE5ffvQhU";
        $nonce = rand();
        $timestamp = time();
        $stringForSignature = "application_id=".$application_id."&auth_key=".$auth_key."&nonce=".$nonce."&timestamp=".$timestamp;
        $signature = hash_hmac( 'sha1', $stringForSignature , $authSecret);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.connectycube.com/session');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{"application_id": "'.$application_id.'", "auth_key": "'.$auth_key.'", "nonce": "'.$nonce.'", "timestamp": "'.$timestamp.'",  "signature": "'.$signature.'"}');

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        $response = json_decode($result,true);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        // echo "<pre>";
        // print_r($response);
        // exit();
        return $response['session']['token'];
    }

   public function retrieveall(){
        $token = $this->create_session();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.connectycube.com/users');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        curl_setopt($ch, CURLOPT_POSTFIELDS, "per_page=1000");

        $headers = array();
        $headers[] = 'Cb-Token: '.$token;
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $response = json_decode($result,true);
        return $response;
   }


   public function signupconnectycude($name,$password,$email,$phone,$name_login){
        $token = $this->create_session();
        $ch = curl_init();
       // $name_login = $phone.rand()."#".$type;//trim($name).rand();
        curl_setopt($ch, CURLOPT_URL, 'https://api.connectycube.com/users');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{"user": {"login": "'.$name_login.'", "password": "'.$password.'", "email": "'.$email.'", "facebook_id": "", "twitter_id": "", "full_name": "'.$name.'", "phone": "'.$phone.'"}}');

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Cb-Token: '.$token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        $response = json_decode($result,true);
        // echo "<pre>";
        // print_r($response);
        // exit();
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        if(isset($response['user'])){
            return $response['user']['id'];
        }else if(!empty($response['errors']['password'][0])){
            return  '0-'.$response['errors']['password'][0];
        }else if(!empty($response['errors']['phone'][0])){
            return  '0-'.$response['errors']['phone'][0];
        }else{
           return  '0-'.$response['errors']['base'][0];
        }

   }

   public function deleteconnectycubeuser($userId)
    {
        if (empty($userId) || env('ConnectyCube') != true) {
            return; // No user to delete or feature is disabled
        }
        $token = $this->create_session();
        if (!$token) {
            \Log::error("ConnectyCube: Failed to create session for user deletion.");
            return;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.connectycube.com/users/' . $userId . '.json');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

        $headers = array();
        $headers[] = 'Cb-Token: ' . $token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            \Log::error('ConnectyCube Deletion Error:' . curl_error($ch));
        }
        curl_close($ch);

        if ($httpcode != 200) {
            \Log::error("ConnectyCube: Failed to delete user {$userId}. Response: " . $result);
        } else {
            \Log::info("ConnectyCube: Successfully deleted user {$userId}.");
        }
    }
}
