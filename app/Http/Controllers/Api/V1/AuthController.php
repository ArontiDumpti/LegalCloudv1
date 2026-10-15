<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AuditoriaLogger;
use App\Support\LegalCloudApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class AuthController extends Controller
{
    public function iniciarSesion(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'correo' => ['required', 'email'],
            'contrasena' => ['required', 'string', 'min:8'],
        ]);

        $user = User::query()->where('email', $datos['correo'])->first();

        if (! $user || ! Hash::check($datos['contrasena'], $user->password) || ! $user->activo) {
            return LegalCloudApi::problema(401, 'No autenticado', 'Correo o contraseña incorrectos.');
        }

        auth()->setUser($user);
        AuditoriaLogger::registrar('auth.iniciar_sesion', 'auth', 'Inicio de sesión API de '.$user->email);

        return LegalCloudApi::json($this->emitirTokens($user));
    }

    public function renovar(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'refreshToken' => ['required', 'string'],
        ]);

        try {
            $payload = json_decode(decrypt($datos['refreshToken']), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return LegalCloudApi::problema(401, 'No autenticado', 'El refresh token no es válido.');
        }

        if (($payload['typ'] ?? '') !== 'refresh' || ($payload['exp'] ?? 0) < time()) {
            return LegalCloudApi::problema(401, 'No autenticado', 'El refresh token expiró.');
        }

        if (! empty($payload['jti']) && Cache::get('api:revoke:'.$payload['jti'])) {
            return LegalCloudApi::problema(401, 'No autenticado', 'La sesión fue cerrada.');
        }

        $user = User::query()->find($payload['sub'] ?? 0);

        if (! $user || ! $user->activo) {
            return LegalCloudApi::problema(401, 'No autenticado', 'La cuenta no está activa.');
        }

        return LegalCloudApi::json($this->emitirTokens($user));
    }

    public function cerrarSesion(Request $request): JsonResponse
    {
        $jti = $request->attributes->get('api_jti');

        if ($jti) {
            Cache::put('api:revoke:'.$jti, true, now()->addDays(8));
        }

        AuditoriaLogger::registrar('auth.cerrar_sesion', 'auth', 'Cierre de sesión API');

        return response()->json(null, 204);
    }

    public function yo(Request $request): JsonResponse
    {
        return LegalCloudApi::json(LegalCloudApi::perfil($request->user()));
    }

    public function cambiarContrasena(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'contrasenaActual' => ['required', 'string'],
            'contrasenaNueva' => ['required', 'string', 'min:8', 'max:128'],
        ]);

        $user = $request->user();

        if (! Hash::check($datos['contrasenaActual'], $user->password)) {
            return LegalCloudApi::problema(422, 'Entidad improcesable', 'La contraseña actual no coincide.', [
                'contrasenaActual' => ['No coincide.'],
            ]);
        }

        $user->update(['password' => Hash::make($datos['contrasenaNueva'])]);
        AuditoriaLogger::registrar('auth.cambiar_contrasena', 'auth', 'Cambio de contraseña API');

        return response()->json(null, 204);
    }

    public function aceptarInvitacion(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'token' => ['required', 'string'],
            'contrasena' => ['required', 'string', 'min:8', 'max:128'],
        ]);

        $invitacion = Cache::get('api:invitacion:'.$datos['token']);

        if (! is_array($invitacion)) {
            return LegalCloudApi::problema(404, 'No encontrado', 'La invitación no existe o expiró.');
        }

        $user = User::query()->find($invitacion['user_id']);

        if (! $user) {
            return LegalCloudApi::problema(404, 'No encontrado', 'El usuario invitado ya no existe.');
        }

        $user->update([
            'password' => Hash::make($datos['contrasena']),
            'activo' => true,
        ]);
        Cache::forget('api:invitacion:'.$datos['token']);

        return LegalCloudApi::json($this->emitirTokens($user));
    }

    public function registrar(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'apellido' => ['required', 'string', 'max:120'],
            'correo' => ['required', 'email', 'unique:users,email'],
            'contrasena' => ['required', 'string', 'min:8', 'max:128', 'same:contrasenaConfirmacion'],
            'contrasenaConfirmacion' => ['required', 'string', 'min:8', 'max:128'],
        ]);

        $user = User::create([
            'name' => trim($datos['nombre'].' '.$datos['apellido']),
            'email' => $datos['correo'],
            'password' => Hash::make($datos['contrasena']),
            'role' => 'consultor',
            'cargo' => 'Usuario consultor',
            'activo' => true,
            'email_verified_at' => now(),
        ]);

        auth()->setUser($user);
        AuditoriaLogger::registrar('auth.registrar', 'auth', 'Alta de cuenta '.$user->email);

        return LegalCloudApi::json($this->emitirTokens($user), 201);
    }

    public function recuperarContrasena(Request $request): JsonResponse
    {
        $request->validate(['correo' => ['required', 'email']]);

        return response()->json(['aceptado' => true], 202);
    }

    /**
     * @return array{tokenType: string, accessToken: string, refreshToken: string, expiraEn: int}
     */
    private function emitirTokens(User $user): array
    {
        $jti = (string) Str::uuid();

        $access = [
            'sub' => $user->id,
            'org_id' => 1,
            'typ' => 'access',
            'jti' => $jti,
            'exp' => time() + 3600,
        ];

        $refresh = [
            'sub' => $user->id,
            'org_id' => 1,
            'typ' => 'refresh',
            'jti' => $jti,
            'exp' => time() + 60 * 60 * 24 * 7,
        ];

        return [
            'tokenType' => 'Bearer',
            'accessToken' => encrypt(json_encode($access)),
            'refreshToken' => encrypt(json_encode($refresh)),
            'expiraEn' => 3600,
        ];
    }
}
