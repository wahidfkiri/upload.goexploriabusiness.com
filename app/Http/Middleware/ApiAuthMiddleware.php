<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key');
        $apiSecret = $request->header('X-API-Secret');
        
        if ($apiKey !== env('API_KEY') || $apiSecret !== env('API_SECRET')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        return $next($request);
    }
}