<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrgUserController;


use App\Models\Rol;
use Illuminate\Http\Request;


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
Route::middleware(['auth:sanctum','throttle:api'])->group(function () {
    // Listar miembros de la org (filtros: q, rol_id, estado, per_page)
    Route::get('/org/users', [OrgUserController::class, 'index'])
        ->middleware('abilities:org.members.read');

    // Crear usuario (admin crea y asigna rol; envía credenciales)
    Route::post('/org/users', [OrgUserController::class, 'store'])
        ->middleware('abilities:org.members.create');

    // Endpoints que operan sobre un usuario específico (requiere OrgScope)
    Route::middleware(['org.scope'])->group(function () {
        // Rotar/reenviar credenciales temporales
        Route::post('/org/users/{user}/resend-credentials', [OrgUserController::class, 'resendCredentials'])
            ->middleware('abilities:org.members.update');

        // Cambiar rol
        Route::patch('/org/users/{user}/role', [OrgUserController::class, 'updateRole'])
            ->middleware('abilities:org.members.update');

        // Cambiar estado (ACTIVO/SUSPENDIDO/BAJA)
        Route::patch('/org/users/{user}/status', [OrgUserController::class, 'updateStatus'])
            ->middleware('abilities:org.members.update');

        // Baja lógica
        Route::delete('/org/users/{user}', [OrgUserController::class, 'destroy'])
            ->middleware('abilities:org.members.delete');
    });
});
