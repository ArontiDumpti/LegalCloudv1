@extends('layouts.crud')

@section('title', 'Registrar persona')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-7">
            <div class="card border-0">
                <div class="card-body p-4 p-md-5">
                    <div class="mb-4">
                        <span class="badge text-bg-primary mb-2">Paso 1</span>
                        <h1 class="h2 mb-2">Registrar persona</h1>
                        <p class="text-secondary mb-0">Completa los datos. El teléfono es opcional.</p>
                        <a href="{{ route('personas.index') }}" class="small">Ver listado de personas</a>
                    </div>

                    @include('partials.errores')

                    <form method="POST" action="{{ route('personas.store') }}" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input
                                type="text"
                                class="form-control @error('nombre') is-invalid @enderror"
                                id="nombre"
                                name="nombre"
                                value="{{ old('nombre') }}"
                                maxlength="100"
                                required
                                autofocus
                            >
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="apellidos" class="form-label">Apellidos</label>
                            <input
                                type="text"
                                class="form-control @error('apellidos') is-invalid @enderror"
                                id="apellidos"
                                name="apellidos"
                                value="{{ old('apellidos') }}"
                                maxlength="150"
                                required
                            >
                            @error('apellidos')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="telefono" class="form-label">Teléfono <span class="text-secondary">(opcional)</span></label>
                            <input
                                type="text"
                                class="form-control @error('telefono') is-invalid @enderror"
                                id="telefono"
                                name="telefono"
                                value="{{ old('telefono') }}"
                                maxlength="30"
                            >
                            @error('telefono')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                            <a href="{{ route('personas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" name="accion" value="persona" class="btn btn-outline-primary">Enviar datos</button>
                            <button type="submit" name="accion" value="capacitacion" class="btn btn-primary">Registrar capacitación</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
