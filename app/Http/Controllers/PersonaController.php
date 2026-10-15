<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonaRequest;
use App\Http\Requests\UpdatePersonaRequest;
use App\Models\Persona;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonaController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda = trim((string) $request->query('q', $request->query('buscar', '')));

        $personas = Persona::query()
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($query) use ($busqueda) {
                    $query->where('nombre', 'like', "%{$busqueda}%")
                        ->orWhere('apellidos', 'like', "%{$busqueda}%")
                        ->orWhere('email', 'like', "%{$busqueda}%")
                        ->orWhere('telefono', 'like', "%{$busqueda}%");
                });
            })
            ->latest()
            ->get();

        return view('personas.index', compact('personas', 'busqueda'));
    }

    public function create(): View
    {
        return view('personas.create');
    }

    public function store(StorePersonaRequest $request): RedirectResponse
    {
        $persona = Persona::create($request->safe()->except('accion'));

        if ($request->input('accion') === 'capacitacion') {
            return redirect()->route('capacitaciones.crear', $persona);
        }

        return redirect()
            ->route('personas.index')
            ->with('exito', 'La persona se agregó correctamente.');
    }

    public function edit(Persona $persona): View
    {
        return view('personas.edit', compact('persona'));
    }

    public function update(UpdatePersonaRequest $request, Persona $persona): RedirectResponse
    {
        $persona->update($request->validated());

        return redirect()
            ->route('personas.index')
            ->with('exito', 'Los datos se actualizaron correctamente.');
    }

    public function destroy(Persona $persona): RedirectResponse
    {
        $nombre = $persona->nombre.' '.$persona->apellidos;
        $persona->delete();

        return redirect()
            ->route('personas.index')
            ->with('exito', 'Se eliminó a '.$nombre.'.');
    }

    public function confirmacion(Persona $persona): View
    {
        return view('personas.confirmacion', compact('persona'));
    }
}
