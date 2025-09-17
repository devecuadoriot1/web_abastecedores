<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Rol;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'rol_id' => ['required','uuid', Rule::exists('roles','id')],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $actor = $this->user();
            if (!$actor) return;

            if (!$actor->is_superadmin) {
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
