<?php

namespace App\Services;

use App\Models\Documento;
use App\Models\FragmentoVector;

class LegalVectorDatabase
{
    /**
     * @param  list<array{orden: int, texto: string, embedding: list<float>}>  $fragmentos
     */
    public function reemplazar(Documento $documento, array $fragmentos, string $modelo): void
    {
        FragmentoVector::query()->where('documento_id', $documento->id)->delete();

        foreach ($fragmentos as $fragmento) {
            FragmentoVector::create([
                'documento_id' => $documento->id,
                'orden' => $fragmento['orden'],
                'texto' => $fragmento['texto'],
                'embedding' => $fragmento['embedding'],
                'modelo' => $modelo,
            ]);
        }

        $documento->update(['embedding_estado' => 'indexado']);
    }

    /**
     * @param  list<float>  $consulta
     * @return list<array{documento_id: int, codigo: string|null, titulo: string|null, expediente: string|null, texto: string, puntaje: float}>
     */
    public function buscar(array $consulta, int $limite = 4): array
    {
        $candidatos = FragmentoVector::query()->with('documento.expediente')->get();
        $ranqueados = [];

        foreach ($candidatos as $fragmento) {
            $vector = $fragmento->embedding;
            if (! is_array($vector) || $vector === []) {
                continue;
            }

            $ranqueados[] = [
                'documento_id' => $fragmento->documento_id,
                'codigo' => $fragmento->documento?->codigo,
                'titulo' => $fragmento->documento?->titulo,
                'expediente' => $fragmento->documento?->expediente?->codigo,
                'texto' => $fragmento->texto,
                'puntaje' => $this->coseno($consulta, $vector),
            ];
        }

        usort($ranqueados, fn (array $a, array $b) => $b['puntaje'] <=> $a['puntaje']);

        return array_slice($ranqueados, 0, $limite);
    }

    /**
     * @param  list<float>  $a
     * @param  list<float>  $b
     */
    private function coseno(array $a, array $b): float
    {
        $n = min(count($a), count($b));
        if ($n === 0) {
            return 0.0;
        }

        $dot = 0.0;
        $na = 0.0;
        $nb = 0.0;

        for ($i = 0; $i < $n; $i++) {
            $va = (float) $a[$i];
            $vb = (float) $b[$i];
            $dot += $va * $vb;
            $na += $va * $va;
            $nb += $vb * $vb;
        }

        $den = sqrt($na) * sqrt($nb);

        return $den > 0 ? $dot / $den : 0.0;
    }
}
