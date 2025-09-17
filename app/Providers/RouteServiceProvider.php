<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Límite general para /api
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?? $request->ip());
        });

        // Límite específico para login (IP y email)
        RateLimiter::for('auth', function (Request $request) {
            $keyIp    = 'auth:ip:'.$request->ip();
            $keyEmail = 'auth:email:'.mb_strtolower((string) $request->input('email'));
            return [
                Limit::perMinute(10)->by($keyIp),
                Limit::perMinute(10)->by($keyEmail),
            ];
        });
    }
}
