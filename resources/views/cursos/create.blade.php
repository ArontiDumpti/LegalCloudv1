<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registrar curso</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <main class="container py-5">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8 col-xl-7">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 p-md-5">
                            <div class="mb-4">
                                <span class="badge text-bg-primary mb-2">Paso 1</span>
                                <h1 class="h2 mb-2">Registrar curso</h1>
                                <p class="text-secondary mb-0">Completa los datos. La descripción es opcional.</p>
                                <a href="{{ route('cursos.index') }}" class="small">Ver listado de cursos</a>
                            </div>

                            <form method="POST" action="{{ route('cursos.store') }}" novalidate>
                                @csrf

                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre del curso</label>
                                    <input
                                        type="text"
                                        class="form-control @error('nombre') is-invalid @enderror"
                                        id="nombre"
                                        name="nombre"
                                        value="{{ old('nombre') }}"
                                        maxlength="150"
                                        required
                                        autofocus
                                    >
                                    @error('nombre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="instructor" class="form-label">Instructor</label>
                                    <input
                                        type="text"
                                        class="form-control @error('instructor') is-invalid @enderror"
                                        id="instructor"
                                        name="instructor"
                                        value="{{ old('instructor') }}"
                                        maxlength="150"
                                        required
                                    >
                                    @error('instructor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="duracion_horas" class="form-label">Duración (horas)</label>
                                    <input
                                        type="number"
                                        class="form-control @error('duracion_horas') is-invalid @enderror"
                                        id="duracion_horas"
                                        name="duracion_horas"
                                        value="{{ old('duracion_horas') }}"
                                        min="1"
                                        max="1000"
                                        required
                                    >
                                    @error('duracion_horas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="descripcion" class="form-label">Descripción <span class="text-secondary">(opcional)</span></label>
                                    <input
                                        type="text"
                                        class="form-control @error('descripcion') is-invalid @enderror"
                                        id="descripcion"
                                        name="descripcion"
                                        value="{{ old('descripcion') }}"
                                        maxlength="255"
                                    >
                                    @error('descripcion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">Enviar datos</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>
