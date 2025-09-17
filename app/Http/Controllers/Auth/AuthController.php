<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $user = Usuario::query()->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales inválidas.'],
            ]);
        }

        if ($user->estado !== 'ACTIVO') {
            return response()->json(['message' => 'Usuario inactivo.'], 403);
        }

        // Define abilities base (ajusta según rol o política)
        $abilities = ['user:read', 'user:update'];

        // Si el cliente no envía device_name, lo generamos de forma limpia y consistente
        $deviceName = $request->input('device_name') ?: $this->guessDeviceLabel($request);

        $token = $user->createToken($deviceName, $abilities);

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $token->plainTextToken,
            'abilities' => $abilities,
            'expires_in_minutes' => config('sanctum.expiration'),
            'user' => [
                'id' => $user->id,
                'org_id' => $user->org_id,
                'rol_id' => $user->rol_id,
                'nombre' => $user->nombre,
                'email' => $user->email,
                'email_verified' => !is_null($user->email_verified_at),
            ],
        ]);
    }

        /**
     * Genera una etiqueta de sesión sin depender del cliente.
     */
    private function guessDeviceLabel(Request $request): string
    {
        $ua = mb_strtolower((string) $request->userAgent());
        $ip = $request->ip();
        $platform = 'api';
    
        if (str_contains($ua, 'postman'))        { $platform = 'postman'; }
        elseif (str_contains($ua, 'insomnia'))   { $platform = 'insomnia'; }
        elseif (str_contains($ua, 'android'))    { $platform = 'android'; }
        elseif (str_contains($ua, 'iphone') || str_contains($ua, 'ipad') || str_contains($ua, 'ios')) { $platform = 'ios'; }
        elseif (str_contains($ua, 'windows'))    { $platform = 'windows'; }
        elseif (str_contains($ua, 'mac os') || str_contains($ua, 'macintosh')) { $platform = 'macos'; }
        elseif (str_contains($ua, 'linux'))      { $platform = 'linux'; }
    
        // etiqueta corta y útil; no guardamos PII innecesaria
        $suffix = substr(hash('xxh128', $ip.(string) now()), 0, 6);
        return "api:{$platform}:{$suffix}";
    }

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
