<?php

namespace App\Actions\Users;

use App\Models\Usuario;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ChangeUserStatusAction
{
    public function __invoke(Usuario $target, string $estado, Usuario $actor, Request $request): void
    {
        if (!$actor->is_superadmin && $actor->org_id !== $target->org_id) {
            abort(403, 'Forbidden');
        }

        DB::transaction(function () use ($target, $estado, $actor, $request) {
            $target->estado = $estado;
            $target->save();

            // Si se suspende o da de baja → revocar sesiones
            if (in_array($estado, ['INACTIVO'], true)) {
                $target->tokens()->delete();
            }

            AuditLogger::log($target->org_id, $actor->id, $target, $estado === 'ACTIVO' ? 'USER_ACTIVATE' : 'USER_INACTIVATE', [
                'new_status' => $estado,
            ], $request);
        });
    }
}
