<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrgUserController;

// Health
Route::get('/health', fn () => response()->json(['ok' => true, 'time' => now()->toIso8601String()]));

Route::prefix('auth')->group(function () {
    // Login (rate limited)
    Route::middleware('throttle:auth')->post('/login', [AuthController::class, 'login']);

    // Email verification (enlace firmado)
    Route::middleware('throttle:6,1')->get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->name('verification.verify');

    // Password reset (público)
    Route::middleware('throttle:6,1')->post('/password/email', [PasswordResetController::class, 'sendResetLink'])
        ->name('password.email');
    Route::middleware('throttle:6,1')->post('/password/reset', [PasswordResetController::class, 'reset'])
        ->name('password.update');

    // Rutas autenticadas
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/me', [AuthController::class, 'me'])->middleware('throttle:api');

        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);

        Route::post('/password/change', [AuthController::class, 'changePassword'])->middleware('throttle:6,1');

        Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
            ->middleware('throttle:6,1')
            ->name('verification.send');

    });
});

/*
|--------------------------------------------------------------------------
| Requiere tokens Sanctum con abilities:
| - org.members.read / org.members.create / org.members.update / org.members.delete
*/
Route::middleware(['auth:sanctum','token.fresh','throttle:api'])->group(function () {
    Route::get('/org/users', [OrgUserController::class, 'index'])
        ->middleware('abilities:org.members.read');

    Route::post('/org/users', [OrgUserController::class, 'store'])
        ->middleware('abilities:org.members.create');

    Route::middleware(['org.scope'])->group(function () {
        // Unificado: perfil + estado + roles
        Route::patch('/org/users/{user}', [OrgUserController::class, 'update'])
            ->middleware('abilities:org.members.update');

        Route::post('/org/users/{user}/resend-credentials', [OrgUserController::class, 'resendCredentials'])
            ->middleware('abilities:org.members.update');

        Route::delete('/org/users/{user}', [OrgUserController::class, 'destroy'])
            ->middleware('abilities:org.members.delete');
    });
});
