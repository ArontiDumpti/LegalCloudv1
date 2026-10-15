<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Support\AuditoriaLogger;
use App\Support\PdfSimple;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
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

    public function procesar(Documento $documento): RedirectResponse
    {
        if ($documento->archivo_ruta && Storage::disk('public')->exists($documento->archivo_ruta)) {
            $extraido = PdfSimple::extraer(Storage::disk('public')->get($documento->archivo_ruta) ?: '');
            if ($extraido !== '') {
                $documento->contenido_ocr = $extraido;
            }
        }

        if (! $documento->contenido_ocr) {
            $documento->contenido_ocr = "TEXTO EXTRAÍDO (OCR SIMULADO)\n\n{$documento->titulo}\nCódigo: {$documento->codigo}\n\nEste es un mockup: no se ejecuta un motor OCR real. El sistema marcaría el documento como procesado y dejaría el texto disponible para búsqueda e indexación.";
        }

        $documento->ocr_estado = 'procesado';
        $documento->embedding_estado = 'indexado';
        $documento->save();

        AuditoriaLogger::registrar('ocr_procesado', 'ocr', "OCR simulado sobre {$documento->codigo}");

        return back()->with('success', 'OCR simulado: el texto quedó disponible para búsqueda y el asistente.');
    }
}
