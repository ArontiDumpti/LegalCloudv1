<?php

use App\Support\DocumentosDemo;
use App\Support\PdfSimple;
use Illuminate\Support\Facades\Storage;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$dir = public_path('ejemplos');
if (! is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$docs = [
    ['RES-2024-0187-reglamento-tramites.pdf', 'Reglamento General de Trámites Administrativos', DocumentosDemo::reglamentoTramites()],
    ['RES-2025-0042-directrices-plazos.pdf', 'Directrices complementarias sobre plazos y subsanación', DocumentosDemo::resolucionPlazos()],
    ['CIR-2026-0008-clasificacion-documentos.pdf', 'Circular de clasificación y carga de documentos', DocumentosDemo::circularClasificacion()],
    ['SOL-2026-0041-cooperativa-andes.pdf', 'Formulario de solicitud de licencia', DocumentosDemo::solicitudLicencia()],
    ['CT-2026-015-gestion-documental.pdf', 'Contrato de gestión documental', DocumentosDemo::contratoSoporte()],
];

foreach ($docs as $doc) {
    [$nombre, $titulo, $texto] = $doc;
    $pdf = PdfSimple::generar($titulo, $texto);
    file_put_contents($dir.DIRECTORY_SEPARATOR.$nombre, $pdf);
    Storage::disk('public')->put('documentos/'.$nombre, $pdf);
    $extraido = PdfSimple::extraer($pdf);
    echo $nombre.' bytes='.strlen($pdf).' extrae='.(str_contains($extraido, 'Artículo') || str_contains($extraido, 'CIRCULAR') || str_contains($extraido, 'Cláusula') || str_contains($extraido, 'FORMULARIO') ? 'OK' : 'NO').PHP_EOL;
}
