<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, string $id, string $hash)
    {
        $user = Usuario::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Enlace inválido.'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['ok' => true, 'message' => 'Email ya verificado.']);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json(['ok' => true, 'message' => 'Email verificado.']);
    }

    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['ok' => true, 'message' => 'Email ya verificado.']);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['ok' => true, 'message' => 'Correo de verificación enviado.']);
    }
}
