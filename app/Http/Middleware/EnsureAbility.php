<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAbility
{
    public function handle(Request $request, Closure $next, string $requiredAbility)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        // SuperAdmin pasa siempre
        if ($user->is_superadmin) {
            return $next($request);
        }

        $abilities = $user->currentAccessToken()?->abilities ?? [];
        if (in_array('*', $abilities, true) || $user->tokenCan($requiredAbility)) {
            return $next($request);
        }

        return response()->json([
            'message' => "No autorizado. Falta ability: {$requiredAbility}"
        ], 403);

    }
}
