<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'token' => ['required','string'],
            'email' => ['required','email','max:190'],
            'password' => ['required','string','min:8','confirmed'],
        ];
    }
}
