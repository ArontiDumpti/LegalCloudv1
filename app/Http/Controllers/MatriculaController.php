<?php

namespace App\Http\Controllers;

use App\Models\Asignatura;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\GrupoAsignatura;
use App\Models\Matricula;
use App\Models\Preseleccion;
use App\Services\MatriculaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class MatriculaController extends Controller
{
    public function __construct(private MatriculaService $servicio) {}

    public function inicio(): View
    {
        return view('matricula.inicio', [
            'estudiante' => $this->estudianteSesion(),
            'paso' => 1,
        ]);
    }

    public function entrar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'matricula' => ['required', 'string', 'max:20'],
            'pin' => ['required', 'string', 'max:20'],
        ], [
            'matricula.required' => 'La matrícula es obligatoria.',
            'pin.required' => 'El PIN es obligatorio.',
        ]);

        $estudiante = Estudiante::query()->where('matricula', $datos['matricula'])->first();

        if (! $estudiante || ! Hash::check($datos['pin'], $estudiante->pin)) {
            return back()->withErrors([
                'matricula' => 'Matrícula o PIN incorrectos.',
            ])->onlyInput('matricula');
        }

        $request->session()->put('estudiante_id', $estudiante->id);

        return redirect()->route('matricula.inicio');
    }

    public function salir(Request $request): RedirectResponse
    {
        $request->session()->forget('estudiante_id');

        return redirect()->route('matricula.inicio');
    }

    public function cancelar(Request $request): RedirectResponse
    {
        $estudiante = $this->estudianteSesion();
        if (! $estudiante) {
            return redirect()->route('matricula.inicio');
        }

        DB::transaction(function () use ($estudiante) {
            $matricula = Matricula::query()
                ->with('cursos')
                ->where('estudiante_id', $estudiante->id)
                ->where('periodo', $estudiante->periodo)
                ->latest()
                ->first();

            if ($matricula) {
                foreach ($matricula->cursos as $curso) {
                    if ($curso->grupo_id) {
                        GrupoAsignatura::query()
                            ->whereKey($curso->grupo_id)
                            ->where('tomados', '>', 0)
                            ->decrement('tomados');
                    }
                }
                $matricula->delete();
            }

            $estudiante->preselecciones()->delete();
        });

        return redirect()->route('matricula.oferta')->with('ok', 'La matrícula quedó cancelada. Puedes elegir de nuevo.');
    }

    public function oferta(Request $request): View|RedirectResponse
    {
        $estudiante = $this->estudianteSesion();
        if (! $estudiante) {
            return redirect()->route('matricula.inicio');
        }
        if ($this->matriculaPeriodo($estudiante)) {
            return redirect()->route('matricula.comprobante');
        }

        $busqueda = trim((string) $request->query('q', ''));
        $facultad = trim((string) $request->query('facultad', ''));

        $asignaturas = Asignatura::query()
            ->with('grupos.profesor')
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($query) use ($busqueda) {
                    $query->where('codigo', 'like', "%{$busqueda}%")
                        ->orWhere('nombre', 'like', "%{$busqueda}%")
                        ->orWhereHas('grupos', fn ($grupos) => $grupos->where('docente', 'like', "%{$busqueda}%"));
                });
            })
            ->when($facultad !== '', fn ($query) => $query->where('facultad', $facultad))
            ->orderBy('codigo')
            ->get();

        $seleccion = $this->servicio->gruposSeleccionados($estudiante);
        $ids = $seleccion->pluck('id');

        return view('matricula.oferta', [
            'estudiante' => $estudiante,
            'asignaturas' => $asignaturas,
            'seleccion' => $seleccion,
            'idsSeleccion' => $ids,
            'busqueda' => $busqueda,
            'facultad' => $facultad,
            'creditos' => $this->servicio->creditos($seleccion),
            'paso' => 2,
        ]);
    }

    public function seleccionar(Request $request): RedirectResponse
    {
        $estudiante = $this->estudianteSesion();
        if (! $estudiante) {
            return redirect()->route('matricula.inicio');
        }
        if ($this->matriculaPeriodo($estudiante)) {
            return redirect()->route('matricula.comprobante');
        }

        $grupo = GrupoAsignatura::query()->with('asignatura')->findOrFail($request->integer('grupo_id'));

        if ($grupo->estaLleno()) {
            return back()->withErrors(['oferta' => 'Ese grupo no tiene cupo.']);
        }

        Preseleccion::query()
            ->where('estudiante_id', $estudiante->id)
            ->whereHas('grupo', fn ($query) => $query->where('asignatura_id', $grupo->asignatura_id))
            ->delete();

        Preseleccion::query()->create([
            'estudiante_id' => $estudiante->id,
            'grupo_id' => $grupo->id,
        ]);

        return back();
    }

    public function quitar(Request $request): RedirectResponse
    {
        $estudiante = $this->estudianteSesion();
        if (! $estudiante) {
            return redirect()->route('matricula.inicio');
        }

        Preseleccion::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('grupo_id', $request->integer('grupo_id'))
            ->delete();

        return back();
    }

    public function prematricula(): View|RedirectResponse
    {
        $estudiante = $this->estudianteSesion();
        if (! $estudiante) {
            return redirect()->route('matricula.inicio');
        }
        if ($this->matriculaPeriodo($estudiante)) {
            return redirect()->route('matricula.comprobante');
        }

        $seleccion = $this->servicio->gruposSeleccionados($estudiante);
        if ($seleccion->isEmpty()) {
            return redirect()->route('matricula.oferta');
        }

        $cruces = $this->servicio->cruces($seleccion);
        $creditos = $this->servicio->creditos($seleccion);
        $excede = $creditos > $estudiante->creditos_max;
        $puedeConfirmar = $cruces === [] && ! $excede;

        return view('matricula.prematricula', [
            'estudiante' => $estudiante,
            'seleccion' => $seleccion,
            'cruces' => $cruces,
            'creditos' => $creditos,
            'excede' => $excede,
            'puedeConfirmar' => $puedeConfirmar,
            'horario' => $this->mapaHorario($seleccion),
            'paso' => 3,
        ]);
    }

    public function confirmar(): RedirectResponse
    {
        $estudiante = $this->estudianteSesion();
        if (! $estudiante) {
            return redirect()->route('matricula.inicio');
        }

        $existente = Matricula::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('periodo', $estudiante->periodo)
            ->first();

        if ($existente) {
            return redirect()->route('matricula.comprobante');
        }

        $seleccion = $this->servicio->gruposSeleccionados($estudiante);
        $cruces = $this->servicio->cruces($seleccion);
        $creditos = $this->servicio->creditos($seleccion);

        if ($seleccion->isEmpty() || $cruces !== [] || $creditos > $estudiante->creditos_max) {
            return redirect()->route('matricula.prematricula');
        }

        DB::transaction(function () use ($estudiante, $seleccion, $creditos) {
            foreach ($seleccion as $grupo) {
                $grupo->refresh();
                if ($grupo->estaLleno()) {
                    abort(409, 'Un grupo se quedó sin cupo.');
                }
                $grupo->increment('tomados');
            }

            $matricula = Matricula::query()->create([
                'estudiante_id' => $estudiante->id,
                'folio' => 'AUR-'.$estudiante->periodo.'-'.substr((string) time(), -6),
                'periodo' => $estudiante->periodo,
                'creditos' => $creditos,
                'confirmada_at' => now(),
            ]);

            foreach ($seleccion as $grupo) {
                $matricula->cursos()->create([
                    'grupo_id' => $grupo->id,
                    'codigo' => $grupo->asignatura->codigo,
                    'nombre' => $grupo->asignatura->nombre,
                    'codigo_grupo' => $grupo->codigo_grupo,
                    'docente' => $grupo->profesor->nombre ?? $grupo->docente,
                    'dias' => $grupo->dias,
                    'inicio' => $grupo->inicio,
                    'fin' => $grupo->fin,
                    'aula' => $grupo->aula,
                    'creditos' => $grupo->asignatura->creditos,
                ]);
            }

            $estudiante->preselecciones()->delete();
        });

        return redirect()->route('matricula.comprobante');
    }

    public function comprobante(): View|RedirectResponse
    {
        $estudiante = $this->estudianteSesion();
        if (! $estudiante) {
            return redirect()->route('matricula.inicio');
        }

        $matricula = Matricula::query()
            ->with('cursos')
            ->where('estudiante_id', $estudiante->id)
            ->where('periodo', $estudiante->periodo)
            ->latest()
            ->first();

        if (! $matricula) {
            return redirect()->route('matricula.prematricula');
        }

        return view('matricula.comprobante', [
            'estudiante' => $estudiante,
            'matricula' => $matricula,
            'paso' => 4,
        ]);
    }

    private function estudianteSesion(): ?Estudiante
    {
        $id = session('estudiante_id');
        if (! $id) {
            return null;
        }

        return Estudiante::query()->with('requisitos')->find($id);
    }

    private function matriculaPeriodo(Estudiante $estudiante): ?Matricula
    {
        return Matricula::query()
            ->where('estudiante_id', $estudiante->id)
            ->where('periodo', $estudiante->periodo)
            ->latest()
            ->first();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, GrupoAsignatura>  $seleccion
     * @return array{dias: list<string>, horas: list<string>, celdas: array<string, array<string, GrupoAsignatura|null>>}
     */
    private function mapaHorario($seleccion): array
    {
        $dias = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'];
        $horas = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00'];
        $idsCruce = [];
        foreach ($this->servicio->cruces($seleccion) as [$a, $b]) {
            $idsCruce[$a->id] = true;
            $idsCruce[$b->id] = true;
        }

        $celdas = [];
        foreach ($horas as $hora) {
            foreach ($dias as $dia) {
                $hit = $seleccion->first(function (GrupoAsignatura $grupo) use ($dia, $hora) {
                    return in_array($dia, $grupo->dias, true)
                        && $this->aMinutos($grupo->inicio) <= $this->aMinutos($hora)
                        && $this->aMinutos($hora) < $this->aMinutos($grupo->fin);
                });
                $celdas[$hora][$dia] = $hit;
            }
        }

        return [
            'dias' => $dias,
            'horas' => $horas,
            'celdas' => $celdas,
            'cruces' => $idsCruce,
        ];
    }

    private function aMinutos(string $hora): int
    {
        [$h, $m] = array_map('intval', explode(':', $hora));

        return ($h * 60) + $m;
    }
}
