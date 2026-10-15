<?php

namespace App\Http\Controllers;

use App\Models\Asignatura;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\GrupoAsignatura;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MatriculaCatalogoController extends Controller
{
    public function menu(): View
    {
        return view('matricula.listas.menu', [
            'estudiante' => $this->estudianteSesion(),
            'totales' => [
                'estudiantes' => Estudiante::query()->count(),
                'cursos' => GrupoAsignatura::query()->count(),
                'docentes' => Docente::query()->count(),
            ],
            'paso' => 1,
        ]);
    }

    public function ver(Request $request, string $lista): View
    {
        if (! in_array($lista, ['estudiantes', 'cursos', 'docentes'], true)) {
            abort(404);
        }

        return view('matricula.listas.'.$lista, [
            'estudiante' => $this->estudianteSesion(),
            'lista' => $lista,
            'estudiantes' => $lista === 'estudiantes'
                ? Estudiante::query()->orderBy('apellidos')->orderBy('nombre')->get()
                : collect(),
            'grupos' => $lista === 'cursos'
                ? GrupoAsignatura::query()->with(['asignatura', 'profesor'])->orderBy('codigo_grupo')->get()
                : collect(),
            'docentes' => Docente::query()->withCount('grupos')->orderBy('nombre')->get(),
            'editandoEstudiante' => $lista === 'estudiantes'
                ? Estudiante::query()->find($request->integer('editar'))
                : null,
            'editandoGrupo' => $lista === 'cursos'
                ? GrupoAsignatura::query()->with('asignatura')->find($request->integer('editar'))
                : null,
            'editandoDocente' => $lista === 'docentes'
                ? Docente::query()->find($request->integer('editar'))
                : null,
            'paso' => 1,
        ]);
    }
    public function guardarEstudiante(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'est_matricula' => ['required', 'string', 'max:20', 'unique:estudiantes,matricula'],
            'est_pin' => ['required', 'string', 'min:4', 'max:20'],
            'nombre' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:150'],
            'programa' => ['required', 'string', 'max:150'],
            'facultad' => ['required', 'string', 'max:100'],
            'semestre' => ['required', 'integer', 'min:1', 'max:12'],
        ], [
            'est_matricula.unique' => 'Esa matrícula ya está registrada.',
            'est_matricula.required' => 'La matrícula es obligatoria.',
            'est_pin.required' => 'El PIN es obligatorio.',
        ]);

        $estudiante = Estudiante::query()->create([
            'matricula' => $datos['est_matricula'],
            'pin' => $datos['est_pin'],
            'nombre' => $datos['nombre'],
            'apellidos' => $datos['apellidos'],
            'programa' => $datos['programa'],
            'facultad' => $datos['facultad'],
            'semestre' => $datos['semestre'],
            'promedio' => 0,
            'estado' => 'Regular',
            'periodo' => '2026-2',
            'creditos_aprobados' => 0,
            'creditos_max' => 18,
        ]);

        foreach ([
            'Paz y salvo financiero',
            'Encuesta docente del periodo anterior',
            'Carnet institucional vigente',
            'Prerrequisitos académicos del semestre',
            'Actualización de datos de contacto',
        ] as $nombre) {
            $estudiante->requisitos()->create([
                'nombre' => $nombre,
                'cumple' => true,
            ]);
        }

        return $this->volverAListas('estudiantes', 'Estudiante '.$estudiante->nombreCompleto().' agregado.');
    }

    public function actualizarEstudiante(Request $request, Estudiante $estudiante): RedirectResponse
    {
        $datos = $request->validate([
            'est_matricula' => ['required', 'string', 'max:20', 'unique:estudiantes,matricula,'.$estudiante->id],
            'est_pin' => ['nullable', 'string', 'min:4', 'max:20'],
            'nombre' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:150'],
            'programa' => ['required', 'string', 'max:150'],
            'facultad' => ['required', 'string', 'max:100'],
            'semestre' => ['required', 'integer', 'min:1', 'max:12'],
        ], [
            'est_matricula.unique' => 'Esa matrícula ya está registrada.',
        ]);

        $estudiante->fill([
            'matricula' => $datos['est_matricula'],
            'nombre' => $datos['nombre'],
            'apellidos' => $datos['apellidos'],
            'programa' => $datos['programa'],
            'facultad' => $datos['facultad'],
            'semestre' => $datos['semestre'],
        ]);

        if (filled($datos['est_pin'] ?? null)) {
            $estudiante->pin = $datos['est_pin'];
        }

        $estudiante->save();

        return $this->volverAListas('estudiantes', 'Estudiante '.$estudiante->nombreCompleto().' actualizado.');
    }

    public function guardarDocente(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre_docente' => ['required', 'string', 'max:150'],
            'facultad_doc' => ['required', 'string', 'max:100'],
        ]);

        Docente::query()->create([
            'nombre' => $datos['nombre_docente'],
            'facultad' => $datos['facultad_doc'],
        ]);

        return $this->volverAListas('docentes', 'Docente '.$datos['nombre_docente'].' agregado.');
    }

    public function actualizarDocente(Request $request, Docente $docente): RedirectResponse
    {
        $datos = $request->validate([
            'nombre_docente' => ['required', 'string', 'max:150'],
            'facultad_doc' => ['required', 'string', 'max:100'],
        ]);

        $docente->update([
            'nombre' => $datos['nombre_docente'],
            'facultad' => $datos['facultad_doc'],
        ]);

        GrupoAsignatura::query()
            ->where('docente_id', $docente->id)
            ->update(['docente' => $docente->nombre]);

        return $this->volverAListas('docentes', 'Docente '.$docente->nombre.' actualizado.');
    }

    public function guardarCurso(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:20'],
            'nombre_curso' => ['required', 'string', 'max:150'],
            'creditos' => ['required', 'integer', 'min:1', 'max:8'],
            'facultad_curso' => ['required', 'string', 'max:100'],
            'prerrequisito' => ['nullable', 'string', 'max:150'],
            'codigo_grupo' => ['required', 'string', 'max:30', 'unique:grupos_asignatura,codigo_grupo'],
            'docente_id' => ['required', 'exists:docentes,id'],
            'dias' => ['required', 'array', 'min:1'],
            'dias.*' => ['in:Lun,Mar,Mie,Jue,Vie,Sab'],
            'inicio' => ['required'],
            'fin' => ['required'],
            'aula' => ['required', 'string', 'max:40'],
            'cupo' => ['required', 'integer', 'min:1', 'max:80'],
        ], [
            'codigo_grupo.unique' => 'Ese grupo ya existe.',
            'docente_id.required' => 'Elige el docente que tomará el curso.',
            'dias.required' => 'Marca al menos un día.',
            'fin.after' => 'La hora de fin debe ser posterior al inicio.',
        ]);

        $docente = Docente::query()->findOrFail($datos['docente_id']);
        $inicio = substr((string) $datos['inicio'], 0, 5);
        $fin = substr((string) $datos['fin'], 0, 5);

        if ($inicio >= $fin) {
            return back()->withErrors(['fin' => 'La hora de fin debe ser posterior al inicio.'])->withInput();
        }

        $asignatura = Asignatura::query()->updateOrCreate(
            ['codigo' => strtoupper($datos['codigo'])],
            [
                'nombre' => $datos['nombre_curso'],
                'creditos' => $datos['creditos'],
                'facultad' => $datos['facultad_curso'],
                'prerrequisito' => $datos['prerrequisito'] ?: 'Ninguno',
            ]
        );

        GrupoAsignatura::query()->create([
            'asignatura_id' => $asignatura->id,
            'codigo_grupo' => strtoupper($datos['codigo_grupo']),
            'docente_id' => $docente->id,
            'docente' => $docente->nombre,
            'dias' => $datos['dias'],
            'inicio' => $inicio,
            'fin' => $fin,
            'aula' => $datos['aula'],
            'cupo' => $datos['cupo'],
            'tomados' => 0,
        ]);

        return $this->volverAListas('cursos', 'Curso '.$asignatura->codigo.' asignado a '.$docente->nombre.'.');
    }

    public function actualizarCurso(Request $request, GrupoAsignatura $grupo): RedirectResponse
    {
        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:20'],
            'nombre_curso' => ['required', 'string', 'max:150'],
            'creditos' => ['required', 'integer', 'min:1', 'max:8'],
            'facultad_curso' => ['required', 'string', 'max:100'],
            'prerrequisito' => ['nullable', 'string', 'max:150'],
            'codigo_grupo' => ['required', 'string', 'max:30', 'unique:grupos_asignatura,codigo_grupo,'.$grupo->id],
            'docente_id' => ['required', 'exists:docentes,id'],
            'dias' => ['required', 'array', 'min:1'],
            'dias.*' => ['in:Lun,Mar,Mie,Jue,Vie,Sab'],
            'inicio' => ['required'],
            'fin' => ['required'],
            'aula' => ['required', 'string', 'max:40'],
            'cupo' => ['required', 'integer', 'min:1', 'max:80'],
        ], [
            'codigo_grupo.unique' => 'Ese grupo ya existe.',
            'docente_id.required' => 'Elige el docente que tomará el curso.',
            'dias.required' => 'Marca al menos un día.',
        ]);

        $docente = Docente::query()->findOrFail($datos['docente_id']);
        $inicio = substr((string) $datos['inicio'], 0, 5);
        $fin = substr((string) $datos['fin'], 0, 5);

        if ($inicio >= $fin) {
            return back()->withErrors(['fin' => 'La hora de fin debe ser posterior al inicio.'])->withInput();
        }

        if ((int) $datos['cupo'] < $grupo->tomados) {
            return back()->withErrors(['cupo' => 'El cupo no puede ser menor a los cupos ya tomados.'])->withInput();
        }

        $grupo->asignatura->update([
            'codigo' => strtoupper($datos['codigo']),
            'nombre' => $datos['nombre_curso'],
            'creditos' => $datos['creditos'],
            'facultad' => $datos['facultad_curso'],
            'prerrequisito' => $datos['prerrequisito'] ?: 'Ninguno',
        ]);

        $grupo->update([
            'codigo_grupo' => strtoupper($datos['codigo_grupo']),
            'docente_id' => $docente->id,
            'docente' => $docente->nombre,
            'dias' => $datos['dias'],
            'inicio' => $inicio,
            'fin' => $fin,
            'aula' => $datos['aula'],
            'cupo' => $datos['cupo'],
        ]);

        return $this->volverAListas('cursos', 'Curso '.$grupo->codigo_grupo.' actualizado.');
    }

    private function volverAListas(string $lista, string $mensaje): RedirectResponse
    {
        return redirect()->route('matricula.listas.ver', $lista)->with('ok', $mensaje);
    }

    private function estudianteSesion(): ?Estudiante
    {
        $id = session('estudiante_id');

        return $id ? Estudiante::query()->find($id) : null;
    }
}
