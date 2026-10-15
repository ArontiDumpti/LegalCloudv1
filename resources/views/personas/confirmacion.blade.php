@extends('layouts.crud')

@section('title', 'Confirmar datos')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-7">
            <div class="card border-0">
                <div class="card-body p-4 p-md-5">
                    <div class="mb-4">
                        <span class="badge text-bg-success mb-2">Paso final</span>
                        <h1 class="h2 mb-2">Confirma los datos</h1>
                        <p class="text-secondary mb-0">Comprueba que la información sea correcta antes de continuar.</p>
                    </div>

                    <div class="alert alert-success d-flex align-items-center" role="alert">
                        <div>Los datos se han recibido correctamente.</div>
                    </div>

                    <dl class="row g-0 border rounded overflow-hidden mb-4">
                        <div class="col-12 d-flex flex-column flex-sm-row border-bottom">
                            <dt class="col-sm-4 px-3 py-3 mb-0 fw-semibold">Nombre</dt>
                            <dd class="col-sm-8 px-3 py-3 mb-0">{{ $persona->nombre }}</dd>
                        </div>

                        <div class="col-12 d-flex flex-column flex-sm-row border-bottom">
                            <dt class="col-sm-4 px-3 py-3 mb-0 fw-semibold">Apellidos</dt>
                            <dd class="col-sm-8 px-3 py-3 mb-0">{{ $persona->apellidos }}</dd>
                        </div>

                        <div class="col-12 d-flex flex-column flex-sm-row border-bottom">
                            <dt class="col-sm-4 px-3 py-3 mb-0 fw-semibold">Correo electrónico</dt>
                            <dd class="col-sm-8 px-3 py-3 mb-0 text-break">{{ $persona->email }}</dd>
                        </div>

                        <div class="col-12 d-flex flex-column flex-sm-row">
                            <dt class="col-sm-4 px-3 py-3 mb-0 fw-semibold">Teléfono</dt>
                            <dd class="col-sm-8 px-3 py-3 mb-0">{{ $persona->telefono ?? 'No indicado' }}</dd>
                        </div>
                    </dl>

                    <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                        <a href="{{ route('personas.crear') }}" class="btn btn-outline-secondary">Registrar otra</a>
                        <a href="{{ route('personas.index') }}" class="btn btn-primary">Ver listado</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
