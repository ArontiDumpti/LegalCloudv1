@extends('layouts.matricula')

@section('title', 'Estudiantes · Lista')

@php
    $abrir = (bool) $editandoEstudiante || $errors->hasAny(['est_matricula', 'est_pin', 'nombre', 'apellidos', 'programa', 'facultad', 'semestre']);
@endphp

@section('content')
    @include('matricula.listas._nav')

    <article class="card">
        <div class="list-head">
            <h1>Estudiantes</h1>
            <span class="muted">{{ $estudiantes->count() }}</span>
        </div>
        <div class="table-wrap">
            <table class="sheet">
                <thead>
                    <tr>
                        <th>Matrícula</th>
                        <th>Nombre</th>
                        <th>Programa</th>
                        <th>Facultad</th>
                        <th>Semestre</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($estudiantes as $item)
                        <tr>
                            <td>{{ $item->matricula }}</td>
                            <td>{{ $item->nombreCompleto() }}</td>
                            <td>{{ $item->programa }}</td>
                            <td>{{ $item->facultad }}</td>
                            <td>{{ $item->semestre }}º</td>
                            <td><a class="tiny" href="{{ route('matricula.listas.ver', ['lista' => 'estudiantes', 'editar' => $item->id]) }}#form-lista">Editar</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="muted">Aún no hay estudiantes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <details id="form-lista" class="adder" @if ($abrir) open @endif>
            <summary>{{ $editandoEstudiante ? 'Editar estudiante' : 'Agregar estudiante' }}</summary>
            <form method="POST" action="{{ $editandoEstudiante ? route('matricula.estudiantes.actualizar', $editandoEstudiante) : route('matricula.estudiantes.guardar') }}" novalidate>
                @csrf
                @if ($editandoEstudiante)
                    @method('PUT')
                @endif
                <div class="grid-2">
                    <div class="field">
                        <label for="matricula_nueva">Número de matrícula</label>
                        <input id="matricula_nueva" name="est_matricula" value="{{ old('est_matricula', $editandoEstudiante->matricula ?? '') }}" required>
                        @error('est_matricula')<p class="err">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label for="pin_nuevo">PIN {{ $editandoEstudiante ? '(opcional)' : '' }}</label>
                        <input id="pin_nuevo" name="est_pin" type="password" @required(! $editandoEstudiante)>
                        @error('est_pin')<p class="err">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="grid-2">
                    <div class="field">
                        <label for="nombre">Nombre</label>
                        <input id="nombre" name="nombre" value="{{ old('nombre', $editandoEstudiante->nombre ?? '') }}" required>
                    </div>
                    <div class="field">
                        <label for="apellidos">Apellidos</label>
                        <input id="apellidos" name="apellidos" value="{{ old('apellidos', $editandoEstudiante->apellidos ?? '') }}" required>
                    </div>
                </div>
                <div class="field">
                    <label for="programa">Programa</label>
                    <input id="programa" name="programa" value="{{ old('programa', $editandoEstudiante->programa ?? '') }}" required>
                </div>
                <div class="grid-2">
                    <div class="field">
                        <label for="facultad_est">Facultad</label>
                        <select id="facultad_est" name="facultad" required>
                            @foreach (['Ingeniería', 'Ciencias', 'Humanidades'] as $opcion)
                                <option value="{{ $opcion }}" @selected(old('facultad', $editandoEstudiante->facultad ?? '') === $opcion)>{{ $opcion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="semestre">Semestre</label>
                        <input id="semestre" name="semestre" type="number" min="1" max="12" value="{{ old('semestre', $editandoEstudiante->semestre ?? 1) }}" required>
                    </div>
                </div>
                <div class="actions">
                    @if ($editandoEstudiante)
                        <a class="btn btn-soft" href="{{ route('matricula.listas.ver', 'estudiantes') }}">Cancelar edición</a>
                    @endif
                    <button class="btn btn-ghost" type="submit">{{ $editandoEstudiante ? 'Guardar cambios' : 'Agregar estudiante' }}</button>
                </div>
            </form>
        </details>
    </article>
@endsection
