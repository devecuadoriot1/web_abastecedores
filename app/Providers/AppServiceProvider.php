<?php

namespace App\Providers;

use App\Models\Usuario;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

     public function boot(): void
    {
        // Rate limit para login (por IP + email)
        RateLimiter::for('auth', function (Request $request) {
            $key = sprintf('auth|%s|%s', $request->ip(), (string) $request->input('email'));
            return [
                Limit::perMinute(10)->by($key)->response(function () {
                    return response()->json([
                        'message' => 'Demasiados intentos. Intenta nuevamente en unos minutos.'
                    ], 429);
                }),
            ];
        });

        // Rate limit general API
        RateLimiter::for('api', function (Request $request) {
            $by = $request->user()?->id ?: $request->ip();
            return Limit::perMinute(60)->by($by);
        });

        // Evitar exponer FQCN en morphs (Sanctum personal_access_tokens)
        Relation::enforceMorphMap([
            'usuarios' => Usuario::class,
        ]);
    }
}
