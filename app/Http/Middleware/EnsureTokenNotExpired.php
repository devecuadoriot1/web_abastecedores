<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTokenNotExpired
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->user()?->currentAccessToken();
        if ($token && isset($token->expires_at) && now()->greaterThan($token->expires_at)) {
            return response()->json([
                'ok' => false,
                'code' => 'TOKEN_EXPIRED',
                'message' => 'El token ha expirado. Inicia sesión nuevamente.'
            ], 401);
        }
        return $next($request);
    }
}
