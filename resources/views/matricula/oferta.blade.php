@extends('layouts.matricula')

@section('title', 'Cursos · Matrícula')

@section('content')
    <div class="page-head">
        <span class="badge badge-hot">Paso 2</span>
        <h1>Elige tus cursos</h1>
    </div>

    @error('oferta')
        <div class="alert alert-bad">{{ $message }}</div>
    @enderror

    <form class="card" method="GET" action="{{ route('matricula.oferta') }}" style="margin-bottom:1rem">
        <div class="grid-2">
            <div class="field" style="margin:0">
                <label for="q">Buscar</label>
                <input id="q" name="q" type="search" value="{{ $busqueda }}" placeholder="Materia, código o docente">
            </div>
            <div class="field" style="margin:0">
                <label for="facultad">Facultad</label>
                <select id="facultad" name="facultad" onchange="this.form.submit()">
                    <option value="">Todas</option>
                    @foreach (['Ingeniería', 'Ciencias', 'Humanidades'] as $opcion)
                        <option value="{{ $opcion }}" @selected($facultad === $opcion)>{{ $opcion }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    <div class="grid-3">
        <div>
            @forelse ($asignaturas as $asignatura)
                <article class="course">
                    <div class="meta">
                        <span class="badge badge-tide">{{ $asignatura->codigo }}</span>
                        <span>{{ $asignatura->creditos }} créditos</span>
                        <span>Prerreq. {{ $asignatura->prerrequisito }}</span>
                    </div>
                    <h3>{{ $asignatura->nombre }}</h3>
                    @foreach ($asignatura->grupos as $grupo)
                        @php
                            $lleno = $grupo->estaLleno();
                            $elegido = $idsSeleccion->contains($grupo->id);
                            $pct = $grupo->cupo > 0 ? round(($grupo->tomados / $grupo->cupo) * 100) : 0;
                            $docente = $grupo->profesor->nombre ?? $grupo->docente;
                        @endphp
                        <div class="group">
                            <div>
                                <strong>{{ $grupo->codigo_grupo }}</strong>
                                <div class="muted">{{ $docente }} · {{ $grupo->horario() }}</div>
                            </div>
                            <div>
                                <div class="seats">{{ $grupo->cuposLibres() }} cupos libres</div>
                                <div class="bar {{ $pct >= 90 ? 'is-low' : '' }}"><i style="width:{{ $pct }}%"></i></div>
                            </div>
                            <form method="POST" action="{{ route('matricula.seleccionar') }}">
                                @csrf
                                <input type="hidden" name="grupo_id" value="{{ $grupo->id }}">
                                <button class="btn {{ $elegido ? 'btn-aqua' : 'btn-ghost' }}" type="submit" @disabled($lleno && ! $elegido)>
                                    {{ $lleno && ! $elegido ? 'Sin cupo' : ($elegido ? 'Seleccionado' : 'Seleccionar') }}
                                </button>
                            </form>
                        </div>
                    @endforeach
                </article>
            @empty
                <div class="card muted">No hay cursos con ese criterio.</div>
            @endforelse
        </div>
        <aside class="card cart">
            <h2 style="margin-top:0">Tu selección</h2>
            @if ($seleccion->isEmpty())
                <p class="muted">Elige un grupo para armar tu horario.</p>
            @else
                <ul style="list-style:none;padding:0;margin:0">
                    @foreach ($seleccion as $grupo)
                        <li>
                            <div>
                                <strong>{{ $grupo->asignatura->codigo }}</strong> {{ $grupo->asignatura->nombre }}<br>
                                <span class="muted">{{ $grupo->codigo_grupo }} · {{ $grupo->horario() }}</span>
                            </div>
                            <form method="POST" action="{{ route('matricula.quitar') }}">
                                @csrf
                                <input type="hidden" name="grupo_id" value="{{ $grupo->id }}">
                                <button class="tiny" type="submit">Quitar</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
            <p style="margin:1rem 0 0">Créditos: <strong>{{ $creditos }}</strong> / {{ $estudiante->creditos_max }}</p>
            <div class="actions">
                <a class="btn btn-hot {{ $seleccion->isEmpty() ? 'is-off' : '' }}" href="{{ $seleccion->isEmpty() ? '#' : route('matricula.prematricula') }}">Revisar horario</a>
            </div>
        </aside>
    </div>
@endsection
