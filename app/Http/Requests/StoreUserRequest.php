<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Rol;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El control fino irá por abilities/policies en rutas y controller.
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'  => ['required','string','max:255'],
            'email'   => ['required','email','max:255','unique:usuarios,email'],
            'telefono'=> ['nullable','string','max:50'],
            'rol_id'  => ['required','uuid', Rule::exists('roles','id')],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $actor = $this->user();
            if (!$actor) return;

            if (!$actor->is_superadmin) {
                // Asegurar que el rol pertenece a su misma organización
                $rolValido = Rol::query()
                    ->where('id', $this->input('rol_id'))
                    ->where('org_id', $actor->org_id)
                    ->exists();
                if (!$rolValido) {
                    $v->errors()->add('rol_id', 'El rol no pertenece a tu organización.');
                }
            }
        });
    }
}
