<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\LegalCloudApi;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthenticateApiV1
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return LegalCloudApi::problema(401, 'No autenticado', 'Falta el encabezado Authorization Bearer.');
        }

        try {
            $payload = json_decode(decrypt($token), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return LegalCloudApi::problema(401, 'No autenticado', 'El access token no es válido.');
        }

        if (($payload['typ'] ?? '') !== 'access') {
            return LegalCloudApi::problema(401, 'No autenticado', 'Se esperaba un access token.');
        }

        if (($payload['exp'] ?? 0) < time()) {
            return LegalCloudApi::problema(401, 'No autenticado', 'El access token expiró.');
        }

        if (! empty($payload['jti']) && Cache::get('api:revoke:'.$payload['jti'])) {
            return LegalCloudApi::problema(401, 'No autenticado', 'La sesión fue cerrada.');
        }

        $user = User::query()->find($payload['sub'] ?? 0);

        if (! $user || ! $user->activo) {
            return LegalCloudApi::problema(401, 'No autenticado', 'La cuenta no está activa.');
        }

        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);
        $request->attributes->set('api_jti', $payload['jti'] ?? null);

        return $next($request);
    }
}
