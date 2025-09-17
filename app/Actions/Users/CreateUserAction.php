<?php

namespace App\Actions\Users;

use App\Models\Usuario;
use App\Models\Rol;
use App\Support\AuditLogger;
use App\Notifications\NewUserCredentials;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreateUserAction
{
    public function __invoke(array $input, Usuario $actor, Request $request): Usuario
    {
        if (!$actor->is_superadmin && $actor->org_id !== Auth::user()->org_id) {
            abort(403, 'Forbidden');
        
        }

        // Verificación de rol consistente
        if (!$actor->is_superadmin) {
            $rol = Rol::where('id', $input['rol_id'])
                ->where('org_id', $actor->org_id)->firstOrFail();
        } else {
            $rol = Rol::findOrFail($input['rol_id']);
        }

        $plain = Str::password(14); // aleatoria robusta
        $usuario = null;

        DB::transaction(function () use ($input, $actor, $rol, $plain, &$usuario, $request) {
            $usuario = new Usuario();
            $usuario->org_id = $actor->is_superadmin ? ($input['org_id'] ?? $actor->org_id) : $actor->org_id;
            $usuario->rol_id = $rol->id;
            $usuario->nombre = $input['nombre'];
            $usuario->email  = $input['email'];
            $usuario->telefono = $input['telefono'] ?? null;
            $usuario->password_hash = Hash::make($plain);
            $usuario->estado = 'ACTIVO';
            $usuario->must_reset_password = true;
            $usuario->save();

            // Enviar correo (cola según config)
            $usuario->notify(new NewUserCredentials(
                organizacionNombre: optional($usuario->organizacion)->nombre ?? 'nuestro sistema',
                email: $usuario->email,
                passwordTemporal: $plain
            ));

            AuditLogger::log($usuario->org_id, $actor->id, $usuario, 'USER_CREATE', [], $request);
        });

        return $usuario ? $usuario->load('rol') : null;
    }
}
