<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Rol;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'nombre'   => ['sometimes','required','string','max:150'],
            'email'    => ['sometimes','required','email','max:190', Rule::unique('usuarios','email')->ignore($user?->id,'id')],
            'telefono' => ['sometimes','nullable','string','max:50'],
            'estado'   => ['sometimes','required','in:ACTIVO,INACTIVO'],
            'roles'    => ['sometimes','array','min:1'],
            'roles.*'  => ['uuid', Rule::exists('roles','id')],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $actor = $this->user();
            if (!$actor || $actor->is_superadmin) return;

            if ($this->filled('roles')) {
                $rolesIds = collect($this->input('roles', []));
                $count = Rol::whereIn('id',$rolesIds)->where('org_id',$actor->org_id)->count();
                if ($count !== $rolesIds->count()) {
                    $v->errors()->add('roles','Uno o más roles no pertenecen a tu organización.');
                }
            }
        });
    }
}
