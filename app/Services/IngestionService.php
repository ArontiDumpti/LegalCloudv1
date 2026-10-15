<?php

namespace App\Services;

use App\Models\Documento;
use App\Services\Gemini\GeminiApi;
use App\Support\AuditoriaLogger;

class IngestionService
{
    public function __construct(
        private GeminiApi $gemini,
        private LegalVectorDatabase $vectores,
        private OcrService $ocr,
    ) {}

    public function asegurarIndice(): void
    {
        $pendientes = Documento::query()
            ->where(function ($consulta) {
                $consulta->where(function ($q) {
                    $q->whereNotNull('contenido_ocr')->where('contenido_ocr', '!=', '');
                })->orWhereNotNull('archivo_ruta');
            })
            ->whereDoesntHave('fragmentos')
            ->get();

        foreach ($pendientes as $documento) {
            $this->procesar($documento);
        }
    }

    public function procesar(Documento $documento): Documento
    {
        $documento = $this->ocr->extraer($documento);
        $texto = trim((string) $documento->contenido_ocr);

        if ($texto === '') {
            $documento->update(['embedding_estado' => 'pendiente']);

            return $documento;
        }

        $fragmentos = [];
        foreach ($this->partir($texto) as $indice => $bloque) {
            $fragmentos[] = [
                'orden' => $indice + 1,
                'texto' => $bloque,
                'embedding' => $this->gemini->incrustar($bloque, 'RETRIEVAL_DOCUMENT'),
            ];
        }

        $modelo = $this->gemini->disponible()
            ? (string) config('services.gemini.embedding_model')
            : 'gemini-embedding-001 (local)';

        $this->vectores->reemplazar($documento, $fragmentos, $modelo);

        AuditoriaLogger::registrar('ingesta.procesar', 'ingestions', $documento->codigo);

        return $documento->fresh();
    }

    /**
     * @return list<string>
     */
    private function partir(string $contenido): array
    {
        $bloques = preg_split('/\n\s*\n/', trim($contenido)) ?: [];
        $limpios = array_values(array_filter(array_map('trim', $bloques), fn (string $b) => mb_strlen($b) > 40));

        return $limpios !== [] ? $limpios : [mb_strimwidth($contenido, 0, 1200, '')];
    }
}
