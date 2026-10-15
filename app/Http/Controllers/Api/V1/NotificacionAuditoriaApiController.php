<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\Notificacion;
use App\Support\LegalCloudApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificacionAuditoriaApiController extends Controller
{
    public function notificaciones(Request $request): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'notificaciones.leer');

        $datos = Notificacion::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn (Notificacion $item) => LegalCloudApi::notificacion($item));

        return LegalCloudApi::json(LegalCloudApi::pagina($datos));
    }

    public function noLeidas(Request $request): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'notificaciones.leer');

        $conteo = Notificacion::query()
            ->where('user_id', $request->user()->id)
            ->where('leida', false)
            ->count();

        return LegalCloudApi::json(['conteo' => $conteo]);
    }

    public function marcarLectura(Request $request, string $notificacionId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'notificaciones.leer');

        $notificacion = Notificacion::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail(LegalCloudApi::parseId($notificacionId));

        $notificacion->update(['leida' => true]);

        return LegalCloudApi::json(LegalCloudApi::notificacion($notificacion->fresh()));
    }

    public function marcarTodas(Request $request): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'notificaciones.leer');

        Notificacion::query()
            ->where('user_id', $request->user()->id)
            ->where('leida', false)
            ->update(['leida' => true]);

        return response()->json(null, 204);
    }

    public function auditoria(Request $request): JsonResponse
    {
        LegalCloudApi::exigeSinConsultor($request->user());
        LegalCloudApi::exige($request->user(), 'auditoria.leer');

        $query = Auditoria::query()->with('user')->latest();

        if ($accion = $request->string('accion')->toString()) {
            $query->where('accion', 'like', "%{$accion}%");
        }

        $datos = $query->limit(100)->get()->map(fn (Auditoria $evento) => LegalCloudApi::evento($evento));

        return LegalCloudApi::json(LegalCloudApi::pagina($datos));
    }

    public function evento(Request $request, string $eventoId): JsonResponse
    {
        LegalCloudApi::exigeSinConsultor($request->user());
        LegalCloudApi::exige($request->user(), 'auditoria.leer');

        $evento = Auditoria::query()->with('user')->findOrFail(LegalCloudApi::parseId($eventoId));

        return LegalCloudApi::json(LegalCloudApi::evento($evento));
    }
}
