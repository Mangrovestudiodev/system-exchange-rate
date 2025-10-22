<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Default API rate limiter
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Per-user API rate limiter
        RateLimiter::for('api-per-user', function (Request $request) {
            // Extract user identifier from token or use IP as fallback
            $userId = $this->getUserIdFromToken($request) ?: $request->ip();

            return [
                Limit::perMinute(100)->by($userId), // 100 requests per minute
                Limit::perHour(1000)->by($userId),  // 1000 requests per hour
                Limit::perDay(10000)->by($userId),  // 10000 requests per day
            ];
        });

        // Custom API rate limiter with different limits
        RateLimiter::for('custom-api', function (Request $request) {
            $key = $request->bearerToken() ?: $request->header('X-API-Token') ?: $request->ip();

            return [
                Limit::perMinute(30)->by($key)->response(function () {
                    return response()->json([
                        'message' => 'Too many requests. Please slow down.',
                        'retry_after' => 60
                    ], 429);
                }),
                Limit::perHour(500)->by($key),
            ];
        });

        // Heavy operations rate limiter
        RateLimiter::for('heavy-operations', function (Request $request) {
            $key = $request->bearerToken() ?: $request->ip();

            return Limit::perMinute(5)->by($key)->response(function () {
                return response()->json([
                    'message' => 'Heavy operation rate limit exceeded. Try again later.',
                    'retry_after' => 60
                ], 429);
            });
        });

        // Admin operations rate limiter
        RateLimiter::for('admin-api', function (Request $request) {
            $key = $request->bearerToken() ?: $request->ip();

            return [
                Limit::perMinute(20)->by($key),
                Limit::perHour(100)->by($key),
            ];
        });

        // File upload rate limiter
        RateLimiter::for('upload-api', function (Request $request) {
            $key = $request->bearerToken() ?: $request->ip();

            return Limit::perMinute(5)->by($key)->response(function () {
                return response()->json([
                    'message' => 'Upload rate limit exceeded.',
                    'retry_after' => 60
                ], 429);
            });
        });
    }

    /**
     * Extract user ID from token for rate limiting
     */
    private function getUserIdFromToken(Request $request): ?string
    {
        $token = $request->bearerToken() ?: $request->header('X-API-Token');

        if (!$token) {
            return null;
        }

        // Simple token-to-user mapping for demo
        // In production, you'd decode JWT or lookup in database
        $tokenUserMap = [
            'test-token-123' => 'user_1',
            'api-key-456' => 'user_2',
            'demo-token-789' => 'user_3',
            'your-secret-token' => 'user_4',
        ];

        return $tokenUserMap[$token] ?? null;
    }
}
