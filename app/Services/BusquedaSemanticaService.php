<?php

namespace App\Services;

use App\Models\Documento;
use App\Services\Gemini\GeminiApi;
use App\Support\LegalCloudApi;

class BusquedaSemanticaService
{
    public function __construct(
        private GeminiApi $gemini,
        private LegalVectorDatabase $vectores,
        private IngestionService $ingesta,
    ) {}

    /**
     * @return list<array{documento: array<string, mixed>, puntaje: float, fragmento: string}>
     */
    public function buscar(string $consulta, int $limite = 10, ?int $expedienteId = null): array
    {
        $this->ingesta->asegurarIndice();

        $vector = $this->gemini->incrustar($consulta, 'RETRIEVAL_QUERY');
        $hits = $this->vectores->buscar($vector, max($limite * 3, 12));

        $porDocumento = [];
        foreach ($hits as $hit) {
            if ($hit['puntaje'] <= 0.05) {
                continue;
            }

            $id = (int) $hit['documento_id'];
            if (! isset($porDocumento[$id]) || $hit['puntaje'] > $porDocumento[$id]['puntaje']) {
                $porDocumento[$id] = $hit;
            }
        }

        $documentos = Documento::query()
            ->with(['expediente', 'categoria'])
            ->whereIn('id', array_keys($porDocumento))
            ->when($expedienteId, fn ($q) => $q->where('expediente_id', $expedienteId))
            ->get()
            ->keyBy('id');

        $resultados = [];
        foreach ($porDocumento as $id => $hit) {
            $documento = $documentos->get($id);
            if (! $documento) {
                continue;
            }

            $resultados[] = [
                'documento' => LegalCloudApi::documento($documento),
                'puntaje' => round((float) $hit['puntaje'], 4),
                'fragmento' => mb_strimwidth(preg_replace('/\s+/', ' ', (string) $hit['texto']) ?: $documento->titulo, 0, 280, '…'),
            ];
        }

        usort($resultados, fn (array $a, array $b) => $b['puntaje'] <=> $a['puntaje']);

        return array_slice($resultados, 0, $limite);
    }
}
