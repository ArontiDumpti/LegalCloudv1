@extends('layouts.matricula')

@section('title', 'Comprobante · Matrícula')

@section('content')
    <div class="receipt">
        <span class="badge badge-aqua">Paso 4</span>
        <h1 style="margin:0.45rem 0 0.3rem">Matrícula confirmada</h1>
        <p class="muted">Guarda este comprobante. Si cambias de plan, puedes cancelar y volver a elegir.</p>

        <article class="card" style="margin-top:1rem">
            <div style="display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap">
                <div>
                    <p class="muted" style="margin:0">Universidad AURA</p>
                    <h2 style="margin:0.2rem 0">Comprobante de matrícula</h2>
                </div>
                <div>
                    <div class="muted">Folio</div>
                    <div class="folio">{{ $matricula->folio }}</div>
                </div>
            </div>
            <div class="bars" aria-hidden="true"></div>
            <p>
                <strong>{{ $estudiante->nombreCompleto() }}</strong><br>
                <span class="muted">{{ $estudiante->matricula }} · {{ $estudiante->programa }} · {{ $matricula->periodo }}</span>
            </p>
            <table>
                <thead>
                    <tr><th>Código</th><th>Asignatura</th><th>Grupo</th><th>Horario</th><th>Créditos</th></tr>
                </thead>
                <tbody>
                    @foreach ($matricula->cursos as $curso)
                        <tr>
                            <td>{{ $curso->codigo }}</td>
                            <td>{{ $curso->nombre }}</td>
                            <td>{{ $curso->codigo_grupo }}</td>
                            <td>{{ $curso->horario() }}</td>
                            <td>{{ $curso->creditos }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p style="margin:1rem 0 0">Total créditos: <strong>{{ $matricula->creditos }}</strong></p>
            <p class="muted">{{ $matricula->confirmada_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
        </article>

        <div class="actions no-print">
            <form method="POST" action="{{ route('matricula.cancelar') }}">
                @csrf
                <button class="btn btn-soft" type="submit">Cancelar matrícula</button>
            </form>
            <button class="btn btn-aqua" type="button" onclick="window.print()">Imprimir</button>
        </div>
    </div>
@endsection
