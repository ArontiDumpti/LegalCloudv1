<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\Documento;
use App\Models\Expediente;
use App\Models\User;
use App\Support\AuditoriaLogger;
use App\Support\LegalCloudApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ExpedienteApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'expedientes.leer');

        $query = Expediente::query()->with('documentos')->latest();

        if ($estado = $request->string('estado')->toString()) {
            $interno = LegalCloudApi::estadoInternoExpediente($estado);
            $query->whereIn('estado', array_unique([$estado, $interno]));
        }

        if ($q = $request->string('q')->toString()) {
            $query->where(function ($builder) use ($q) {
                $builder->where('codigo', 'like', "%{$q}%")
                    ->orWhere('titulo', 'like', "%{$q}%");
            });
        }

        $datos = $query->get()->map(fn (Expediente $expediente) => LegalCloudApi::expediente($expediente));

        return LegalCloudApi::json(LegalCloudApi::pagina($datos));
    }

    public function store(Request $request): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'expedientes.crear');

        $datos = $request->validate([
            'titulo' => ['required', 'string', 'max:240'],
            'descripcion' => ['nullable', 'string', 'max:4000'],
            'categoriaId' => ['nullable', 'string'],
            'nivelConfidencialidad' => ['nullable', 'string'],
            'etiquetas' => ['nullable', 'array'],
        ]);

        $siguiente = Expediente::count() + 1;
        $expediente = Expediente::create([
            'codigo' => 'EXP-'.now()->year.'-'.str_pad((string) $siguiente, 5, '0', STR_PAD_LEFT),
            'titulo' => $datos['titulo'],
            'descripcion' => $datos['descripcion'] ?? null,
            'estado' => 'abierto',
            'prioridad' => $datos['etiquetas'][0] ?? 'media',
            'categoria_id' => isset($datos['categoriaId']) ? LegalCloudApi::parseId($datos['categoriaId']) : null,
            'responsable_id' => $request->user()->id,
            'creado_por' => $request->user()->id,
            'fecha_apertura' => now(),
        ]);

        AuditoriaLogger::registrar('expediente.crear', 'expedientes', $expediente->codigo);

        return LegalCloudApi::json(LegalCloudApi::expediente($expediente->fresh('documentos')), 201);
    }

    public function show(Request $request, string $expedienteId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'expedientes.leer');

        $expediente = Expediente::query()->with('documentos')->findOrFail(LegalCloudApi::parseId($expedienteId));

        return LegalCloudApi::json(LegalCloudApi::expediente($expediente));
    }

    public function update(Request $request, string $expedienteId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'expedientes.actualizar');

        $datos = $request->validate([
            'titulo' => ['nullable', 'string', 'max:240'],
            'descripcion' => ['nullable', 'string', 'max:4000'],
            'categoriaId' => ['nullable', 'string'],
            'nivelConfidencialidad' => ['nullable', 'string'],
            'etiquetas' => ['nullable', 'array'],
        ]);

        $expediente = Expediente::query()->findOrFail(LegalCloudApi::parseId($expedienteId));
        $expediente->update(array_filter([
            'titulo' => $datos['titulo'] ?? null,
            'descripcion' => $datos['descripcion'] ?? null,
            'categoria_id' => array_key_exists('categoriaId', $datos) && $datos['categoriaId']
                ? LegalCloudApi::parseId($datos['categoriaId'])
                : null,
            'prioridad' => $datos['etiquetas'][0] ?? $expediente->prioridad,
        ], fn ($valor) => $valor !== null));

        return LegalCloudApi::json(LegalCloudApi::expediente($expediente->fresh('documentos')));
    }

    public function transicionar(Request $request, string $expedienteId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'expedientes.transicionar');

        $datos = $request->validate([
            'accion' => ['required', 'in:abrir,enviar_a_revision,devolver,cerrar,archivar,reabrir'],
            'comentario' => ['nullable', 'string', 'max:2000'],
        ]);

        $expediente = Expediente::query()->findOrFail(LegalCloudApi::parseId($expedienteId));
        $expediente->update([
            'estado' => LegalCloudApi::estadoInternoExpediente($datos['accion']),
            'fecha_cierre' => in_array($datos['accion'], ['cerrar', 'archivar'], true) ? now() : $expediente->fecha_cierre,
        ]);

        AuditoriaLogger::registrar('expediente.transicionar', 'expedientes', $expediente->codigo.' '.$datos['accion']);

        return LegalCloudApi::json(LegalCloudApi::expediente($expediente->fresh('documentos')));
    }

    public function participantes(Request $request, string $expedienteId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'expedientes.leer');

        $expediente = Expediente::query()->findOrFail(LegalCloudApi::parseId($expedienteId));
        $lista = $this->listaParticipantes($expediente);

        return LegalCloudApi::json(['datos' => $lista]);
    }

    public function asignarParticipante(Request $request, string $expedienteId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'expedientes.actualizar');

        $datos = $request->validate([
            'usuarioId' => ['required', 'string'],
            'rolEnExpediente' => ['required', 'in:responsable,editor,revisor,lector'],
        ]);

        $expediente = Expediente::query()->findOrFail(LegalCloudApi::parseId($expedienteId));
        $user = User::query()->findOrFail(LegalCloudApi::parseId($datos['usuarioId']));

        $lista = $this->listaParticipantes($expediente);
        $lista = array_values(array_filter($lista, fn (array $item) => $item['usuarioId'] !== LegalCloudApi::uuid($user->id)));
        $lista[] = [
            'usuarioId' => LegalCloudApi::uuid($user->id),
            'nombre' => $user->name,
            'correo' => $user->email,
            'rolEnExpediente' => $datos['rolEnExpediente'],
        ];

        Cache::put('api:participantes:'.$expediente->id, $lista, now()->addDays(30));

        if ($datos['rolEnExpediente'] === 'responsable') {
            $expediente->update(['responsable_id' => $user->id]);
        }

        return LegalCloudApi::json(['datos' => $lista], 201);
    }

    public function quitarParticipante(Request $request, string $expedienteId, string $usuarioId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'expedientes.actualizar');

        $expediente = Expediente::query()->findOrFail(LegalCloudApi::parseId($expedienteId));
        $lista = array_values(array_filter(
            $this->listaParticipantes($expediente),
            fn (array $item) => $item['usuarioId'] !== $usuarioId && $item['usuarioId'] !== LegalCloudApi::uuid(LegalCloudApi::parseId($usuarioId))
        ));
        Cache::put('api:participantes:'.$expediente->id, $lista, now()->addDays(30));

        return response()->json(null, 204);
    }

    public function trazabilidad(Request $request, string $expedienteId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'expedientes.leer');

        $expediente = Expediente::query()->findOrFail(LegalCloudApi::parseId($expedienteId));
        $eventos = Auditoria::query()
            ->with('user')
            ->where('modulo', 'expedientes')
            ->where('descripcion', 'like', '%'.$expediente->codigo.'%')
            ->latest()
            ->get()
            ->map(fn (Auditoria $evento) => LegalCloudApi::evento($evento));

        return LegalCloudApi::json(LegalCloudApi::pagina($eventos));
    }

    public function documentos(Request $request, string $expedienteId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'documentos.leer');

        $expediente = Expediente::query()->findOrFail(LegalCloudApi::parseId($expedienteId));
        $query = Documento::query()->where('expediente_id', $expediente->id)->latest();

        if ($tipo = $request->string('tipo')->toString()) {
            $query->where('tipo', $tipo);
        }

        $datos = $query->get()->map(fn (Documento $documento) => LegalCloudApi::documento($documento));

        return LegalCloudApi::json(LegalCloudApi::pagina($datos));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function listaParticipantes(Expediente $expediente): array
    {
        $guardados = Cache::get('api:participantes:'.$expediente->id);

        if (is_array($guardados)) {
            return $guardados;
        }

        $responsable = $expediente->responsable;

        return $responsable ? [[
            'usuarioId' => LegalCloudApi::uuid($responsable->id),
            'nombre' => $responsable->name,
            'correo' => $responsable->email,
            'rolEnExpediente' => 'responsable',
        ]] : [];
    }
}
