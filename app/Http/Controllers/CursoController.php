<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CursoController extends Controller
{
    public function index(): View
    {
        $cursos = Curso::query()->latest()->get();

        return view('cursos.index', compact('cursos'));
    }

    public function create(): View
    {
        return view('cursos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'instructor' => ['required', 'string', 'max:150'],
            'duracion_horas' => ['required', 'integer', 'min:1', 'max:1000'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ], [
            'nombre.required' => 'El nombre del curso es obligatorio.',
            'instructor.required' => 'El instructor es obligatorio.',
            'duracion_horas.required' => 'La duración es obligatoria.',
            'duracion_horas.integer' => 'La duración debe ser un número de horas.',
            'duracion_horas.min' => 'La duración debe ser de al menos 1 hora.',
        ]);

        $curso = Curso::create($validated);

        return redirect()->route('cursos.confirmacion', $curso);
    }

    public function confirmacion(Curso $curso): View
    {
        return view('cursos.confirmacion', compact('curso'));
    }
}
