<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Documento;
use App\Services\IngestionService;
use App\Services\MsRagService;
use App\Support\LegalCloudApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArquitecturaLegalController extends Controller
{
    public function ingestions(Request $request, IngestionService $ingesta): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'documentos.actualizar');

        $datos = $request->validate([
            'documentoId' => ['required', 'string'],
        ]);

        $documento = Documento::query()->findOrFail(LegalCloudApi::parseId($datos['documentoId']));
        $documento = $ingesta->procesar($documento);

        return LegalCloudApi::json([
            'recurso' => '/ingestions',
            'documentoId' => LegalCloudApi::uuid($documento->id),
            'ocr' => $documento->ocr_estado,
            'embeddings' => $documento->embedding_estado,
            'estado' => $documento->embedding_estado,
            'fragmentos' => $documento->fragmentos()->count(),
            'modelo' => $documento->fragmentos()->value('modelo'),
        ], 201);
    }

    public function queries(Request $request, MsRagService $rag): JsonResponse
    {
        LegalCloudApi::exige($request->user(), 'asistente.conversar');

        $datos = $request->validate([
            'consulta' => ['required_without:pregunta', 'string'],
            'pregunta' => ['required_without:consulta', 'string'],
        ]);

        $consulta = $rag->consultar($datos['consulta'] ?? $datos['pregunta'], $request->user());
        $mensaje = LegalCloudApi::mensaje($consulta);

        return LegalCloudApi::json([
            'recurso' => '/queries',
            'resulttext' => $consulta->respuesta,
            'fuentes' => $mensaje['fuentes'],
        ]);
    }
}
