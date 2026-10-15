<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title', 'Matrícula')</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700|syne:600,700,800" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('css/matricula.css') }}">
    </head>
    <body>
        <div class="glow-hot"></div>
        <div class="glow-aqua"></div>
        <div class="rail" aria-hidden="true">
            <span style="background:#000"></span>
            <span style="background:#D88997"></span>
            <span style="background:#FF2D55"></span>
            <span style="background:#1FE5E1"></span>
            <span style="background:#3D717C"></span>
        </div>

        <header class="app">
            <div class="topbar">
                <a class="brand" href="{{ route('matricula.inicio') }}">AURA<span>.</span></a>
                <div class="tools">
                    <div class="period">
                        @isset($estudiante)
                            {{ $estudiante->nombreCompleto() }} · {{ $estudiante->matricula }}
                        @else
                            Periodo 2026-2
                        @endisset
                    </div>
                    @if (isset($estudiante) && ($paso ?? 1) >= 2)
                        <form method="POST" action="{{ route('matricula.cancelar') }}">
                            @csrf
                            <button class="btn btn-soft" type="submit">Cancelar matrícula</button>
                        </form>
                    @endif
                </div>
            </div>
            <nav class="steps" aria-label="Etapas">
                @php $paso = $paso ?? 1; @endphp
                <a class="step {{ $paso === 1 ? 'is-on' : ($paso > 1 ? 'is-done' : '') }}" href="{{ route('matricula.inicio') }}"><b>1</b><span>Inicio</span></a>
                <a class="step {{ $paso === 2 ? 'is-on' : ($paso > 2 ? 'is-done' : '') }}" href="{{ route('matricula.oferta') }}"><b>2</b><span>Cursos</span></a>
                <a class="step {{ $paso === 3 ? 'is-on' : ($paso > 3 ? 'is-done' : '') }}" href="{{ route('matricula.prematricula') }}"><b>3</b><span>Horario</span></a>
                <a class="step {{ $paso === 4 ? 'is-on' : '' }}" href="{{ route('matricula.comprobante') }}"><b>4</b><span>Comprobante</span></a>
            </nav>
        </header>

        <main class="wrap">
            @if (session('ok'))
                <div class="alert alert-ok">{{ session('ok') }}</div>
            @endif
            @yield('content')
        </main>
    </body>
</html>
