<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AgoraService;
use Illuminate\Support\Facades\Session;

class AgoraController extends Controller
{
    protected $agoraService;

    public function __construct(AgoraService $agoraService)
    {
        $this->agoraService = $agoraService;
    }

    public function token(Request $request)
    {
        $request->validate([
            'channel_name' => 'required|string',
        ]);

        $user_id = Session::get('user_id');
        if (!$user_id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            // Role 1 = Publisher (can speak/show video)
            $token = $this->agoraService->generateToken($request->channel_name, $user_id, 1);
            return response()->json(['token' => $token]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}