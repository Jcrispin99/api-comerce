<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\TenantRegistrationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| Routes for API version 1.
|
*/

// Central routes - only accessible from central domain
// Route::middleware('central.only')->group(function (): void {
//     // Public routes with auth rate limiter (5/min - brute force protection)
//     Route::middleware('throttle:auth')->group(function (): void {
//         Route::post('register', [AuthController::class, 'register'])->name('auth.register');
//         Route::post('login', [AuthController::class, 'login'])->name('auth.login');
//     });

//     // Protected routes with authenticated rate limiter (120/min)
//     Route::middleware(['auth:sanctum', 'throttle:authenticated'])->group(function (): void {
//         Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
//         Route::get('me', [AuthController::class, 'me'])->name('auth.me');

//         // Email verification
//         Route::post('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
//             ->middleware('signed')
//             ->name('verification.verify');
//         Route::post('email/resend', [AuthController::class, 'resendVerificationEmail'])
//             ->middleware('throttle:6,1')
//             ->name('verification.send');
//     });

//     // Password reset routes (public with rate limiting)
//     Route::middleware('throttle:6,1')->group(function (): void {
//         Route::post('forgot-password', [AuthController::class, 'forgotPassword'])
//             ->name('password.email');
//         Route::post('reset-password', [AuthController::class, 'resetPassword'])
//             ->name('password.reset');
//     });

//     // Tenant registration (central routes)
//     Route::get('tenants/domains', [TenantRegistrationController::class, 'index'])
//         ->name('api.v1.tenants.domains');

//     Route::post('tenants', [TenantRegistrationController::class, 'store'])
//         ->middleware('throttle:6,1')
//         ->name('api.v1.tenants.store');
// });
