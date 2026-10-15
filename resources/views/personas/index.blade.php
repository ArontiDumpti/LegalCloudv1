@extends('layouts.crud')

@section('title', 'Personas')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <span class="badge text-bg-secondary mb-2">Listado</span>
            <h1 class="h2 mb-1">Personas registradas</h1>
            <p class="text-secondary mb-0">Busca, edita, elimina o registra capacitaciones.</p>
        </div>
        <a href="{{ route('personas.crear') }}" class="btn btn-primary">Registrar persona</a>
    </div>

    @if (session('exito') || session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('exito') ?? session('success') }}
        </div>
    @endif

    <form method="GET" action="{{ route('personas.index') }}" class="card border-0 mb-4">
        <div class="card-body">
            <label for="q" class="form-label">Buscador</label>
            <div class="input-group">
                <input
                    type="search"
                    class="form-control"
                    id="q"
                    name="q"
                    value="{{ $busqueda }}"
                    placeholder="Nombre, apellidos, correo o teléfono"
                >
                <button type="submit" class="btn btn-outline-primary">Buscar</button>
                @if ($busqueda !== '')
                    <a href="{{ route('personas.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                @endif
            </div>
        </div>
    </form>

    <div class="card border-0">
        <div class="card-body p-0">
            @if ($personas->isEmpty())
                <div class="p-4 p-md-5 text-center text-secondary">
                    @if ($busqueda !== '')
                        No hay personas que coincidan con “{{ $busqueda }}”.
                    @else
                        Aún no hay personas registradas.
                    @endif
                    <div class="mt-3">
                        @if ($busqueda !== '')
                            <a href="{{ route('personas.index') }}" class="btn btn-outline-secondary">Ver todas</a>
                        @else
                            <a href="{{ route('personas.crear') }}" class="btn btn-outline-primary">Añadir la primera</a>
                        @endif
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Apellidos</th>
                                <th scope="col">Correo electrónico</th>
                                <th scope="col">Teléfono</th>
                                <th scope="col" class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($personas as $persona)
                                <tr>
                                    <td>{{ $persona->id }}</td>
                                    <td>{{ $persona->nombre }}</td>
                                    <td>{{ $persona->apellidos }}</td>
                                    <td class="text-break">{{ $persona->email }}</td>
                                    <td>{{ $persona->telefono ?: 'No indicado' }}</td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-2 flex-wrap">
                                            <a href="{{ route('personas.editar', $persona) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                            <a href="{{ route('capacitaciones.index', $persona) }}" class="btn btn-sm btn-outline-success">Capacitación</a>
                                            <form
                                                method="POST"
                                                action="{{ route('personas.destroy', $persona) }}"
                                                onsubmit="return confirm('¿Eliminar a {{ $persona->nombre }} {{ $persona->apellidos }}?')"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
