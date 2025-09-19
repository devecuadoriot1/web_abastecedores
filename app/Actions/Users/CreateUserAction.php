<?php

namespace App\Actions\Users;

use App\Models\Usuario;
use App\Support\AuditLogger;
use App\Notifications\NewUserCredentials;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class CreateUserAction
{
    public function __invoke(array $input, Usuario $actor, Request $request): Usuario
    {
        $plain = Str::password(14);
        $usuario = null;

        DB::transaction(function () use ($input, $actor, $plain, &$usuario, $request) {
            $usuario = new Usuario();
            $usuario->org_id  = $actor->org_id;           // misma org del creador (salvo superadmin si decides soportar cross-org)
            $usuario->nombre  = $input['nombre'];
            $usuario->email   = $input['email'];
            $usuario->telefono = $input['telefono'] ?? null;
            $usuario->password_hash = Hash::make($plain);
            $usuario->estado  = 'ACTIVO';
            $usuario->must_reset_password = true;
            $usuario->rol_id  = $input['roles'][0] ?? null; // opcional: rol primario
            $usuario->save();

            // Multi-rol
            $usuario->roles()->sync($input['roles']);

            // Notificación de credenciales
            $usuario->notify(new NewUserCredentials(
                organizacionNombre: optional($usuario->organizacion)->nombre ?? 'nuestro sistema',
                email: $usuario->email,
                passwordTemporal: $plain
            ));

            AuditLogger::log($usuario->org_id, $actor->id, $usuario, 'USER_CREATE', [
                'roles' => $input['roles'],
            ], $request);
        });

        return $usuario ? $usuario->load('roles') : null;
    }
}
