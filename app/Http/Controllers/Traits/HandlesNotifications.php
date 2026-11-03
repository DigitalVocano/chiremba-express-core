<?php

namespace App\Http\Controllers\Traits;

use App\Models\Setting;
use App\Models\TokenData;
use Illuminate\Support\Facades\File;
use Google_Client;

trait HandlesNotifications
{
    public function generateAccessToken()
    {
        $setting = Setting::find(1);
        $path = public_path('upload/jsonfile/' . $setting->not_json_filename);
        if (!File::exists($path)) {
            $path = storage_path('app/jsonfile/' . $setting->not_json_filename);
        }
        $contents = File::get($path);
        $accountJson = json_decode($contents, true);

        $scopes = [
            "https://www.googleapis.com/auth/userinfo.email",
            "https://www.googleapis.com/auth/firebase.database",
            "https://www.googleapis.com/auth/firebase.messaging"
        ];

        $client = new Google_Client();
        $client->setAuthConfig($accountJson);
        $client->setScopes($scopes);
        $accessToken = $client->fetchAccessTokenWithAssertion()["access_token"];
        return $accessToken;
    }

    public function sendNotifications($msg, $id, $field, $order_id)
    {
        $notifService = new \App\Services\NotificationService();
        $sent = $notifService->sendNotification($msg, $id, $field, $order_id, [
            'order_id' => (string)$order_id,
            'type' => (string)$field,
            'receiver_type' => (string)$field,
        ]);

        return $sent ? 1 : 0;
    }
}
