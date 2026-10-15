@extends('layouts.crud')

@section('title', 'Editar capacitación')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-7">
            <div class="card border-0">
                <div class="card-body p-4 p-md-5">
                    <div class="mb-4">
                        <span class="badge text-bg-warning mb-2">Editar</span>
                        <h1 class="h2 mb-2">Editar capacitación</h1>
                        <p class="text-secondary mb-0">
                            Datos de {{ $persona->nombre }} {{ $persona->apellidos }}.
                            Selecciona la fecha en el calendario.
                        </p>
                        <a href="{{ route('capacitaciones.index', $persona) }}" class="small">Volver al listado</a>
                    </div>

                    @include('partials.errores')

                    <form method="POST" action="{{ route('capacitaciones.update', [$persona, $capacitacion]) }}" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="capacitacion" class="form-label">Capacitación</label>
                            <input
                                type="text"
                                class="form-control @error('capacitacion') is-invalid @enderror"
                                id="capacitacion"
                                name="capacitacion"
                                value="{{ old('capacitacion', $capacitacion->capacitacion) }}"
                                maxlength="150"
                                required
                                autofocus
                            >
                            @error('capacitacion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="fecha" class="form-label">Fecha</label>
                            <input
                                type="date"
                                class="form-control @error('fecha') is-invalid @enderror"
                                id="fecha"
                                name="fecha"
                                value="{{ old('fecha', $capacitacion->fecha->format('Y-m-d')) }}"
                                required
                            >
                            @error('fecha')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="duracion" class="form-label">Duración</label>
                            <input
                                type="text"
                                class="form-control @error('duracion') is-invalid @enderror"
                                id="duracion"
                                name="duracion"
                                value="{{ old('duracion', $capacitacion->duracion) }}"
                                placeholder="8 horas"
                                maxlength="50"
                                required
                            >
                            @error('duracion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('capacitaciones.index', $persona) }}" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
