@extends('layouts.crud')

@section('title', 'Capacitaciones')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <span class="badge text-bg-secondary mb-2">Capacitación</span>
            <h1 class="h2 mb-1">Capacitaciones</h1>
            <p class="text-secondary mb-0">
                Datos de {{ $persona->nombre }} {{ $persona->apellidos }}.
            </p>
            <a href="{{ route('personas.index') }}" class="small">Volver a personas</a>
        </div>
        <a href="{{ route('capacitaciones.crear', $persona) }}" class="btn btn-primary">Registrar capacitación</a>
    </div>

    @if (session('exito'))
        <div class="alert alert-success" role="alert">
            {{ session('exito') }}
        </div>
    @endif

    <div class="card border-0">
        <div class="card-body p-0">
            @if ($capacitaciones->isEmpty())
                <div class="p-4 p-md-5 text-center text-secondary">
                    Aún no hay capacitaciones registradas.
                    <div class="mt-3">
                        <a href="{{ route('capacitaciones.crear', $persona) }}" class="btn btn-outline-primary">Añadir la primera</a>
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Capacitación</th>
                                <th scope="col">Fecha</th>
                                <th scope="col">Duración</th>
                                <th scope="col" class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($capacitaciones as $capacitacion)
                                <tr>
                                    <td>{{ $capacitacion->capacitacion }}</td>
                                    <td>{{ $capacitacion->fecha->format('d/m/Y') }}</td>
                                    <td>{{ $capacitacion->duracion }}</td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('capacitaciones.editar', [$persona, $capacitacion]) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                            <form
                                                method="POST"
                                                action="{{ route('capacitaciones.destroy', [$persona, $capacitacion]) }}"
                                                onsubmit="return confirm('¿Eliminar la capacitación {{ $capacitacion->capacitacion }}?')"
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
