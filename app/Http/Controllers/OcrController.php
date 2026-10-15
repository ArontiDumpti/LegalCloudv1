<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Services\IngestionService;
use App\Support\AuditoriaLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OcrController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('ocr/index', [
            'documentos' => Documento::with('expediente')->latest()->get()->map(function (Documento $documento) {
                $data = $documento->toArray();
                $data['archivo_url'] = $documento->archivo_ruta
                    ? route('documentos.archivo', $documento)
                    : null;

                return $data;
            }),
        ]);
    }

    public function procesar(Documento $documento, IngestionService $ingesta): RedirectResponse
    {
        $ingesta->procesar($documento);

        AuditoriaLogger::registrar('ocr_procesado', 'ocr', "OCR, embeddings e ingesta de {$documento->codigo}");

        return back()->with('success', 'OCR y embeddings listos para búsqueda semántica y RAG.');
    }
}
