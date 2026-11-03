<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\DeveloperKey;

class ValidateDeveloperApiKey
{
    /**
     * Handle an incoming developer API request.
     */
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-KEY') 
            ?: $request->header('x-api-key') 
            ?: $request->bearerToken() 
            ?: $request->query('api_key');

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'status_code' => 401,
                'error' => 'Unauthorized: Missing API Key',
                'message' => 'Please provide your API Key via the X-API-KEY header or Bearer Token.',
                'documentation_url' => url('/developers/api')
            ], 401);
        }

        $devKey = DeveloperKey::where('api_key', $apiKey)->first();

        if (!$devKey) {
            return response()->json([
                'success' => false,
                'status_code' => 403,
                'error' => 'Forbidden: Invalid API Key',
                'message' => 'The provided API key does not exist or has expired.',
                'documentation_url' => url('/developers/api')
            ], 403);
        }

        if (!$devKey->is_active) {
            return response()->json([
                'success' => false,
                'status_code' => 403,
                'error' => 'Forbidden: API Key Suspended',
                'message' => 'Your developer API key has been suspended. Please contact support.',
            ], 403);
        }

        // Update last used timestamp
        $devKey->update(['last_used_at' => now()]);
        $request->attributes->set('developer_key', $devKey);

        return $next($request);
    }
}
