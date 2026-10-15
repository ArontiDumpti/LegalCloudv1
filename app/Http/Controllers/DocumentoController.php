<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Documento;
use App\Models\DocumentoVersion;
use App\Models\Expediente;
use App\Support\AuditoriaLogger;
use App\Support\PdfSimple;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentoController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Documento::with(['expediente', 'categoria', 'autor'])->latest();

        if ($busqueda = $request->string('q')->toString()) {
            $query->where(function ($builder) use ($busqueda) {
                $builder->where('codigo', 'like', "%{$busqueda}%")
                    ->orWhere('titulo', 'like', "%{$busqueda}%")
                    ->orWhere('contenido_ocr', 'like', "%{$busqueda}%");
            });
        }

        return Inertia::render('documentos/index', [
            'documentos' => $query->get()->map(fn (Documento $documento) => $this->presentar($documento)),
            'expedientes' => Expediente::orderBy('codigo')->get(['id', 'codigo', 'titulo']),
            'categorias' => Categoria::orderBy('nombre')->get(),
            'filtros' => $request->only(['q']),
            'ejemplo_pdf' => asset('ejemplos/RES-2024-0187-reglamento-tramites.pdf'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'expediente_id' => ['required', 'exists:expedientes,id'],
            'categoria_id' => ['nullable', 'exists:categorias,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'string', 'max:50'],
            'descripcion' => ['nullable', 'string'],
            'archivo' => ['required', 'file', 'mimes:pdf,txt', 'max:5120'],
        ]);

        $archivo = $request->file('archivo');
        $binario = file_get_contents($archivo->getRealPath()) ?: '';
        $texto = PdfSimple::extraer($binario);
        $siguiente = Documento::count() + 1;
        $codigo = 'DOC-'.now()->year.'-'.str_pad((string) $siguiente, 4, '0', STR_PAD_LEFT);
        $extension = strtolower($archivo->getClientOriginalExtension() ?: 'pdf');
        $nombreArchivo = Str::slug($codigo).'.'.$extension;
        $ruta = 'documentos/'.$nombreArchivo;

        Storage::disk('public')->put($ruta, $binario);

        $procesado = $texto !== '';

        $documento = Documento::create([
            'expediente_id' => $datos['expediente_id'],
            'categoria_id' => $datos['categoria_id'] ?? null,
            'codigo' => $codigo,
            'titulo' => $datos['titulo'],
            'tipo' => $datos['tipo'],
            'descripcion' => $datos['descripcion'] ?? null,
            'archivo_nombre' => $archivo->getClientOriginalName(),
            'archivo_ruta' => $ruta,
            'mime' => $archivo->getMimeType() ?: 'application/pdf',
            'tamano' => strlen($binario),
            'version_actual' => 1,
            'ocr_estado' => $procesado ? 'procesado' : 'pendiente',
            'contenido_ocr' => $procesado ? $texto : null,
            'embedding_estado' => $procesado ? 'indexado' : 'pendiente',
            'subido_por' => $request->user()->id,
        ]);

        DocumentoVersion::create([
            'documento_id' => $documento->id,
            'numero' => 1,
            'resumen_cambios' => 'Carga de PDF/texto.',
            'contenido' => $texto !== '' ? $texto : null,
            'subido_por' => $request->user()->id,
        ]);

        AuditoriaLogger::registrar('carga_documento', 'documentos', "Carga de {$documento->codigo} ({$archivo->getClientOriginalName()})");

        $mensaje = $procesado
            ? 'PDF cargado. El texto se extrajo e indexó para búsqueda y el asistente.'
            : 'PDF cargado. El OCR quedó pendiente: proceselo en el módulo OCR.';

        return back()->with('success', $mensaje);
    }

    public function storeEjemplo(Request $request): RedirectResponse
    {
        $rutaOrigen = public_path('ejemplos/RES-2024-0187-reglamento-tramites.pdf');
        abort_unless(is_file($rutaOrigen), 404, 'No está el PDF de ejemplo.');

        $binario = file_get_contents($rutaOrigen) ?: '';
        $texto = PdfSimple::extraer($binario);
        $expediente = Expediente::query()->orderBy('id')->firstOrFail();
        $categoria = Categoria::query()->where('slug', 'reglamento')->first();
        $siguiente = Documento::count() + 1;
        $codigo = 'DOC-'.now()->year.'-'.str_pad((string) $siguiente, 4, '0', STR_PAD_LEFT);
        $nombreArchivo = Str::slug($codigo).'.pdf';
        $ruta = 'documentos/'.$nombreArchivo;

        Storage::disk('public')->put($ruta, $binario);

        $documento = Documento::create([
            'expediente_id' => $expediente->id,
            'categoria_id' => $categoria?->id,
            'codigo' => $codigo,
            'titulo' => 'Reglamento General de Trámites Administrativos (subido)',
            'tipo' => 'reglamento',
            'descripcion' => 'PDF de ejemplo cargado desde el botón de subida.',
            'archivo_nombre' => 'RES-2024-0187-reglamento-tramites.pdf',
            'archivo_ruta' => $ruta,
            'mime' => 'application/pdf',
            'tamano' => strlen($binario),
            'version_actual' => 1,
            'ocr_estado' => 'procesado',
            'contenido_ocr' => $texto,
            'embedding_estado' => 'indexado',
            'subido_por' => $request->user()->id,
        ]);

        DocumentoVersion::create([
            'documento_id' => $documento->id,
            'numero' => 1,
            'resumen_cambios' => 'Subida del PDF de ejemplo.',
            'contenido' => $texto,
            'subido_por' => $request->user()->id,
        ]);

        AuditoriaLogger::registrar('carga_documento', 'documentos', "Subida del PDF de ejemplo {$documento->codigo}");

        return redirect()->route('documentos.show', $documento)->with('success', 'El PDF de ejemplo se subió e indexó.');
    }

    public function show(Documento $documento): Response
    {
        $documento->load(['expediente', 'categoria', 'autor', 'versiones.autor']);

        return Inertia::render('documentos/show', [
            'documento' => $this->presentar($documento),
        ]);
    }

    public function archivo(Documento $documento): StreamedResponse
    {
        abort_unless($documento->archivo_ruta && Storage::disk('public')->exists($documento->archivo_ruta), 404);

        return Storage::disk('public')->response(
            $documento->archivo_ruta,
            $documento->archivo_nombre ?: basename($documento->archivo_ruta),
            [
                'Content-Type' => $documento->mime ?: 'application/pdf',
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function presentar(Documento $documento): array
    {
        $data = $documento->toArray();
        $data['archivo_url'] = $documento->archivo_ruta
            ? route('documentos.archivo', $documento)
            : null;

        return $data;
    }
}
