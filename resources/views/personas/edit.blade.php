@extends('layouts.crud')

@section('title', 'Editar persona')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-7">
            <div class="card border-0">
                <div class="card-body p-4 p-md-5">
                    <div class="mb-4">
                        <span class="badge text-bg-warning mb-2">Editar</span>
                        <h1 class="h2 mb-2">Editar persona</h1>
                        <p class="text-secondary mb-0">Modifica los datos y guarda los cambios.</p>
                        <a href="{{ route('personas.index') }}" class="small">Volver al listado</a>
                    </div>

                    @include('partials.errores')

                    <form method="POST" action="{{ route('personas.update', $persona) }}" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input
                                type="text"
                                class="form-control @error('nombre') is-invalid @enderror"
                                id="nombre"
                                name="nombre"
                                value="{{ old('nombre', $persona->nombre) }}"
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
                                value="{{ old('apellidos', $persona->apellidos) }}"
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
                                value="{{ old('email', $persona->email) }}"
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
                                value="{{ old('telefono', $persona->telefono) }}"
                                maxlength="30"
                            >
                            @error('telefono')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('personas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
