<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Http\Requests\UpdateUserStatusRequest;
use App\Http\Resources\UsuarioResource;
use App\Actions\Users\CreateUserAction;
use App\Actions\Users\ResendCredentialsAction;
use App\Actions\Users\ChangeUserRoleAction;
use App\Actions\Users\ChangeUserStatusAction;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OrgUserController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $actor = $request->user();
        $this->authorize('viewAny', Usuario::class);

        $q = Usuario::query()->with('rol');

        if (!$actor->is_superadmin) {
            $q->where('org_id', $actor->org_id);
        } else {
            // opcional: permitir filtrar por org_id desde Postman
            if ($org = $request->query('org_id')) {
                $q->where('org_id', $org);
            }
        }

        if ($s = $request->query('q')) {
            $q->where(function ($w) use ($s) {
                $w->where('nombre','like',"%{$s}%")
                  ->orWhere('email','like',"%{$s}%");
            });
        }
        if ($rol = $request->query('rol_id')) $q->where('rol_id', $rol);
        if ($estado = $request->query('estado')) $q->where('estado', $estado);

        $users = $q->orderBy('nombre')->paginate($request->integer('per_page', 15));

        return UsuarioResource::collection($users)->additional(['ok'=>true]);
    }

    public function store(StoreUserRequest $request, CreateUserAction $action)
    {
        $actor = $request->user();
        $this->authorize('create', Usuario::class);

        $user = $action($request->validated(), $actor, $request);

        return (new UsuarioResource($user))
            ->additional(['ok'=>true,'message'=>'Usuario creado y credenciales enviadas.'])
            ->response()->setStatusCode(201);
    }

    public function resendCredentials(Request $request, Usuario $user, ResendCredentialsAction $action)
    {
        $actor = $request->user();
        // capacidad: org.members.update (o create), reusa update
        if (!$actor->is_superadmin && !$actor->tokenCan('org.members.update') && !$actor->tokenCan('org.members.create')) {
            return response()->json(['ok'=>false,'message'=>'Forbidden'], 403);
        }

        $action($user, $actor, $request);

        return response()->json(['ok'=>true,'message'=>'Credenciales temporales reenviadas.']);
    }

    public function updateRole(UpdateUserRoleRequest $request, Usuario $user, ChangeUserRoleAction $action)
    {
        $this->authorize('updateRole', $user);

        $action($user, $request->validated('rol_id'), $request->user(), $request);

        return response()->json(['ok'=>true,'message'=>'Rol actualizado.']);
    }

    public function updateStatus(UpdateUserStatusRequest $request, Usuario $user, ChangeUserStatusAction $action)
    {
        $this->authorize('updateStatus', $user);

        $action($user, $request->validated('estado'), $request->user(), $request);

        return response()->json(['ok'=>true,'message'=>'Estado actualizado.']);
    }

    public function destroy(Request $request, Usuario $user)
    {
        $this->authorize('delete', $user);

        $user->estado = 'BAJA';
        $user->tokens()->delete();
        $user->save();

        return response()->json(['ok'=>true,'message'=>'Usuario dado de baja.']);
    }
}
