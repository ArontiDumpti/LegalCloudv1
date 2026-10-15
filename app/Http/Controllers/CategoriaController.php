<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Support\AuditoriaLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CategoriaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('categorias/index', [
            'categorias' => Categoria::withCount(['expedientes', 'documentos'])->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        Categoria::create([
            'nombre' => $datos['nombre'],
            'slug' => Str::slug($datos['nombre']),
            'descripcion' => $datos['descripcion'] ?? null,
            'color' => $datos['color'] ?? '#1D4ED8',
        ]);

        AuditoriaLogger::registrar('crear_categoria', 'categorias', "Alta de categoría {$datos['nombre']}");

        return back()->with('success', 'Categoría creada.');
    }
}
