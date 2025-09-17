<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAbility
{
    public function handle(Request $request, Closure $next, string ...$abilities)
    {
        $token = $request->user()?->currentAccessToken();

        if (!$token) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        foreach ($abilities as $ability) {
            if (! $token->can($ability) && ! $token->can('*')) {
                return response()->json(['message' => 'No autorizado. Falta ability: '.$ability], 403);
            }
        }

        return $next($request);
    }
}
