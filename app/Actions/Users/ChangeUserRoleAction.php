<?php

namespace App\Actions\Users;

use App\Models\Usuario;
use App\Models\Rol;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ChangeUserRoleAction
{
    public function __invoke(Usuario $target, string $newRolId, Usuario $actor, Request $request): void
    {
        if (!$actor->is_superadmin && $actor->org_id !== $target->org_id) {
            abort(403, 'Forbidden');
        }

        if ($actor->id === $target->id) {
            abort(422, 'No puedes cambiar tu propio rol.');
        }

        $rol = $actor->is_superadmin
            ? Rol::findOrFail($newRolId)
            : Rol::where('id', $newRolId)->where('org_id', $actor->org_id)->firstOrFail();

        DB::transaction(function () use ($target, $rol, $actor, $request) {
            $old = $target->rol_id;
            $target->rol_id = $rol->id;
            $target->save();

            AuditLogger::log($target->org_id, $actor->id, $target, 'USER_ROLE_CHANGE', [
                'old_role' => $old, 'new_role' => $rol->id,
            ], $request);
        });
    }
}
