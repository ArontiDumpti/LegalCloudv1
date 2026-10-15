<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Documento;
use App\Models\DocumentoVersion;
use App\Models\Expediente;
use App\Support\AuditoriaLogger;
use App\Support\LegalCloudApi;
use App\Support\PdfSimple;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class DocumentoApiController extends Controller
{
    public function iniciar(Request $request, string $expedienteId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'documentos.crear');

        $datos = $request->validate([
            'titulo' => ['required', 'string', 'max:240'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['required', 'string'],
            'origen' => ['required', 'in:digital,digitalizado'],
            'categoriaId' => ['nullable', 'string'],
            'nombreArchivo' => ['required', 'string', 'max:255'],
            'tipoMime' => ['required', 'string'],
            'tamanoBytes' => ['required', 'integer', 'min:1'],
            'notas' => ['nullable', 'string'],
        ]);

        $expediente = Expediente::query()->findOrFail(LegalCloudApi::parseId($expedienteId));
        $siguiente = Documento::count() + 1;
        $codigo = 'DOC-'.now()->year.'-'.str_pad((string) $siguiente, 4, '0', STR_PAD_LEFT);

        $documento = Documento::create([
            'expediente_id' => $expediente->id,
            'categoria_id' => isset($datos['categoriaId']) ? LegalCloudApi::parseId($datos['categoriaId']) : $expediente->categoria_id,
            'codigo' => $codigo,
            'titulo' => $datos['titulo'],
            'tipo' => $datos['tipo'],
            'descripcion' => $datos['descripcion'] ?? null,
            'archivo_nombre' => $datos['nombreArchivo'],
            'archivo_ruta' => null,
            'mime' => $datos['tipoMime'],
            'tamano' => $datos['tamanoBytes'],
            'version_actual' => 1,
            'ocr_estado' => 'pendiente',
            'embedding_estado' => 'pendiente',
            'subido_por' => $request->user()->id,
        ]);

        $version = DocumentoVersion::create([
            'documento_id' => $documento->id,
            'numero' => 1,
            'resumen_cambios' => $datos['notas'] ?? 'Reserva de carga API.',
            'contenido' => null,
            'subido_por' => $request->user()->id,
        ]);

        $token = (string) Str::uuid();
        Cache::put('api:carga:'.$token, [
            'documento_id' => $documento->id,
            'version_id' => $version->id,
        ], now()->addHour());

        AuditoriaLogger::registrar('documento.iniciar', 'documentos', $documento->codigo);

        return LegalCloudApi::json([
            'documento' => LegalCloudApi::documento($documento->fresh()),
            'version' => LegalCloudApi::version($version, $documento),
            'carga' => [
                'metodo' => 'PUT',
                'url' => url('/api/v1/cargas/'.$token),
                'expiraEn' => now()->addHour()->toIso8601String(),
                'encabezados' => [
                    'Content-Type' => $datos['tipoMime'],
                ],
            ],
        ], 201);
    }

    public function recibirCarga(Request $request, string $token): Response
    {
        $reserva = Cache::get('api:carga:'.$token);

        if (! is_array($reserva)) {
            return LegalCloudApi::problema(404, 'No encontrado', 'La URL de carga expiró.');
        }

        $binario = $request->getContent();

        if ($binario === '' && $request->hasFile('archivo')) {
            $binario = file_get_contents($request->file('archivo')->getRealPath()) ?: '';
        }

        if ($binario === '') {
            return LegalCloudApi::problema(422, 'Entidad improcesable', 'El cuerpo de la carga está vacío.');
        }

        $documento = Documento::query()->findOrFail($reserva['documento_id']);
        $extension = pathinfo((string) $documento->archivo_nombre, PATHINFO_EXTENSION) ?: 'pdf';
        $ruta = 'documentos/'.Str::slug($documento->codigo).'.'.$extension;
        Storage::disk('public')->put($ruta, $binario);

        $documento->update([
            'archivo_ruta' => $ruta,
            'tamano' => strlen($binario),
        ]);

        Cache::put('api:carga:'.$token, array_merge($reserva, ['cargado' => true]), now()->addHour());

        return response()->noContent();
    }

    public function show(Request $request, string $documentoId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'documentos.leer');

        $documento = Documento::query()->findOrFail(LegalCloudApi::parseId($documentoId));

        return LegalCloudApi::json(LegalCloudApi::documento($documento));
    }

    public function update(Request $request, string $documentoId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'documentos.actualizar');

        $datos = $request->validate([
            'titulo' => ['nullable', 'string', 'max:240'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['nullable', 'string'],
            'categoriaId' => ['nullable', 'string'],
        ]);

        $documento = Documento::query()->findOrFail(LegalCloudApi::parseId($documentoId));
        $documento->update(array_filter([
            'titulo' => $datos['titulo'] ?? null,
            'descripcion' => $datos['descripcion'] ?? null,
            'tipo' => $datos['tipo'] ?? null,
            'categoria_id' => isset($datos['categoriaId']) ? LegalCloudApi::parseId($datos['categoriaId']) : null,
        ], fn ($valor) => $valor !== null));

        return LegalCloudApi::json(LegalCloudApi::documento($documento->fresh()));
    }

    public function archivar(Request $request, string $documentoId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'documentos.actualizar');

        $documento = Documento::query()->findOrFail(LegalCloudApi::parseId($documentoId));
        $documento->update(['embedding_estado' => 'archivado']);

        return response()->json(null, 204);
    }

    public function versiones(Request $request, string $documentoId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'documentos.leer');

        $documento = Documento::query()->findOrFail(LegalCloudApi::parseId($documentoId));
        $datos = $documento->versiones->map(fn (DocumentoVersion $version) => LegalCloudApi::version($version, $documento));

        return LegalCloudApi::json(LegalCloudApi::pagina($datos));
    }

    public function version(Request $request, string $documentoId, string $versionId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'documentos.leer');

        $documento = Documento::query()->findOrFail(LegalCloudApi::parseId($documentoId));
        $version = $documento->versiones()->findOrFail(LegalCloudApi::parseId($versionId));

        return LegalCloudApi::json(LegalCloudApi::version($version, $documento));
    }

    public function confirmar(Request $request, string $documentoId, string $versionId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'documentos.crear');

        $documento = Documento::query()->findOrFail(LegalCloudApi::parseId($documentoId));
        $version = $documento->versiones()->findOrFail(LegalCloudApi::parseId($versionId));

        $texto = '';
        if ($documento->archivo_ruta && Storage::disk('public')->exists($documento->archivo_ruta)) {
            $texto = PdfSimple::extraer(Storage::disk('public')->get($documento->archivo_ruta) ?: '');
        }

        $procesado = $texto !== '';
        $documento->update([
            'ocr_estado' => $procesado ? 'procesado' : 'pendiente',
            'contenido_ocr' => $procesado ? $texto : $documento->contenido_ocr,
            'embedding_estado' => $procesado ? 'indexado' : 'pendiente',
            'version_actual' => $version->numero,
        ]);
        $version->update(['contenido' => $procesado ? $texto : $version->contenido]);

        AuditoriaLogger::registrar('version.confirmar', 'documentos', $documento->codigo.' v'.$version->numero);

        return LegalCloudApi::json([
            'version' => LegalCloudApi::version($version->fresh(), $documento->fresh()),
            'trabajo' => [
                'id' => LegalCloudApi::uuid($documento->id),
                'tipo' => 'procesamiento',
                'estado' => $procesado ? 'completado' : 'en_cola',
                'documentoId' => LegalCloudApi::uuid($documento->id),
                'versionId' => LegalCloudApi::uuid($version->id),
                'creadoEn' => now()->toIso8601String(),
            ],
        ]);
    }

    public function restaurar(Request $request, string $documentoId, string $versionId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'documentos.actualizar');

        $documento = Documento::query()->findOrFail(LegalCloudApi::parseId($documentoId));
        $origen = $documento->versiones()->findOrFail(LegalCloudApi::parseId($versionId));
        $numero = (int) $documento->versiones()->max('numero') + 1;

        $nueva = DocumentoVersion::create([
            'documento_id' => $documento->id,
            'numero' => $numero,
            'resumen_cambios' => 'Restauración de la versión '.$origen->numero,
            'contenido' => $origen->contenido,
            'subido_por' => $request->user()->id,
        ]);

        $documento->update([
            'version_actual' => $numero,
            'contenido_ocr' => $origen->contenido,
            'ocr_estado' => $origen->contenido ? 'procesado' : $documento->ocr_estado,
            'embedding_estado' => $origen->contenido ? 'indexado' : $documento->embedding_estado,
        ]);

        return LegalCloudApi::json(LegalCloudApi::version($nueva, $documento->fresh()), 201);
    }

    public function descarga(Request $request, string $documentoId, string $versionId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'documentos.descargar');

        $documento = Documento::query()->findOrFail(LegalCloudApi::parseId($documentoId));
        $documento->versiones()->findOrFail(LegalCloudApi::parseId($versionId));

        AuditoriaLogger::registrar('documento.descargar', 'documentos', $documento->codigo);

        $url = $documento->archivo_ruta
            ? url('/storage/'.$documento->archivo_ruta)
            : url('/ejemplos/RES-2024-0187-reglamento-tramites.pdf');

        return LegalCloudApi::json([
            'url' => $url,
            'expiraEn' => now()->addMinutes(15)->toIso8601String(),
            'nombreArchivo' => $documento->archivo_nombre,
            'hashSha256' => hash('sha256', $documento->codigo),
        ]);
    }

    public function texto(Request $request, string $documentoId, string $versionId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'documentos.leer');

        $documento = Documento::query()->findOrFail(LegalCloudApi::parseId($documentoId));
        $version = $documento->versiones()->findOrFail(LegalCloudApi::parseId($versionId));
        $texto = $version->contenido ?: $documento->contenido_ocr ?: '';

        return LegalCloudApi::json([
            'versionId' => LegalCloudApi::uuid($version->id),
            'datos' => $texto === '' ? [] : [[
                'pagina' => 1,
                'texto' => $texto,
            ]],
            'paginacion' => ['limit' => 50, 'nextCursor' => null],
        ]);
    }

    public function reprocesar(Request $request, string $documentoId, string $versionId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'documentos.actualizar');

        $documento = Documento::query()->findOrFail(LegalCloudApi::parseId($documentoId));
        $version = $documento->versiones()->findOrFail(LegalCloudApi::parseId($versionId));

        $texto = '';
        if ($documento->archivo_ruta && Storage::disk('public')->exists($documento->archivo_ruta)) {
            $texto = PdfSimple::extraer(Storage::disk('public')->get($documento->archivo_ruta) ?: '');
        }

        $documento->update([
            'ocr_estado' => $texto !== '' ? 'procesado' : 'pendiente',
            'contenido_ocr' => $texto !== '' ? $texto : $documento->contenido_ocr,
            'embedding_estado' => $texto !== '' ? 'indexado' : 'pendiente',
        ]);

        AuditoriaLogger::registrar('procesamiento.ejecutar', 'ocr', $documento->codigo);

        return LegalCloudApi::json([
            'id' => LegalCloudApi::uuid($documento->id),
            'tipo' => 'procesamiento',
            'estado' => $texto !== '' ? 'completado' : 'en_cola',
            'documentoId' => LegalCloudApi::uuid($documento->id),
            'versionId' => LegalCloudApi::uuid($version->id),
            'creadoEn' => now()->toIso8601String(),
        ], 202);
    }

    public function trabajo(Request $request, string $trabajoId): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'documentos.leer');

        $documento = Documento::query()->findOrFail(LegalCloudApi::parseId($trabajoId));

        return LegalCloudApi::json([
            'id' => LegalCloudApi::uuid($documento->id),
            'tipo' => 'procesamiento',
            'estado' => LegalCloudApi::estadoProcesamiento($documento) === 'indexado' ? 'completado' : 'en_cola',
            'documentoId' => LegalCloudApi::uuid($documento->id),
            'creadoEn' => optional($documento->updated_at)?->toIso8601String(),
        ]);
    }
}
