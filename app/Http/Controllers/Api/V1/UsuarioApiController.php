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

class UsuarioApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        LegalCloudApi::exigeSinConsultor($request->user());
        LegalCloudApi::exige($request->user(), 'usuarios.leer');

        $usuarios = User::query()
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => LegalCloudApi::usuario($user));

        return LegalCloudApi::json(LegalCloudApi::pagina($usuarios));
    }

    public function store(Request $request): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'usuarios.invitar');

        $datos = $request->validate([
            'correo' => ['required', 'email', 'unique:users,email'],
            'nombre' => ['required', 'string', 'max:120'],
            'apellido' => ['nullable', 'string', 'max:120'],
            'roles' => ['required', 'array', 'min:1'],
        ]);

        $codigo = is_array($datos['roles'][0] ?? null)
            ? ($datos['roles'][0]['codigo'] ?? $datos['roles'][0]['id'] ?? 'CONSULTOR')
            : (string) $datos['roles'][0];

        $clave = LegalCloudApi::claveDesdeCodigo((string) $codigo) ?? 'consultor';

        $user = User::create([
            'name' => trim($datos['nombre'].' '.($datos['apellido'] ?? '')),
            'email' => $datos['correo'],
            'password' => Hash::make('password'),
            'role' => $clave,
            'cargo' => LegalCloudApi::ROLES[$clave]['nombre'] ?? $clave,
            'activo' => false,
        ]);

        $token = (string) Str::uuid();
        Cache::put('api:invitacion:'.$token, ['user_id' => $user->id], now()->addDays(7));

        AuditoriaLogger::registrar('usuario.invitar', 'usuarios', 'Invitación a '.$user->email);

        return LegalCloudApi::json(array_merge(LegalCloudApi::usuario($user), [
            'invitacionToken' => $token,
        ]), 201);
    }

    public function show(Request $request, string $usuarioId): JsonResponse
    {
        LegalCloudApi::exigeSinConsultor($request->user());

        $user = User::query()->findOrFail(LegalCloudApi::parseId($usuarioId));

        return LegalCloudApi::json(LegalCloudApi::usuario($user));
    }

    public function update(Request $request, string $usuarioId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'usuarios.actualizar');

        $datos = $request->validate([
            'nombre' => ['nullable', 'string', 'max:120'],
            'apellido' => ['nullable', 'string', 'max:120'],
        ]);

        $user = User::query()->findOrFail(LegalCloudApi::parseId($usuarioId));
        $nombre = trim(($datos['nombre'] ?? explode(' ', $user->name)[0]).' '.($datos['apellido'] ?? ''));
        $user->update(['name' => $nombre !== '' ? $nombre : $user->name]);

        return LegalCloudApi::json(LegalCloudApi::usuario($user->fresh()));
    }

    public function reemplazarRoles(Request $request, string $usuarioId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'usuarios.actualizar');

        $datos = $request->validate([
            'roles' => ['required', 'array', 'min:1'],
        ]);

        $codigo = is_array($datos['roles'][0] ?? null)
            ? ($datos['roles'][0]['codigo'] ?? '')
            : (string) $datos['roles'][0];

        $clave = LegalCloudApi::claveDesdeCodigo((string) $codigo) ?? 'consultor';
        $user = User::query()->findOrFail(LegalCloudApi::parseId($usuarioId));
        $user->update(['role' => $clave, 'cargo' => LegalCloudApi::ROLES[$clave]['nombre'] ?? $clave]);

        AuditoriaLogger::registrar('usuario.roles_reemplazados', 'usuarios', $user->email.' → '.$clave);

        return LegalCloudApi::json(LegalCloudApi::usuario($user->fresh()));
    }

    public function suspender(Request $request, string $usuarioId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'usuarios.suspender');

        $user = User::query()->findOrFail(LegalCloudApi::parseId($usuarioId));
        $user->update(['activo' => false]);

        return response()->json(null, 204);
    }

    public function reactivar(Request $request, string $usuarioId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'usuarios.suspender');

        $user = User::query()->findOrFail(LegalCloudApi::parseId($usuarioId));
        $user->update(['activo' => true]);

        return LegalCloudApi::json(LegalCloudApi::usuario($user->fresh()));
    }
}
