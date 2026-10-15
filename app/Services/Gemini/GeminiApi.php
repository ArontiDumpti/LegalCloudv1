<?php

namespace App\Services\Gemini;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiApi
{
    public function disponible(): bool
    {
        return filled(config('services.gemini.key'));
    }

    /**
     * @return list<float>
     */
    public function incrustar(string $texto, string $tarea = 'RETRIEVAL_DOCUMENT'): array
    {
        if (! $this->disponible()) {
            return $this->incrustacionLocal($texto);
        }

        $modelo = config('services.gemini.embedding_model');
        $respuesta = Http::timeout(40)
            ->acceptJson()
            ->post($this->url($modelo, 'embedContent'), [
                'model' => 'models/'.$modelo,
                'content' => ['parts' => [['text' => mb_substr($texto, 0, 8000)]]],
                'taskType' => $tarea,
            ]);

        if (! $respuesta->successful()) {
            return $this->incrustacionLocal($texto);
        }

        $valores = $respuesta->json('embedding.values');

        return is_array($valores) ? array_map('floatval', $valores) : $this->incrustacionLocal($texto);
    }

    public function generar(string $prompt): string
    {
        if (! $this->disponible()) {
            throw new RuntimeException('Gemini no está configurado.');
        }

        $modelo = config('services.gemini.flash_model');
        $respuesta = Http::timeout(60)
            ->acceptJson()
            ->post($this->url($modelo, 'generateContent'), [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
            ]);

        if (! $respuesta->successful()) {
            throw new RuntimeException('Gemini Flash no respondió.');
        }

        $texto = $respuesta->json('candidates.0.content.parts.0.text');

        if (! is_string($texto) || trim($texto) === '') {
            throw new RuntimeException('Gemini Flash devolvió una respuesta vacía.');
        }

        return trim($texto);
    }

    private function url(string $modelo, string $metodo): string
    {
        $clave = urlencode((string) config('services.gemini.key'));

        return 'https://generativelanguage.googleapis.com/v1beta/models/'.$modelo.':'.$metodo.'?key='.$clave;
    }

    /**
     * Vector local para que la arquitectura funcione sin clave (demo / tesis).
     *
     * @return list<float>
     */
    public function incrustacionLocal(string $texto): array
    {
        $dim = 64;
        $vector = array_fill(0, $dim, 0.0);
        $tokens = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($texto)) ?: [];

        foreach ($tokens as $token) {
            if (mb_strlen($token) < 3) {
                continue;
            }
            $indice = crc32($token) % $dim;
            $vector[$indice] += 1.0;
        }

        $norma = sqrt(array_sum(array_map(fn (float $v) => $v * $v, $vector))) ?: 1.0;

        return array_map(fn (float $v) => $v / $norma, $vector);
    }
}
