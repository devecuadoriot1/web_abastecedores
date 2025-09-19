<?php

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Laravel\Sanctum\Http\Middleware\AuthenticateSession;

return [
    // ...

    'middleware' => [
        // Para SPA con cookies (si usaras ese modo)
        'encrypt_cookies'           => EncryptCookies::class,
        'add_queued_cookies'        => AddQueuedCookiesToResponse::class,
        'start_session'             => StartSession::class,
        'share_errors_from_session' => ShareErrorsFromSession::class,
        // En Laravel 11 es ValidateCsrfToken (no VerifyCsrfToken del App\)
        'validate_csrf_token'       => ValidateCsrfToken::class,
        'authenticate_session'      => AuthenticateSession::class,
    ],
];
