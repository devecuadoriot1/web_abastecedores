<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Rol;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre'   => ['required','string','max:150'],
            'email'    => ['required','email','max:190','unique:usuarios,email'],
            'telefono' => ['nullable','string','max:50'],
            'roles'    => ['required','array','min:1'],
            'roles.*'  => ['uuid', Rule::exists('roles','id')],
            // (opcional) datos extra a futuro: documento, ciudad, etc. -> agregar aquí
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $actor = $this->user();
            if (!$actor || $actor->is_superadmin) return;

            // Roles deben pertenecer a su misma organización
            $rolesIds = collect($this->input('roles', []));
            $count = Rol::whereIn('id', $rolesIds)->where('org_id', $actor->org_id)->count();
            if ($count !== $rolesIds->count()) {
                $v->errors()->add('roles', 'Uno o más roles no pertenecen a tu organización.');
            }
        });
    }

    // public function withValidator($validator)
    // {
    //    $validator->after(function ($v) {
    //         $actor = $this->user();
    //         if (!$actor) return;

    //         // Todos los roles deben pertenecer a la misma org del actor (si no es superadmin)
    //         $rolesIds = collect($this->input('roles', []));
    //         if ($this->filled('rol_id')) $rolesIds = $rolesIds->push($this->input('rol_id'))->unique();

    //         if ($rolesIds->isNotEmpty() && !$actor->is_superadmin) {
    //             $count = Rol::whereIn('id', $rolesIds)->where('org_id', $actor->org_id)->count();
    //             if ($count !== $rolesIds->count()) {
    //                 $v->errors()->add('roles', 'Uno o más roles no pertenecen a tu organización.');
    //             }
    //         }
    //     });
    // }
}
