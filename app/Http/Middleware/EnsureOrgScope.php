<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureOrgScope
{
    public function handle(Request $request, Closure $next)
    {
        $actor = $request->user();
        if (!$actor) {
            return response()->json(['ok'=>false,'message'=>'Unauthenticated'], 401);
        }

        // SuperAdmin pasa sin restricciones
        if ($actor->is_superadmin) {
            return $next($request);
        }

        // Si en la ruta viene {user} (App\Models\Usuario)
        if ($request->route('user')) {
            $target = $request->route('user');
            if ($target->org_id !== $actor->org_id) {
                return response()->json(['ok'=>false,'message'=>'Forbidden: cross-org access'], 403);
            }
        }

        return $next($request);
    }
}
