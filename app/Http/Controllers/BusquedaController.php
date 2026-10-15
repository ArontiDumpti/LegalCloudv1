<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\Expediente;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BusquedaController extends Controller
{
    public function index(Request $request): Response
    {
        $q = $request->string('q')->toString();

        $documentos = collect();
        $expedientes = collect();

        if ($q !== '') {
            $documentos = Documento::with(['expediente', 'categoria'])
                ->where(function ($builder) use ($q) {
                    $builder->where('titulo', 'like', "%{$q}%")
                        ->orWhere('codigo', 'like', "%{$q}%")
                        ->orWhere('contenido_ocr', 'like', "%{$q}%");
                })
                ->limit(20)
                ->get();

            $expedientes = Expediente::with('categoria')
                ->where(function ($builder) use ($q) {
                    $builder->where('titulo', 'like', "%{$q}%")
                        ->orWhere('codigo', 'like', "%{$q}%")
                        ->orWhere('descripcion', 'like', "%{$q}%");
                })
                ->limit(10)
                ->get();
        }

        return Inertia::render('busqueda/index', [
            'q' => $q,
            'documentos' => $documentos,
            'expedientes' => $expedientes,
        ]);
    }
}
