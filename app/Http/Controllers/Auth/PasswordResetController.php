<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\Usuario;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function sendResetLink(ForgotPasswordRequest $request)
    {
        $status = Password::broker('usuarios')->sendResetLink(
            ['email' => $request->email]
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['ok' => true, 'message' => __($status)])
            : response()->json(['message' => __($status)], 422);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $status = Password::broker('usuarios')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Usuario $user, string $password) {
                $user->password_hash = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();

                // Revocar todos los tokens tras reset
                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['ok' => true, 'message' => __($status)])
            : response()->json(['message' => __($status)], 422);
    }
}
