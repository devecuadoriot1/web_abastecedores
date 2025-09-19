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
            'roles'   => ['required','array','min:1'],
            'roles.*' => ['uuid', Rule::exists('roles','id')],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $actor = $this->user();
            if (!$actor || $actor->is_superadmin) return;

            $rolesIds = collect($this->input('roles', []));
            $count = Rol::whereIn('id', $rolesIds)->where('org_id', $actor->org_id)->count();
            if ($count !== $rolesIds->count()) {
                $v->errors()->add('roles', 'Uno o más roles no pertenecen a tu organización.');
            }
        });
    }
}
