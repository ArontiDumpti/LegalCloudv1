<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCapacitacionRequest;
use App\Http\Requests\UpdateCapacitacionRequest;
use App\Models\Capacitacion;
use App\Models\Persona;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CapacitacionController extends Controller
{
    public function index(Persona $persona): View
    {
        $capacitaciones = $persona->capacitaciones()->latest('fecha')->latest('id')->get();

        return view('capacitaciones.index', compact('persona', 'capacitaciones'));
    }

    public function create(Persona $persona): View
    {
        return view('capacitaciones.create', compact('persona'));
    }

    public function store(StoreCapacitacionRequest $request, Persona $persona): RedirectResponse
    {
        $persona->capacitaciones()->create($request->safe()->only(['capacitacion', 'fecha', 'duracion']));

        return redirect()
            ->route('capacitaciones.index', $persona)
            ->with('exito', 'La capacitación se agregó correctamente.');
    }

    public function edit(Persona $persona, Capacitacion $capacitacion): View
    {
        return view('capacitaciones.edit', compact('persona', 'capacitacion'));
    }

    public function update(UpdateCapacitacionRequest $request, Persona $persona, Capacitacion $capacitacion): RedirectResponse
    {
        $capacitacion->update($request->safe()->only(['capacitacion', 'fecha', 'duracion']));

        return redirect()
            ->route('capacitaciones.index', $persona)
            ->with('exito', 'La capacitación se actualizó correctamente.');
    }

    public function destroy(Persona $persona, Capacitacion $capacitacion): RedirectResponse
    {
        $capacitacion->delete();

        return redirect()
            ->route('capacitaciones.index', $persona)
            ->with('exito', 'La capacitación se eliminó correctamente.');
    }
}
