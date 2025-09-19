<?php

namespace App\Actions\Users;

use App\Models\Usuario;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class UpdateUserAction
{
    public function __invoke(Usuario $target, array $data, Usuario $actor, Request $request): Usuario
    {
        return DB::transaction(function () use ($target, $data, $actor, $request) {
            $before = [
                'nombre'  => $target->nombre,
                'email'   => $target->email,
                'telefono'=> $target->telefono,
                'estado'  => $target->estado,
                'roles'   => $target->roles()->pluck('roles.id')->all(),
            ];

            $rolesChanged = false;
            $statusChangedToInactive = false;

            // Perfil
            $target->fill(array_intersect_key($data, array_flip(['nombre','email','telefono'])));

            // Estado
            if (array_key_exists('estado', $data)) {
                $target->estado = $data['estado'];
                $statusChangedToInactive = ($data['estado'] === 'INACTIVO');
            }
            $target->save();

            // Roles
            if (array_key_exists('roles', $data)) {
                $newRoles = (array) $data['roles'];
                $target->roles()->sync($newRoles);
                $target->rol_id = $newRoles[0] ?? $target->rol_id; // primario opcional
                $target->save();
                $rolesChanged = true;
            }

            // Revocar tokens si cambian roles o queda INACTIVO
            if ($rolesChanged || $statusChangedToInactive) {
                $target->tokens()->delete();
            }

            $after = [
                'nombre'  => $target->nombre,
                'email'   => $target->email,
                'telefono'=> $target->telefono,
                'estado'  => $target->estado,
                'roles'   => $target->roles()->pluck('roles.id')->all(),
            ];

            AuditLogger::log($target->org_id, $actor->id, $target, 'USER_UPDATE', [
                'before' => $before,
                'after'  => $after,
                'roles_changed' => $rolesChanged,
                'state_changed_to_inactive' => $statusChangedToInactive,
            ], $request);

            return $target->load('roles');
        });
    }
}
