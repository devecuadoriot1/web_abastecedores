<?php

namespace App\Actions\Users;

use App\Models\Usuario;
use App\Support\AuditLogger;
use App\Notifications\NewUserCredentials;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class ResendCredentialsAction
{
    public function __invoke(Usuario $target, Usuario $actor, Request $request): void
    {
        if (!$actor->is_superadmin && $actor->org_id !== $target->org_id) {
            abort(403, 'Forbidden');
        }

        DB::transaction(function () use ($target, $actor, $request) {
            $plain = Str::password(14);

            $target->password_hash = Hash::make($plain);
            $target->must_reset_password = true;
            $target->save();

            // Revocar sesiones activas (Sanctum)
            $target->tokens()->delete();

            $target->notify(new NewUserCredentials(
                organizacionNombre: optional($target->organizacion)->nombre ?? 'nuestro sistema',
                email: $target->email,
                passwordTemporal: $plain
            ));

            AuditLogger::log($target->org_id, $actor->id, $target, 'USER_RESEND_CREDENTIALS', [], $request);
        });
    }
}
