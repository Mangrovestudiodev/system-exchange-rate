<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiToken;

class VerifyApiToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('Incoming API request', [
            'path' => $request->path(),
            'authorization' => $request->header('Authorization')
        ]);

        // Get token from Authorization header (Bearer token only)
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'message' => 'Access token required. Please provide token.',
                'error' => 'MISSING_TOKEN'
            ], 401);
        }

        if (!$this->isValidToken($token)) {
            return response()->json([
                'message' => 'Invalid or expired bearer token.',
                'error' => 'INVALID_TOKEN'
            ], 401);
        }

        return $next($request);
    }

    /**
     * Validate the provided token.
     */
    private function isValidToken(string $token): bool
    {
        try {
            return ApiToken::validateToken($token);
        } catch (\Exception $e) {
            \Log::error('Token validation failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

}
