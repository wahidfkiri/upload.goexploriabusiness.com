<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CdnAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Récupérer les clés API des headers
        $apiKey = $request->header('X-API-Key');
        $apiSecret = $request->header('X-API-Secret');
        
        // Récupérer les clés attendues du fichier .env
        $expectedKey = env('API_KEY');
        $expectedSecret = env('API_SECRET');
        
        // Log pour debug
        Log::channel('cdn_upload')->debug('Authentication attempt', [
            'ip' => $request->ip(),
            'path' => $request->path(),
            'method' => $request->method(),
            'has_key' => !empty($apiKey),
            'has_secret' => !empty($apiSecret),
            'expected_key_exists' => !empty($expectedKey),
            'expected_secret_exists' => !empty($expectedSecret)
        ]);
        
        // Vérifier que les clés sont présentes
        if (empty($apiKey) || empty($apiSecret)) {
            Log::channel('cdn_upload')->warning('Authentication failed - Missing credentials', [
                'ip' => $request->ip(),
                'path' => $request->path(),
                'missing_key' => empty($apiKey),
                'missing_secret' => empty($apiSecret)
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'API credentials are required',
                'missing' => [
                    'api_key' => empty($apiKey),
                    'api_secret' => empty($apiSecret)
                ]
            ], 401);
        }
        
        // Vérifier que les clés attendues sont configurées
        if (empty($expectedKey) || empty($expectedSecret)) {
            Log::channel('cdn_upload')->error('Authentication failed - Server configuration error', [
                'ip' => $request->ip(),
                'missing_expected_key' => empty($expectedKey),
                'missing_expected_secret' => empty($expectedSecret)
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Server configuration error',
                'message' => 'CDN service is not properly configured'
            ], 500);
        }
        
        // Vérifier que les clés correspondent
        if ($apiKey !== $expectedKey || $apiSecret !== $expectedSecret) {
            Log::channel('cdn_upload')->warning('Authentication failed - Invalid credentials', [
                'ip' => $request->ip(),
                'provided_key_preview' => substr($apiKey, 0, 10) . '...',
                'expected_key_preview' => substr($expectedKey, 0, 10) . '...'
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'Invalid API credentials'
            ], 401);
        }
        
        // Authentification réussie
        Log::channel('cdn_upload')->info('Authentication successful', [
            'ip' => $request->ip(),
            'path' => $request->path(),
            'method' => $request->method()
        ]);
        
        return $next($request);
    }
}