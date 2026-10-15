<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Confirmar curso</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <main class="container py-5">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8 col-xl-7">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4 p-md-5">
                            <div class="mb-4">
                                <span class="badge text-bg-success mb-2">Paso final</span>
                                <h1 class="h2 mb-2">Confirma los datos</h1>
                                <p class="text-secondary mb-0">Comprueba que la información del curso sea correcta.</p>
                            </div>

                            <div class="alert alert-success d-flex align-items-center" role="alert">
                                <div>Los datos se han recibido correctamente.</div>
                            </div>

                            <dl class="row g-0 border rounded overflow-hidden mb-4">
                                <div class="col-12 d-flex flex-column flex-sm-row border-bottom">
                                    <dt class="col-sm-4 bg-light px-3 py-3 mb-0 fw-semibold">Nombre</dt>
                                    <dd class="col-sm-8 px-3 py-3 mb-0">{{ $curso->nombre }}</dd>
                                </div>

                                <div class="col-12 d-flex flex-column flex-sm-row border-bottom">
                                    <dt class="col-sm-4 bg-light px-3 py-3 mb-0 fw-semibold">Instructor</dt>
                                    <dd class="col-sm-8 px-3 py-3 mb-0">{{ $curso->instructor }}</dd>
                                </div>

                                <div class="col-12 d-flex flex-column flex-sm-row border-bottom">
                                    <dt class="col-sm-4 bg-light px-3 py-3 mb-0 fw-semibold">Duración</dt>
                                    <dd class="col-sm-8 px-3 py-3 mb-0">{{ $curso->duracion_horas }} h</dd>
                                </div>

                                <div class="col-12 d-flex flex-column flex-sm-row">
                                    <dt class="col-sm-4 bg-light px-3 py-3 mb-0 fw-semibold">Descripción</dt>
                                    <dd class="col-sm-8 px-3 py-3 mb-0">{{ $curso->descripcion ?: 'No indicada' }}</dd>
                                </div>
                            </dl>

                            <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                                <a href="{{ route('cursos.crear') }}" class="btn btn-outline-secondary">Registrar otro</a>
                                <a href="{{ route('cursos.index') }}" class="btn btn-primary">Ver listado</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>
