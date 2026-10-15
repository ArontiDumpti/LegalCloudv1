@extends('layouts.matricula')

@section('title', 'Docentes · Lista')

@php
    $abrir = (bool) $editandoDocente || $errors->hasAny(['nombre_docente', 'facultad_doc']);
@endphp

@section('content')
    @include('matricula.listas._nav')

    <article class="card">
        <div class="list-head">
            <h1>Docentes</h1>
            <span class="muted">{{ $docentes->count() }}</span>
        </div>
        <div class="table-wrap">
            <table class="sheet">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Facultad</th>
                        <th>Cursos</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($docentes as $docente)
                        <tr>
                            <td>{{ $docente->nombre }}</td>
                            <td>{{ $docente->facultad }}</td>
                            <td>{{ $docente->grupos_count }}</td>
                            <td><a class="tiny" href="{{ route('matricula.listas.ver', ['lista' => 'docentes', 'editar' => $docente->id]) }}#form-lista">Editar</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="muted">Aún no hay docentes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <details id="form-lista" class="adder" @if ($abrir) open @endif>
            <summary>{{ $editandoDocente ? 'Editar docente' : 'Agregar docente' }}</summary>
            <form method="POST" action="{{ $editandoDocente ? route('matricula.docentes.actualizar', $editandoDocente) : route('matricula.docentes.guardar') }}" novalidate>
                @csrf
                @if ($editandoDocente)
                    @method('PUT')
                @endif
                <div class="grid-2">
                    <div class="field">
                        <label for="nombre_docente">Nombre</label>
                        <input id="nombre_docente" name="nombre_docente" value="{{ old('nombre_docente', $editandoDocente->nombre ?? '') }}" required>
                        @error('nombre_docente')<p class="err">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label for="facultad_doc">Facultad</label>
                        <select id="facultad_doc" name="facultad_doc" required>
                            @foreach (['Ingeniería', 'Ciencias', 'Humanidades'] as $opcion)
                                <option value="{{ $opcion }}" @selected(old('facultad_doc', $editandoDocente->facultad ?? '') === $opcion)>{{ $opcion }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="actions">
                    @if ($editandoDocente)
                        <a class="btn btn-soft" href="{{ route('matricula.listas.ver', 'docentes') }}">Cancelar edición</a>
                    @endif
                    <button class="btn btn-ghost" type="submit">{{ $editandoDocente ? 'Guardar cambios' : 'Agregar docente' }}</button>
                </div>
            </form>
        </details>
    </article>
@endsection
