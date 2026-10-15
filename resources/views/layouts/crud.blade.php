<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title', 'Personas')</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700|syne:600,700,800" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="{{ asset('css/crud.css') }}?v=2" rel="stylesheet">
    </head>
    <body>
        <div class="glow-hot"></div>
        <div class="glow-aqua"></div>
        <div class="palette-rail" aria-hidden="true">
            <span style="background:#000000"></span>
            <span style="background:#D88997"></span>
            <span style="background:#FF2D55"></span>
            <span style="background:#1FE5E1"></span>
            <span style="background:#3D717C"></span>
        </div>

        <header class="app-header">
            <div class="container py-3 d-flex align-items-center justify-content-between gap-3">
                <a href="{{ route('personas.index') }}" class="brand">AURA<span style="color:#FF2D55">.</span></a>
                <nav class="d-flex align-items-center gap-3">
                    <a href="{{ route('personas.index') }}" class="{{ request()->routeIs('personas.index') ? 'active' : '' }}">Personas</a>
                    <a href="{{ route('personas.crear') }}" class="{{ request()->routeIs('personas.crear') ? 'active' : '' }}">Registrar</a>
                    @auth
                        <form method="POST" action="{{ route('logout') }}" class="mb-0">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Cerrar sesión</button>
                        </form>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="container app-shell py-5">
            @yield('content')
        </main>
    </body>
</html>
