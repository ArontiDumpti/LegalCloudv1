<?php

namespace App\Services;

use App\Models\ConsultaRag;
use App\Models\User;
use App\Services\Gemini\GeminiApi;
use App\Support\AuditoriaLogger;
use Throwable;

class MsRagService
{
    public function __construct(
        private GeminiApi $gemini,
        private LegalVectorDatabase $vectores,
        private IngestionService $ingesta,
    ) {}

    public function consultar(string $pregunta, User $user): ConsultaRag
    {
        $this->ingesta->asegurarIndice();

        $consulta = $this->gemini->incrustar($pregunta, 'RETRIEVAL_QUERY');
        $fuentes = $this->vectores->buscar($consulta, 4);
        $fuentes = array_values(array_filter($fuentes, fn (array $f) => $f['puntaje'] > 0.05));

        $respuesta = $this->responder($pregunta, $fuentes);
        $modelo = $this->gemini->disponible()
            ? (string) config('services.gemini.flash_model')
            : 'ms-rag + gemini-2.5-flash (reserva local)';

        $consultaModelo = ConsultaRag::create([
            'user_id' => $user->id,
            'pregunta' => $pregunta,
            'respuesta' => $respuesta,
            'fuentes' => $fuentes,
            'modelo_simulado' => $modelo,
        ]);

        AuditoriaLogger::registrar('asistente.consultar', 'queries', $pregunta);

        return $consultaModelo;
    }

    /**
     * @param  list<array<string, mixed>>  $fuentes
     */
    private function responder(string $pregunta, array $fuentes): string
    {
        if ($fuentes === []) {
            return 'No hay evidencia suficiente en la base vectorial legal para responder esa pregunta.';
        }

        $contexto = collect($fuentes)
            ->map(fn (array $f, int $i) => ($i + 1).'. '.($f['codigo'] ?? 'DOC').': '.$f['texto'])
            ->implode("\n\n");

        $prompt = <<<TXT
Eres el asistente de LegalCloud AI. Responde solo con el contexto recuperado de la base vectorial legal.
Si no alcanza la evidencia, dilo con claridad. No inventes requisitos.

Pregunta:
{$pregunta}

Contexto:
{$contexto}
TXT;

        try {
            return $this->gemini->generar($prompt);
        } catch (Throwable) {
            return $this->reservaLocal($pregunta, $fuentes);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $fuentes
     */
    private function reservaLocal(string $pregunta, array $fuentes): string
    {
        $extractos = collect($fuentes)
            ->map(fn (array $fuente) => '• '.($fuente['codigo'] ?? '').': '.mb_strimwidth(preg_replace('/\s+/', ' ', (string) $fuente['texto']) ?? '', 0, 280, '…'))
            ->implode("\n");

        return "Según los fragmentos recuperados por ms-rag desde la base vectorial legal:\n\n{$extractos}";
    }

}
