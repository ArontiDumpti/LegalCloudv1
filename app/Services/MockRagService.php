<?php

namespace App\Services;

use App\Models\ConsultaRag;
use App\Models\Documento;
use App\Models\User;
use App\Support\AuditoriaLogger;

class MockRagService
{
    /**
     * Recupera fragmentos por coincidencia de palabras y arma una respuesta
     * demostrativa. No llama a un LLM ni genera embeddings reales.
     */
    public function consultar(string $pregunta, User $user): ConsultaRag
    {
        $documentos = Documento::query()
            ->where('embedding_estado', 'indexado')
            ->whereNotNull('contenido_ocr')
            ->with(['expediente', 'categoria'])
            ->get();

        $fragmentos = [];

        foreach ($documentos as $documento) {
            foreach ($this->fragmentar($documento->contenido_ocr) as $texto) {
                $puntaje = $this->puntuar($pregunta, $texto);

                if ($puntaje <= 0) {
                    continue;
                }

                $fragmentos[] = [
                    'documento_id' => $documento->id,
                    'codigo' => $documento->codigo,
                    'titulo' => $documento->titulo,
                    'expediente' => $documento->expediente?->codigo,
                    'texto' => $texto,
                    'puntaje' => $puntaje,
                ];
            }
        }

        usort($fragmentos, fn (array $a, array $b) => $b['puntaje'] <=> $a['puntaje']);
        $fuentes = array_slice($fragmentos, 0, 3);

        $respuesta = $this->componerRespuesta($pregunta, $fuentes);

        $consulta = ConsultaRag::create([
            'user_id' => $user->id,
            'pregunta' => $pregunta,
            'respuesta' => $respuesta,
            'fuentes' => $fuentes,
            'modelo_simulado' => 'LegalCloud RAG (demo, sin LLM)',
        ]);

        AuditoriaLogger::registrar(
            'consulta_rag',
            'asistente',
            "Consulta al asistente: {$pregunta}"
        );

        return $consulta;
    }

    /**
     * @return list<string>
     */
    private function fragmentar(string $contenido): array
    {
        $bloques = preg_split('/\n\s*\n/', trim($contenido)) ?: [];

        return array_values(array_filter(array_map('trim', $bloques)));
    }

    private function puntuar(string $pregunta, string $texto): int
    {
        $terminos = $this->terminos($pregunta);
        $corpus = mb_strtolower($texto);
        $puntaje = 0;

        foreach ($terminos as $termino) {
            if (mb_strpos($corpus, $termino) !== false) {
                $puntaje += 2;
            }
        }

        if ($this->esPreguntaDeRequisitos($pregunta) && str_contains($corpus, 'artículo 12')) {
            $puntaje += 8;
        }

        if ($this->contiene($pregunta, ['plazo', 'plazos', 'recurso', 'recursos']) && str_contains($corpus, 'artículo 18')) {
            $puntaje += 8;
        }

        if ($this->contiene($pregunta, ['firmar', 'firma', 'solicitud']) && str_contains($corpus, 'artículo 8')) {
            $puntaje += 8;
        }

        return $puntaje;
    }

    /**
     * @param  list<array<string, mixed>>  $fuentes
     */
    private function componerRespuesta(string $pregunta, array $fuentes): string
    {
        if ($fuentes === []) {
            return 'No se encontró información suficiente en el acervo documental indexado para responder esa pregunta. Pruebe con otra formulación o revise que el documento esté procesado por OCR.';
        }

        if ($this->esPreguntaDeRequisitos($pregunta)) {
            return <<<'TXT'
Según el Reglamento General de Trámites Administrativos (Resolución N.º 187-2024), los requisitos establecidos para iniciar un procedimiento administrativo son:

1. Solicitud escrita, física o digital, dirigida a la dependencia competente.
2. Copia del documento de identidad o, si es persona jurídica, certificado de existencia y representación legal.
3. Formulario oficial del trámite, diligenciado y firmado.
4. Soportes que acrediten los hechos o derechos invocados.
5. Comprobante de pago de la tasa administrativa, cuando corresponda.
6. Poder o autorización, si actúa un tercero.
7. Dirección física y correo electrónico para notificaciones.

Si la solicitud está incompleta, la entidad requiere al interesado para que subsane en cinco (5) días hábiles. Esta respuesta es demostrativa: se armó con coincidencias sobre el texto almacenado, sin un modelo de lenguaje real.
TXT;
        }

        if ($this->contiene($pregunta, ['plazo', 'plazos', 'recurso', 'recursos'])) {
            return 'De acuerdo con el artículo 18 de la Resolución 187-2024, los recursos de reposición y apelación se interponen dentro de los diez (10) días hábiles siguientes a la notificación del acto. El recurso de queja procede en cinco (5) días hábiles. Los plazos se cuentan en días hábiles, según la Resolución 042-2025. Respuesta demostrativa, sustentada en los fragmentos recuperados.';
        }

        if ($this->contiene($pregunta, ['firmar', 'firma', 'quién', 'quien'])) {
            return 'Según el artículo 8 de la Resolución 187-2024, la solicitud puede firmarla el interesado (manuscrita o con firma electrónica avanzada), un apoderado con poder suficiente, o el representante legal de una persona jurídica con certificado vigente no mayor a treinta días. Respuesta demostrativa, sin LLM.';
        }

        $extractos = collect($fuentes)
            ->map(fn (array $fuente) => '• '.$fuente['codigo'].': '.mb_strimwidth(preg_replace('/\s+/', ' ', $fuente['texto']) ?? '', 0, 280, '…'))
            ->implode("\n");

        return "Según la documentación recuperada en el acervo, estos son los pasajes más pertinentes:\n\n{$extractos}\n\nEsta es una respuesta demostrativa generada por coincidencia de términos, no por un modelo de IA.";
    }

    private function esPreguntaDeRequisitos(string $pregunta): bool
    {
        return $this->contiene($pregunta, ['requisito', 'requisitos'])
            && $this->contiene($pregunta, ['procedimiento', 'tramite', 'trámite', 'tramites', 'trámites']);
    }

    /**
     * @param  list<string>  $palabras
     */
    private function contiene(string $texto, array $palabras): bool
    {
        $normalizado = $this->normalizar($texto);

        foreach ($palabras as $palabra) {
            if (str_contains($normalizado, $this->normalizar($palabra))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function terminos(string $texto): array
    {
        $vacias = ['cuales', 'cuáles', 'como', 'cómo', 'para', 'realizar', 'determinado', 'establecidos', 'sobre', 'esta', 'este', 'los', 'las', 'una', 'unos', 'del', 'que', 'con', 'por', 'son'];
        $tokens = preg_split('/[^\p{L}\p{N}]+/u', $this->normalizar($texto)) ?: [];

        return array_values(array_filter(
            $tokens,
            fn (string $token) => mb_strlen($token) > 3 && ! in_array($token, $vacias, true)
        ));
    }

    private function normalizar(string $texto): string
    {
        $texto = mb_strtolower($texto);
        $reemplazos = ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n'];

        return strtr($texto, $reemplazos);
    }
}
