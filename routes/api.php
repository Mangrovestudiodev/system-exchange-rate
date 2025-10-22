<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Middleware\VerifyApiToken;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
*/


// Sanctum authenticated routes (if you want to use Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user(),
            'authenticated_via' => 'sanctum'
        ]);
    });
});

// public routes (not require API token)
Route::middleware(['throttle:60,1'])->prefix('v1/@config')->group(function () {
    Route::post('/auth/signin', [AuthController::class, 'signin']);
});

Route::middleware(['throttle:60,1'])->prefix('v1/@config')->group(function () {
    Route::get('/rates', [ExchangeRateController::class, 'index']);
});

// protected routes (require valid API token)
Route::middleware([VerifyApiToken::class, 'throttle:60,1'])->prefix('v1/@config')->group(function () {
    Route::post('/exchange', [ExchangeRateController::class, 'store']);
});

