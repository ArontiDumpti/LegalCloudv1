<?php

namespace App\Services;

use App\Models\Documento;
use App\Support\AuditoriaLogger;
use App\Support\PdfSimple;
use Illuminate\Support\Facades\Storage;

class OcrService
{
    public function extraer(Documento $documento): Documento
    {
        $texto = '';

        if ($documento->archivo_ruta && Storage::disk('public')->exists($documento->archivo_ruta)) {
            $texto = trim(PdfSimple::extraer(Storage::disk('public')->get($documento->archivo_ruta) ?: ''));
        }

        if ($texto === '') {
            $texto = trim((string) $documento->contenido_ocr);
        }

        if ($texto === '') {
            $texto = trim((string) ($documento->versiones()->value('contenido') ?? ''));
        }

        $documento->update([
            'contenido_ocr' => $texto !== '' ? $texto : $documento->contenido_ocr,
            'ocr_estado' => $texto !== '' ? 'procesado' : 'pendiente',
        ]);

        AuditoriaLogger::registrar('ocr.extraer', 'ocr', $documento->codigo);

        return $documento->fresh();
    }
}
