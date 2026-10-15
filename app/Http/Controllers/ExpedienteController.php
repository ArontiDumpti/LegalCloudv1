<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Expediente;
use App\Models\User;
use App\Support\AuditoriaLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExpedienteController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Expediente::with(['categoria', 'responsable', 'documentos'])->latest();

        if ($busqueda = $request->string('q')->toString()) {
            $query->where(function ($builder) use ($busqueda) {
                $builder->where('codigo', 'like', "%{$busqueda}%")
                    ->orWhere('titulo', 'like', "%{$busqueda}%");
            });
        }

        if ($estado = $request->string('estado')->toString()) {
            $query->where('estado', $estado);
        }

        return Inertia::render('expedientes/index', [
            'expedientes' => $query->get(),
            'filtros' => $request->only(['q', 'estado']),
            'categorias' => Categoria::orderBy('nombre')->get(),
            'responsables' => User::where('activo', true)->orderBy('name')->get(['id', 'name', 'role']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'estado' => ['required', 'in:abierto,en_revision,cerrado'],
            'prioridad' => ['required', 'in:baja,media,alta'],
            'categoria_id' => ['nullable', 'exists:categorias,id'],
            'responsable_id' => ['nullable', 'exists:users,id'],
        ]);

        $siguiente = Expediente::count() + 1;
        $expediente = Expediente::create([
            ...$datos,
            'codigo' => 'EXP-'.now()->year.'-'.str_pad((string) $siguiente, 4, '0', STR_PAD_LEFT),
            'creado_por' => $request->user()->id,
            'fecha_apertura' => now()->toDateString(),
        ]);

        AuditoriaLogger::registrar('crear_expediente', 'expedientes', "Alta de {$expediente->codigo}");

        return back()->with('success', 'Expediente registrado.');
    }

    public function show(Expediente $expediente): Response
    {
        $expediente->load(['categoria', 'responsable', 'creador', 'documentos.categoria', 'documentos.autor']);

        return Inertia::render('expedientes/show', [
            'expediente' => $expediente,
        ]);
    }
}
