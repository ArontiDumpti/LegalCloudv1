<?php

namespace App\Support;

class PdfSimple
{
    /**
     * Genera un PDF básico (Helvetica + WinAnsi) con encabezado LegalCloud.
     */
    public static function generar(string $titulo, string $cuerpo): string
    {
        $lineas = self::envolver($titulo, $cuerpo);
        $paginas = array_chunk($lineas, 46);
        if ($paginas === []) {
            $paginas = [[$titulo]];
        }

        $kids = [];
        $objetos = [];
        $objetos[1] = null;
        $objetos[2] = null;
        $objetos[3] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>";
        $objetos[4] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>";

        $siguiente = 5;
        $pageIds = [];

        foreach ($paginas as $indice => $lineasPagina) {
            $contenido = self::contenidoPagina($lineasPagina, $indice + 1, count($paginas));
            $streamId = $siguiente++;
            $pageId = $siguiente++;
            $objetos[$streamId] = "<< /Length ".strlen($contenido)." >>\nstream\n{$contenido}\nendstream";
            $objetos[$pageId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents {$streamId} 0 R /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> >>";
            $pageIds[] = $pageId;
        }

        $kids = implode(' ', array_map(fn (int $id) => "{$id} 0 R", $pageIds));
        $objetos[2] = "<< /Type /Pages /Kids [{$kids}] /Count ".count($pageIds)." >>";
        $objetos[1] = "<< /Type /Catalog /Pages 2 0 R >>";

        ksort($objetos);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objetos as $id => $objeto) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$objeto}\nendobj\n";
        }

        $startxref = strlen($pdf);
        $max = max(array_keys($objetos));
        $pdf .= "xref\n0 ".($max + 1)."\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $max; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i] ?? 0);
        }
        $pdf .= "trailer << /Size ".($max + 1)." /Root 1 0 R >>\nstartxref\n{$startxref}\n%%EOF";

        return $pdf;
    }

    /**
     * Extrae texto de un PDF generado por esta clase o de un .txt.
     */
    public static function extraer(string $binario): string
    {
        if (! str_starts_with($binario, '%PDF')) {
            return trim($binario);
        }

        preg_match_all('/\((?:\\\\.|[^\\\\)])*\)\s*T[jJ]/', $binario, $coincidencias);
        $lineas = [];

        foreach ($coincidencias[0] as $pieza) {
            if (! preg_match('/^\((.*)\)\s*T[jJ]$/', $pieza, $partes)) {
                continue;
            }
            $lineas[] = self::desescapar($partes[1]);
        }

        if ($lineas === []) {
            preg_match_all('/\((?:\\\\.|[^\\\\)]){3,}\)/', $binario, $textos);
            foreach ($textos[0] as $pieza) {
                $lineas[] = self::desescapar(substr($pieza, 1, -1));
            }
        }

        $texto = trim(implode("\n", $lineas));
        $texto = preg_replace('/^LEGALCLOUD AI\n/m', '', $texto) ?? $texto;
        $texto = preg_replace('/\nPágina \d+ de \d+$/m', '', $texto) ?? $texto;

        return trim($texto);
    }

    /**
     * @return list<array{text: string, bold: bool}>
     */
    private static function envolver(string $titulo, string $cuerpo): array
    {
        $lineas = [
            ['text' => $titulo, 'bold' => true],
            ['text' => '', 'bold' => false],
        ];

        foreach (preg_split("/\r\n|\n|\r/", $cuerpo) ?: [] as $parrafo) {
            $parrafo = trim($parrafo);
            if ($parrafo === '') {
                $lineas[] = ['text' => '', 'bold' => false];
                continue;
            }
            $esTitulo = (bool) preg_match('/^(Artículo|RESOLUCIÓN|RESUELVE|CONSIDERANDO|Parágrafo|CIRCULAR|CONTRATO|\d+\.\s+[A-ZÁÉÍÓÚÑ]|PASO\s+\d+|FORMA MÁS|COPIAR:)/u', $parrafo);
            foreach (self::partir($parrafo, $esTitulo ? 78 : 92) as $trozo) {
                $lineas[] = ['text' => $trozo, 'bold' => $esTitulo];
            }
        }

        return $lineas;
    }

    /**
     * @param  list<array{text: string, bold: bool}>  $lineas
     */
    private static function contenidoPagina(array $lineas, int $numero, int $total): string
    {
        $ops = "0.067 0.227 0.431 rg\n50 742 512 28 re f\n1 1 1 rg\nBT\n/F2 12 Tf\n62 752 Td\n".self::s('LEGALCLOUD AI')." Tj\nET\n";
        $ops .= "0.067 0.227 0.431 rg\n50 48 512 1.2 re f\n";
        $ops .= "0 0 0 rg\nBT\n";
        $y = 710;
        $primera = true;
        foreach ($lineas as $linea) {
            $fuente = $linea['bold'] ? '/F2' : '/F1';
            $tam = $linea['bold'] ? 12 : 10;
            $ops .= "{$fuente} {$tam} Tf\n";
            if ($primera) {
                $ops .= "62 {$y} Td\n";
                $primera = false;
            } else {
                $ops .= "0 -15 Td\n";
            }
            $ops .= self::s($linea['text'] === '' ? ' ' : $linea['text'])." Tj\n";
        }
        $ops .= "ET\n0.25 0.35 0.55 rg\nBT\n/F1 8 Tf\n260 32 Td\n".self::s("Página {$numero} de {$total}")." Tj\nET\n";

        return $ops;
    }

    /**
     * @return list<string>
     */
    private static function partir(string $texto, int $ancho): array
    {
        $palabras = preg_split('/\s+/', $texto) ?: [];
        $filas = [];
        $actual = '';
        foreach ($palabras as $palabra) {
            $prueba = $actual === '' ? $palabra : $actual.' '.$palabra;
            if (mb_strlen($prueba) > $ancho) {
                if ($actual !== '') {
                    $filas[] = $actual;
                }
                $actual = $palabra;
            } else {
                $actual = $prueba;
            }
        }
        if ($actual !== '') {
            $filas[] = $actual;
        }

        return $filas ?: [$texto];
    }

    private static function s(string $utf8): string
    {
        $win = @iconv('UTF-8', 'Windows-1252//TRANSLIT', $utf8);
        if ($win === false) {
            $win = utf8_decode($utf8);
        }
        $out = '';
        $len = strlen($win);
        for ($i = 0; $i < $len; $i++) {
            $c = $win[$i];
            $o = ord($c);
            if ($c === '(' || $c === ')' || $c === '\\') {
                $out .= '\\'.$c;
            } elseif ($o < 32 || $o > 126) {
                $out .= sprintf('\\%03o', $o);
            } else {
                $out .= $c;
            }
        }

        return '('.$out.')';
    }

    private static function desescapar(string $pdf): string
    {
        $pdf = str_replace(['\\(', '\\)', '\\\\'], ['(', ')', '\\'], $pdf);
        $pdf = preg_replace_callback('/\\\\([0-7]{3})/', fn (array $m) => chr(octdec($m[1])), $pdf) ?? $pdf;
        $win = $pdf;
        $utf = @iconv('Windows-1252', 'UTF-8//TRANSLIT', $win);

        return $utf === false ? $win : $utf;
    }
}
