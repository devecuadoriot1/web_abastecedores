<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmailVerifiedForRole
{
    /**
     * Uso: ->middleware('verified.role:Admin,Operador')
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        // Si rol del usuario está en la lista y NO está verificado => bloquear
        $rolNombre = $user->rol->nombre ?? null; // ajusta a tu relación real
        $requiereVerificado = $rolNombre && in_array($rolNombre, $roles, true);

        if ($requiereVerificado && !$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Verifica tu email para acceder a esta sección.'
            ], 403);
        }

        return $next($request);
    }
}
