<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Support\AbilityMap;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
        ]);

        $user = Usuario::query()->where('email', $request->email)->first();

        // Respuestas consistentes
        if (!$user || !Hash::check($request->input('password'), $user->password_hash)) {
            return response()->json([
                'ok' => false,
                'code' => 'BAD_CREDENTIALS',
                'message' => 'Credenciales inválidas.'
            ], 401);
        }

        if ($user->estado !== 'ACTIVO') {
            return response()->json([
                'ok' => false,
                'code' => 'USER_INACTIVE',
                'message' => 'Usuario inactivo. Contacte al administrador.'
            ], 403);
        }

       // Abilities segun multi-rol
        $abilities = AbilityMap::forUser($user);

        // Expiración opcional (honra config/sanctum.php -> 'expiration' en minutos)
        $expiresAt = null;
        if (config('sanctum.expiration')) {
            $expiresAt = now()->addMinutes(config('sanctum.expiration'));
        }

        // Crear token
        $plain = $user->createToken('api', $abilities, $expiresAt)->plainTextToken;

        // Último login
        $user->forceFill(['ultimo_login_at' => now()])->save();


        return response()->json([
            'ok' => true,
            'message' => 'Autenticado.',
            'token' => $plain,
            'abilities' => $abilities,
            'must_reset_password' => (bool)$user->must_reset_password,
            'user' => [
                'id' => $user->id,
                'org_id' => $user->org_id,
                'email' => $user->email,
                'roles' => $user->roles()->pluck('slug'), // ver en respuesta qué roles tiene
            ],
        ]);
    }

    //     /**
    //  * Genera una etiqueta de sesión sin depender del cliente.
    //  */
    // private function guessDeviceLabel(Request $request): string
    // {
    //     $ua = mb_strtolower((string) $request->userAgent());
    //     $ip = $request->ip();
    //     $platform = 'api';
    
    //     if (str_contains($ua, 'postman'))        { $platform = 'postman'; }
    //     elseif (str_contains($ua, 'insomnia'))   { $platform = 'insomnia'; }
    //     elseif (str_contains($ua, 'android'))    { $platform = 'android'; }
    //     elseif (str_contains($ua, 'iphone') || str_contains($ua, 'ipad') || str_contains($ua, 'ios')) { $platform = 'ios'; }
    //     elseif (str_contains($ua, 'windows'))    { $platform = 'windows'; }
    //     elseif (str_contains($ua, 'mac os') || str_contains($ua, 'macintosh')) { $platform = 'macos'; }
    //     elseif (str_contains($ua, 'linux'))      { $platform = 'linux'; }
    
    //     // etiqueta corta y útil; no guardamos PII innecesaria
    //     $suffix = substr(hash('xxh128', $ip.(string) now()), 0, 6);
    //     return "api:{$platform}:{$suffix}";
    // }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
            'abilities' => $request->user()->currentAccessToken()?->abilities ?? [],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['ok' => true]);
    }

    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['ok' => true, 'message' => 'Sesiones cerradas en todos los dispositivos.']);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password_hash)) {
            return response()->json(['message' => 'La contraseña actual no es correcta.'], 422);
        }

        $user->password_hash = Hash::make($request->password);
        $user->save();

        // Política: revocar todos los tokens después de cambiar la clave
        $user->tokens()->delete();

        return response()->json(['ok' => true, 'message' => 'Contraseña actualizada. Vuelve a iniciar sesión.']);
    }
}
