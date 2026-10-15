@extends('layouts.matricula')

@section('title', 'Inicio · Matrícula')

@section('content')
    <div class="page-head">
        <span class="badge badge-hot">Paso 1</span>
        <h1>Matricularse</h1>
        <p class="muted">Ingresa con tu matrícula y PIN para armar el horario del periodo 2026-2.</p>
    </div>

    @unless ($estudiante)
        <section class="card enroll">
            <div>
                <h2>Ingreso al periodo</h2>
                <p class="muted">Para consultar o actualizar el catálogo, abre las listas y elige cuál quieres ver.</p>
            </div>
            <form method="POST" action="{{ route('matricula.entrar') }}" novalidate>
                @csrf
                <div class="field">
                    <label for="matricula">Número de matrícula</label>
                    <input id="matricula" name="matricula" value="{{ old('matricula') }}" inputmode="numeric" autocomplete="username" required>
                </div>
                <div class="field">
                    <label for="pin">PIN</label>
                    <input id="pin" name="pin" type="password" autocomplete="current-password" required>
                    @error('matricula')<p class="err">{{ $message }}</p>@enderror
                    @error('pin')<p class="err">{{ $message }}</p>@enderror
                </div>
                <div class="actions">
                    <a class="btn btn-ghost" href="{{ route('matricula.listas') }}">Ver lista</a>
                    <button class="btn btn-hot" type="submit">Ingresar</button>
                </div>
            </form>
        </section>
    @else
        <section class="card enroll">
            <div>
                <span class="badge badge-aqua">Sesión activa</span>
                <h2 style="margin:0.45rem 0 0.2rem">{{ $estudiante->nombreCompleto() }}</h2>
                <p class="muted">{{ $estudiante->matricula }} · {{ $estudiante->programa }} · semestre {{ $estudiante->semestre }}</p>
            </div>
            <div>
                <div class="grid-2">
                    <div class="stat"><span class="muted">Estado</span><b>{{ $estudiante->estado }}</b></div>
                    <div class="stat"><span class="muted">Tope</span><b>{{ $estudiante->creditos_max }} cr</b></div>
                </div>
                @foreach ($estudiante->requisitos as $requisito)
                    <div class="req">
                        <span>{{ $requisito->nombre }}</span>
                        <strong class="{{ $requisito->cumple ? 'ok' : 'warn' }}">{{ $requisito->cumple ? 'Cumple' : 'Pendiente' }}</strong>
                    </div>
                @endforeach
                <div class="actions">
                    <form method="POST" action="{{ route('matricula.salir') }}">
                        @csrf
                        <button class="btn btn-soft" type="submit">Cerrar sesión</button>
                    </form>
                    <a class="btn btn-ghost" href="{{ route('matricula.listas') }}">Ver lista</a>
                    <a class="btn btn-hot" href="{{ route('matricula.oferta') }}">Elegir cursos</a>
                </div>
            </div>
        </section>
    @endunless
@endsection
