<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\Categoria;
use App\Models\ConsultaRag;
use App\Models\Documento;
use App\Models\Expediente;
use App\Models\Role;
use App\Support\AuditoriaLogger;
use App\Support\LegalCloudApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizacionController extends Controller
{
    public function salud(): JsonResponse
    {
        return LegalCloudApi::json([
            'estado' => 'ok',
            'version' => '1.0.0',
            'contrato' => 'legalcloud-ai.v1',
            'generadoEn' => now()->toIso8601String(),
        ]);
    }

    public function permisos(Request $request): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'roles.leer');

        return LegalCloudApi::json(['datos' => LegalCloudApi::catalogoPermisos()]);
    }

    public function roles(Request $request): JsonResponse
    {
        LegalCloudApi::exigeSinConsultor($request->user());

        $roles = Role::query()->orderBy('id')->get()->map(fn (Role $role) => LegalCloudApi::rolCompleto($role));

        return LegalCloudApi::json(LegalCloudApi::pagina($roles));
    }

    public function rol(Request $request, string $rolId): JsonResponse
    {
        LegalCloudApi::exigeSinConsultor($request->user());

        $role = Role::query()->findOrFail(LegalCloudApi::parseId($rolId));

        return LegalCloudApi::json(LegalCloudApi::rolCompleto($role));
    }

    public function actualizarRol(Request $request, string $rolId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'roles.actualizar');

        $datos = $request->validate([
            'nombre' => ['nullable', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ]);

        $role = Role::query()->findOrFail(LegalCloudApi::parseId($rolId));
        $role->update(array_filter($datos, fn ($valor) => $valor !== null));

        return LegalCloudApi::json(LegalCloudApi::rolCompleto($role->fresh()));
    }

    public function actualizarPermisosRol(Request $request, string $rolId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'roles.actualizar');
        $request->validate(['permisos' => ['required', 'array', 'min:1']]);
        $role = Role::query()->findOrFail(LegalCloudApi::parseId($rolId));

        return LegalCloudApi::json(LegalCloudApi::rolCompleto($role));
    }

    public function categorias(Request $request): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'categorias.leer');

        $categorias = Categoria::query()->orderBy('nombre')->get()->map(fn (Categoria $c) => LegalCloudApi::categoria($c));

        return LegalCloudApi::json(LegalCloudApi::pagina($categorias));
    }

    public function crearCategoria(Request $request): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'categorias.crear');

        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:80'],
            'nombre' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string'],
        ]);

        $categoria = Categoria::create([
            'slug' => $datos['codigo'],
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'color' => '#1D4ED8',
        ]);

        AuditoriaLogger::registrar('categoria.crear', 'categorias', 'Categoría '.$categoria->nombre);

        return LegalCloudApi::json(LegalCloudApi::categoria($categoria), 201);
    }

    public function categoria(Request $request, string $categoriaId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'categorias.leer');

        $categoria = Categoria::query()->findOrFail(LegalCloudApi::parseId($categoriaId));

        return LegalCloudApi::json(LegalCloudApi::categoria($categoria));
    }

    public function actualizarCategoria(Request $request, string $categoriaId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'categorias.actualizar');

        $datos = $request->validate([
            'nombre' => ['nullable', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string'],
        ]);

        $categoria = Categoria::query()->findOrFail(LegalCloudApi::parseId($categoriaId));
        $categoria->update(array_filter([
            'nombre' => $datos['nombre'] ?? null,
            'descripcion' => $datos['descripcion'] ?? null,
        ], fn ($valor) => $valor !== null));

        return LegalCloudApi::json(LegalCloudApi::categoria($categoria->fresh()));
    }

    public function desactivarCategoria(Request $request, string $categoriaId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'categorias.actualizar');
        Categoria::query()->findOrFail(LegalCloudApi::parseId($categoriaId));

        return response()->json(null, 204);
    }

    public function dashboard(Request $request): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'dashboard.leer');

        $expedientes = Expediente::query()->get();
        $porEstado = [
            'borrador' => 0,
            'en_tramite' => 0,
            'en_revision' => 0,
            'cerrado' => 0,
            'archivado' => 0,
        ];

        foreach ($expedientes as $expediente) {
            $estado = LegalCloudApi::estadoExpediente($expediente->estado);
            $porEstado[$estado] = ($porEstado[$estado] ?? 0) + 1;
        }

        $documentos = Documento::query()->get();
        $porProcesamiento = [
            'pendiente_carga' => 0,
            'en_ocr' => 0,
            'en_indexacion' => 0,
            'indexado' => 0,
            'error' => 0,
            'archivado' => 0,
        ];

        foreach ($documentos as $documento) {
            $estado = LegalCloudApi::estadoProcesamiento($documento);
            $porProcesamiento[$estado] = ($porProcesamiento[$estado] ?? 0) + 1;
        }

        $actividad = Auditoria::query()
            ->with('user')
            ->latest()
            ->take(8)
            ->get()
            ->map(fn (Auditoria $evento) => LegalCloudApi::evento($evento));

        return LegalCloudApi::json([
            'expedientesPorEstado' => $porEstado,
            'documentosPorProcesamiento' => $porProcesamiento,
            'consultas7d' => ConsultaRag::query()->where('created_at', '>=', now()->subDays(7))->count(),
            'resumenes7d' => 0,
            'almacenamientoBytes' => (int) Documento::query()->sum('tamano'),
            'actividadReciente' => $actividad,
            'generadoEn' => now()->toIso8601String(),
        ]);
    }
}
