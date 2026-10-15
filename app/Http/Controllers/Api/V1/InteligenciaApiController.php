<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ConsultaRag;
use App\Models\Documento;
use App\Models\Expediente;
use App\Services\MockRagService;
use App\Support\AuditoriaLogger;
use App\Support\LegalCloudApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class InteligenciaApiController extends Controller
{
    public function listarBusquedas(Request $request): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'busqueda.consultar');

        $ids = Cache::get('api:busquedas:user:'.$request->user()->id, []);
        $datos = collect($ids)
            ->map(fn (string $id) => Cache::get('api:busqueda:'.$id))
            ->filter()
            ->values();

        return LegalCloudApi::json(LegalCloudApi::pagina($datos));
    }

    public function crearBusqueda(Request $request): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'busqueda.consultar');

        $datos = $request->validate([
            'consulta' => ['required', 'string', 'min:2'],
            'modo' => ['nullable', 'string'],
            'limite' => ['nullable', 'integer', 'min:1', 'max:50'],
            'filtros' => ['nullable', 'array'],
        ]);

        $limite = $datos['limite'] ?? 10;
        $terminos = array_filter(preg_split('/\s+/', mb_strtolower($datos['consulta'])) ?: []);

        $query = Documento::query()->with(['expediente', 'categoria']);

        if (! empty($datos['filtros']['expedienteId'])) {
            $query->where('expediente_id', LegalCloudApi::parseId((string) $datos['filtros']['expedienteId']));
        }

        $resultados = $query->get()->map(function (Documento $documento) use ($terminos) {
            $corpus = mb_strtolower(($documento->titulo ?? '').' '.($documento->contenido_ocr ?? ''));
            $puntaje = 0;
            foreach ($terminos as $termino) {
                if (mb_strlen((string) $termino) > 2 && str_contains($corpus, (string) $termino)) {
                    $puntaje++;
                }
            }

            return [
                'documento' => LegalCloudApi::documento($documento),
                'puntaje' => $puntaje,
                'fragmento' => mb_strimwidth(preg_replace('/\s+/', ' ', (string) $documento->contenido_ocr) ?: $documento->titulo, 0, 280, '…'),
            ];
        })
            ->filter(fn (array $item) => $item['puntaje'] > 0)
            ->sortByDesc('puntaje')
            ->take($limite)
            ->values()
            ->all();

        $id = (string) Str::uuid();
        $busqueda = [
            'id' => $id,
            'consulta' => $datos['consulta'],
            'modo' => $datos['modo'] ?? 'hibrida',
            'resultados' => $resultados,
            'creadoEn' => now()->toIso8601String(),
        ];

        Cache::put('api:busqueda:'.$id, $busqueda, now()->addDay());
        $ids = Cache::get('api:busquedas:user:'.$request->user()->id, []);
        array_unshift($ids, $id);
        Cache::put('api:busquedas:user:'.$request->user()->id, array_slice($ids, 0, 30), now()->addDays(7));

        AuditoriaLogger::registrar('busqueda.crear', 'busqueda', $datos['consulta']);

        return LegalCloudApi::json($busqueda, 201);
    }

    public function obtenerBusqueda(Request $request, string $busquedaId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'busqueda.consultar');

        $busqueda = Cache::get('api:busqueda:'.$busquedaId);

        if (! is_array($busqueda)) {
            return LegalCloudApi::problema(404, 'No encontrado', 'La búsqueda no existe.');
        }

        return LegalCloudApi::json($busqueda);
    }

    public function listarConversaciones(Request $request): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'asistente.conversar');

        $datos = ConsultaRag::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn (ConsultaRag $consulta) => LegalCloudApi::conversacion($consulta));

        return LegalCloudApi::json(LegalCloudApi::pagina($datos));
    }

    public function crearConversacion(Request $request): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'asistente.conversar');

        $datos = $request->validate([
            'titulo' => ['nullable', 'string', 'max:240'],
            'alcance' => ['nullable', 'array'],
        ]);

        $id = (string) Str::uuid();
        $conversacion = [
            'id' => $id,
            'titulo' => $datos['titulo'] ?: 'Nueva consulta',
            'alcance' => $datos['alcance'] ?? (object) [],
            'mensajes' => [],
            'creadoEn' => now()->toIso8601String(),
            'actualizadoEn' => now()->toIso8601String(),
        ];

        Cache::put('api:conversacion:'.$id, $conversacion, now()->addDays(7));
        $ids = Cache::get('api:conversaciones:user:'.$request->user()->id, []);
        array_unshift($ids, $id);
        Cache::put('api:conversaciones:user:'.$request->user()->id, array_slice($ids, 0, 40), now()->addDays(7));

        return LegalCloudApi::json($conversacion, 201);
    }

    public function obtenerConversacion(Request $request, string $conversacionId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'asistente.conversar');

        if ($cache = Cache::get('api:conversacion:'.$conversacionId)) {
            return LegalCloudApi::json($cache);
        }

        $consulta = ConsultaRag::query()
            ->where('user_id', $request->user()->id)
            ->find(LegalCloudApi::parseId($conversacionId));

        if (! $consulta) {
            return LegalCloudApi::problema(404, 'No encontrado', 'La conversación no existe.');
        }

        return LegalCloudApi::json(array_merge(LegalCloudApi::conversacion($consulta), [
            'mensajes' => [LegalCloudApi::mensaje($consulta)],
        ]));
    }

    public function crearMensaje(Request $request, string $conversacionId, MockRagService $rag): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'asistente.conversar');

        $datos = $request->validate([
            'contenido' => ['required_without:pregunta', 'string'],
            'pregunta' => ['required_without:contenido', 'string'],
        ]);

        $pregunta = $datos['contenido'] ?? $datos['pregunta'];
        $consulta = $rag->consultar($pregunta, $request->user());
        $mensaje = LegalCloudApi::mensaje($consulta);

        if ($cache = Cache::get('api:conversacion:'.$conversacionId)) {
            $cache['mensajes'][] = $mensaje;
            $cache['actualizadoEn'] = now()->toIso8601String();
            Cache::put('api:conversacion:'.$conversacionId, $cache, now()->addDays(7));
        }

        return LegalCloudApi::json($mensaje, 201);
    }

    public function crearResumen(Request $request): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'resumenes.solicitar');

        $datos = $request->validate([
            'expedienteId' => ['nullable', 'string'],
            'documentoId' => ['nullable', 'string'],
        ]);

        $texto = '';
        $titulo = 'Resumen';

        if (! empty($datos['documentoId'])) {
            $documento = Documento::query()->findOrFail(LegalCloudApi::parseId($datos['documentoId']));
            $texto = (string) $documento->contenido_ocr;
            $titulo = $documento->titulo;
        } elseif (! empty($datos['expedienteId'])) {
            $expediente = Expediente::query()->with('documentos')->findOrFail(LegalCloudApi::parseId($datos['expedienteId']));
            $texto = $expediente->documentos->pluck('contenido_ocr')->filter()->implode("\n\n");
            $titulo = $expediente->titulo;
        }

        $resumen = [
            'id' => (string) Str::uuid(),
            'titulo' => $titulo,
            'estado' => 'listo',
            'contenido' => $texto !== ''
                ? mb_strimwidth(preg_replace('/\s+/', ' ', $texto) ?? '', 0, 700, '…')
                : 'No hay texto indexado para resumir.',
            'creadoEn' => now()->toIso8601String(),
        ];

        Cache::put('api:resumen:'.$resumen['id'], $resumen, now()->addDay());
        AuditoriaLogger::registrar('resumen.solicitar', 'asistente', $titulo);

        return LegalCloudApi::json($resumen, 201);
    }

    public function obtenerResumen(Request $request, string $resumenId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'resumenes.solicitar');

        $resumen = Cache::get('api:resumen:'.$resumenId);

        if (! is_array($resumen)) {
            return LegalCloudApi::problema(404, 'No encontrado', 'El resumen no existe.');
        }

        return LegalCloudApi::json($resumen);
    }
}
