@extends('layouts.matricula')

@section('title', 'Cursos · Lista')

@php
    $abrir = (bool) $editandoGrupo || $errors->hasAny(['codigo', 'nombre_curso', 'codigo_grupo', 'docente_id', 'dias', 'inicio', 'fin', 'aula', 'cupo', 'facultad_curso']);
@endphp

@section('content')
    @include('matricula.listas._nav')

    <article class="card">
        <div class="list-head">
            <h1>Cursos</h1>
            <span class="muted">{{ $grupos->count() }}</span>
        </div>
        <div class="table-wrap">
            <table class="sheet">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Asignatura</th>
                        <th>Grupo</th>
                        <th>Docente</th>
                        <th>Horario</th>
                        <th>Cupo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($grupos as $grupo)
                        <tr>
                            <td>{{ $grupo->asignatura->codigo }}</td>
                            <td>{{ $grupo->asignatura->nombre }}</td>
                            <td>{{ $grupo->codigo_grupo }}</td>
                            <td>{{ $grupo->profesor->nombre ?? $grupo->docente }}</td>
                            <td>{{ $grupo->horario() }}</td>
                            <td>{{ $grupo->tomados }}/{{ $grupo->cupo }}</td>
                            <td><a class="tiny" href="{{ route('matricula.listas.ver', ['lista' => 'cursos', 'editar' => $grupo->id]) }}#form-lista">Editar</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="muted">Aún no hay cursos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <details id="form-lista" class="adder" @if ($abrir) open @endif>
            <summary>{{ $editandoGrupo ? 'Editar curso' : 'Agregar curso' }}</summary>
            <form method="POST" action="{{ $editandoGrupo ? route('matricula.cursos.actualizar', $editandoGrupo) : route('matricula.cursos.guardar') }}" novalidate>
                @csrf
                @if ($editandoGrupo)
                    @method('PUT')
                @endif
                <div class="grid-2">
                    <div class="field">
                        <label for="codigo">Código</label>
                        <input id="codigo" name="codigo" value="{{ old('codigo', $editandoGrupo->asignatura->codigo ?? '') }}" required>
                    </div>
                    <div class="field">
                        <label for="codigo_grupo">Grupo</label>
                        <input id="codigo_grupo" name="codigo_grupo" value="{{ old('codigo_grupo', $editandoGrupo->codigo_grupo ?? '') }}" placeholder="SIS401-A" required>
                        @error('codigo_grupo')<p class="err">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="field">
                    <label for="nombre_curso">Nombre</label>
                    <input id="nombre_curso" name="nombre_curso" value="{{ old('nombre_curso', $editandoGrupo->asignatura->nombre ?? '') }}" required>
                </div>
                <div class="grid-2">
                    <div class="field">
                        <label for="creditos">Créditos</label>
                        <input id="creditos" name="creditos" type="number" min="1" max="8" value="{{ old('creditos', $editandoGrupo->asignatura->creditos ?? 3) }}" required>
                    </div>
                    <div class="field">
                        <label for="facultad_curso">Facultad</label>
                        <select id="facultad_curso" name="facultad_curso">
                            @foreach (['Ingeniería', 'Ciencias', 'Humanidades'] as $opcion)
                                <option value="{{ $opcion }}" @selected(old('facultad_curso', $editandoGrupo->asignatura->facultad ?? '') === $opcion)>{{ $opcion }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="field">
                    <label for="prerrequisito">Prerrequisito</label>
                    <input id="prerrequisito" name="prerrequisito" value="{{ old('prerrequisito', $editandoGrupo->asignatura->prerrequisito ?? 'Ninguno') }}">
                </div>
                <div class="field">
                    <label for="docente_id">Docente que toma el curso</label>
                    <select id="docente_id" name="docente_id" required @disabled($docentes->isEmpty())>
                        <option value="">{{ $docentes->isEmpty() ? 'Agrega un docente primero' : 'Selecciona' }}</option>
                        @foreach ($docentes as $docente)
                            <option value="{{ $docente->id }}" @selected((string) old('docente_id', $editandoGrupo->docente_id ?? '') === (string) $docente->id)>{{ $docente->nombre }}</option>
                        @endforeach
                    </select>
                    @error('docente_id')<p class="err">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <span class="lbl">Días</span>
                    <div class="days">
                        @foreach (['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'] as $dia)
                            <label>
                                <input type="checkbox" name="dias[]" value="{{ $dia }}" @checked(in_array($dia, old('dias', $editandoGrupo->dias ?? []), true))>
                                {{ $dia }}
                            </label>
                        @endforeach
                    </div>
                    @error('dias')<p class="err">{{ $message }}</p>@enderror
                </div>
                <div class="grid-2">
                    <div class="field">
                        <label for="inicio">Inicio</label>
                        <input id="inicio" name="inicio" type="time" value="{{ old('inicio', $editandoGrupo->inicio ?? '08:00') }}" required>
                    </div>
                    <div class="field">
                        <label for="fin">Fin</label>
                        <input id="fin" name="fin" type="time" value="{{ old('fin', $editandoGrupo->fin ?? '10:00') }}" required>
                        @error('fin')<p class="err">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="grid-2">
                    <div class="field">
                        <label for="aula">Aula</label>
                        <input id="aula" name="aula" value="{{ old('aula', $editandoGrupo->aula ?? '') }}" required>
                    </div>
                    <div class="field">
                        <label for="cupo">Cupo</label>
                        <input id="cupo" name="cupo" type="number" min="1" max="80" value="{{ old('cupo', $editandoGrupo->cupo ?? 30) }}" required>
                        @error('cupo')<p class="err">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="actions">
                    @if ($editandoGrupo)
                        <a class="btn btn-soft" href="{{ route('matricula.listas.ver', 'cursos') }}">Cancelar edición</a>
                    @endif
                    <button class="btn btn-ghost" type="submit" @disabled($docentes->isEmpty())>{{ $editandoGrupo ? 'Guardar cambios' : 'Agregar curso' }}</button>
                </div>
            </form>
        </details>
    </article>
@endsection
