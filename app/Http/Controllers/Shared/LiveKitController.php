<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\VideoGrant;

class LiveKitController extends Controller
{
    public function token(Request $request)
    {
        $request->validate([
            'channel_name' => 'required|string',
        ]);

        $user_id = null;
        if ($request->has('user_id') && !empty($request->user_id)) {
            $user_id = (string) $request->user_id;
        } elseif (Auth::check()) {
            $user_id = 'user_' . Auth::user()->id;
        } elseif (Session::has('user_id')) {
            $user_id = 'user_' . Session::get('user_id');
        } else {
            $user_id = 'participant_' . rand(1000, 9999) . '_' . substr(uniqid(), -6);
        }

        $userName = $request->user_name ?? ("User_" . substr($user_id, -6));

        Log::info("LiveKit: Generating token for Identity {$user_id}, Name {$userName}, Channel {$request->channel_name}");

        $apiKey = env('LIVEKIT_API_KEY', 'APIb5BFgiPRoHsp');
        $apiSecret = env('LIVEKIT_API_SECRET', 'NHi9hA9yXfh5ltt4jlUZgF5vtyntEeyuMfZr5zee254E');
        
        if (empty($apiKey) || empty($apiSecret)) {
            Log::error("LiveKit: Missing LIVEKIT_API_KEY or LIVEKIT_API_SECRET in .env");
            return response()->json(['error' => 'Server communication error with telephony provider.'], 500);
        }

        try {
            // Generate LiveKit Token
            $tokenOptions = (new AccessTokenOptions())
                ->setIdentity((string) $user_id)
                ->setName((string) $userName);
            
            $grant = (new VideoGrant())
                ->setRoomJoin()
                ->setRoomName($request->channel_name);

            $token = (new AccessToken($apiKey, $apiSecret))
                ->init($tokenOptions)
                ->setGrant($grant);

            Log::info("LiveKit: Token generated successfully for room: " . $request->channel_name);
            
            return response()->json([
                'token' => $token->toJwt(), 
                'channel' => $request->channel_name,
                'url' => env('LIVEKIT_URL', 'wss://linkit-6u57ihxm.livekit.cloud')
            ]);
        } catch (\Exception $e) {
            Log::error("LiveKit Exception: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function sendCallNotification(Request $request, \App\Services\NotificationService $notificationService)
    {
        $request->validate([
            'receiver_id' => 'required',
            'receiver_type' => 'required|in:user_id,doctor_id',
            'channel_name' => 'required|string',
            'caller_name' => 'nullable|string',
            'call_type' => 'nullable|string'
        ]);

        $receiverId = $request->receiver_id;
        $receiverType = $request->receiver_type;
        $channelName = $request->channel_name;
        $callerName = $request->caller_name ?? 'Incoming Call';
        $callType = $request->call_type ?? 'video';

        // Cache the active call channel mapping so cancel-call can find the recipient even if only channel_name is passed
        \Illuminate\Support\Facades\Cache::put("call_channel_{$channelName}", [
            'receiver_id' => $receiverId,
            'receiver_type' => $receiverType,
        ], 120);

        $success = $notificationService->sendCallPushNotification(
            $receiverId,
            $receiverType,
            $channelName,
            $callerName,
            $callType
        );

        return response()->json([
            'success' => $success ? "1" : "0",
            'message' => $success ? "Call notification sent successfully" : "Receiver token not found or offline"
        ], 200);
    }

    public function cancelCallNotification(Request $request, \App\Services\NotificationService $notificationService)
    {
        $request->validate([
            'channel_name' => 'required|string',
        ]);

        $channelName = $request->channel_name;
        $receiverId = $request->receiver_id;
        $receiverType = $request->receiver_type;

        if (empty($receiverId) || empty($receiverType)) {
            $cached = \Illuminate\Support\Facades\Cache::get("call_channel_{$channelName}");
            if ($cached) {
                $receiverId = $cached['receiver_id'] ?? null;
                $receiverType = $cached['receiver_type'] ?? null;
            }
        }

        \Illuminate\Support\Facades\Cache::forget("call_channel_{$channelName}");

        $success = false;
        if (!empty($receiverId) && !empty($receiverType)) {
            $success = $notificationService->sendCancelCallPushNotification(
                $receiverId,
                $receiverType,
                $channelName
            );
        }

        return response()->json([
            'success' => $success ? "1" : "0",
            'message' => "Cancel notification processed"
        ], 200);
    }
}

