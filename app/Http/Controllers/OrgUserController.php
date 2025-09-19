<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UsuarioResource;
use App\Actions\Users\CreateUserAction;
use App\Actions\Users\UpdateUserAction;
use App\Actions\Users\ResendCredentialsAction;


class OrgUserController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function index(Request $request)
    {
        try {
            $actor = $request->user();
            $this->authorize('viewAny', Usuario::class);

            $q = Usuario::query()->with('roles');

            if (!$actor->is_superadmin) {
                $q->where('org_id', $actor->org_id);
            } elseif ($org = $request->query('org_id')) {
                $q->where('org_id', $org);
            }

            if ($s = $request->query('q')) {
                $q->where(function ($w) use ($s) {
                    $w->where('nombre','like',"%{$s}%")
                      ->orWhere('email','like',"%{$s}%");
                });
            }
            if ($estado = $request->query('estado')) $q->where('estado',$estado);

            $users = $q->orderBy('nombre')->paginate($request->integer('per_page', 15));

            return UsuarioResource::collection($users)->additional(['ok'=>true]);
        } catch (\Throwable $e) {
            return response()->json(['ok'=>false,'code'=>'UNEXPECTED','message'=>'Error inesperado.'], 500);
        }
    }

    public function store(StoreUserRequest $request, CreateUserAction $action)
    {
        try {
            $this->authorize('create', Usuario::class);

            $user = $action($request->validated(), $request->user(), $request);

            return (new UsuarioResource($user))
                ->additional(['ok'=>true,'message'=>'Usuario creado y credenciales enviadas.'])
                ->response()->setStatusCode(201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['ok'=>false,'code'=>'VALIDATION_ERROR','message'=>'Datos inválidos.','errors'=>$e->errors()], 422);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['ok'=>false,'code'=>'FORBIDDEN','message'=>'No está autorizado para crear usuarios.'], 403);
        } catch (\Throwable $e) {
            return response()->json(['ok'=>false,'code'=>'UNEXPECTED','message'=>'Error inesperado.'], 500);
        }
    }

    public function update(UpdateUserRequest $request, Usuario $user, \App\Actions\Users\UpdateUserAction $action)
    {
        try {
            $this->authorize('update', $user);

            $updated = $action($user, $request->validated(), $request->user(), $request);

            return (new UsuarioResource($updated))
                ->additional(['ok'=>true,'message'=>'Usuario actualizado.']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['ok'=>false,'code'=>'VALIDATION_ERROR','message'=>'Datos inválidos.','errors'=>$e->errors()], 422);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['ok'=>false,'code'=>'FORBIDDEN','message'=>'No está autorizado para actualizar este usuario.'], 403);
        } catch (\Throwable $e) {
            return response()->json(['ok'=>false,'code'=>'UNEXPECTED','message'=>'Error inesperado.'], 500);
        }
    }

    public function resendCredentials(Request $request, Usuario $user, ResendCredentialsAction $action)
    {
        try {
            // mismo permiso que update
            $this->authorize('update', $user);

            $action($user, $request->user(), $request);

            return response()->json(['ok'=>true,'message'=>'Credenciales temporales reenviadas.']);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['ok'=>false,'code'=>'FORBIDDEN','message'=>'No está autorizado.'], 403);
        } catch (\Throwable $e) {
            return response()->json(['ok'=>false,'code'=>'UNEXPECTED','message'=>'Error inesperado.'], 500);
        }
    }

    public function destroy(Request $request, Usuario $user)
    {
        try {
            $this->authorize('delete', $user);

            $user->estado = 'INACTIVO';
            $user->tokens()->delete();
            $user->save();

            return response()->json(['ok'=>true,'message'=>'Usuario dado de baja.']);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['ok'=>false,'code'=>'FORBIDDEN','message'=>'No está autorizado para eliminar.'], 403);
        } catch (\Throwable $e) {
            return response()->json(['ok'=>false,'code'=>'UNEXPECTED','message'=>'Error inesperado.'], 500);
        }
    }
}
