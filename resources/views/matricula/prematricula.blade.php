@extends('layouts.matricula')

@section('title', 'Horario · Matrícula')

@section('content')
    <div class="page-head">
        <span class="badge badge-hot">Paso 3</span>
        <h1>Revisa tu horario</h1>
    </div>

    @if (count($cruces))
        <div class="alert alert-bad">
            Hay cruce entre {{ collect($cruces)->map(fn ($par) => $par[0]->asignatura->codigo.' y '.$par[1]->asignatura->codigo)->join(', ') }}.
            Quita uno de esos grupos antes de confirmar.
        </div>
    @endif
    @if ($excede)
        <div class="alert alert-bad">Llevas {{ $creditos }} créditos y el tope es {{ $estudiante->creditos_max }}.</div>
    @endif
    @if ($puedeConfirmar)
        <div class="alert alert-ok">Horario libre y dentro del tope. Puedes confirmar.</div>
    @endif

    <div class="grid-2" style="margin-bottom:1rem">
        <div class="stat"><span class="muted">Asignaturas</span><b>{{ $seleccion->count() }}</b></div>
        <div class="stat"><span class="muted">Créditos</span><b>{{ $creditos }} / {{ $estudiante->creditos_max }}</b></div>
    </div>

    <div class="card" style="margin-bottom:1rem">
        <h2 style="margin-top:0">Horario semanal</h2>
        <div class="week">
            <div></div>
            @foreach ($horario['dias'] as $dia)
                <div class="head">{{ $dia }}</div>
            @endforeach
            @foreach ($horario['horas'] as $hora)
                <div class="hour">{{ $hora }}</div>
                @foreach ($horario['dias'] as $dia)
                    @php $grupo = $horario['celdas'][$hora][$dia] ?? null; @endphp
                    @if ($grupo && $grupo->inicio === $hora)
                        <div class="slot {{ isset($horario['cruces'][$grupo->id]) ? 'clash' : '' }}">
                            {{ $grupo->asignatura->codigo }}<br>{{ $grupo->inicio }}–{{ $grupo->fin }}
                        </div>
                    @elseif ($grupo)
                        <div class="slot {{ isset($horario['cruces'][$grupo->id]) ? 'clash' : '' }}"></div>
                    @else
                        <div class="cell"></div>
                    @endif
                @endforeach
            @endforeach
        </div>
    </div>

    <div class="card">
        <h2 style="margin-top:0">Resumen</h2>
        <table>
            <thead>
                <tr><th>Código</th><th>Asignatura</th><th>Grupo</th><th>Horario</th><th>Créditos</th></tr>
            </thead>
            <tbody>
                @foreach ($seleccion as $grupo)
                    <tr>
                        <td>{{ $grupo->asignatura->codigo }}</td>
                        <td>{{ $grupo->asignatura->nombre }}</td>
                        <td>{{ $grupo->codigo_grupo }}</td>
                        <td>{{ $grupo->horario() }}</td>
                        <td>{{ $grupo->asignatura->creditos }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="actions">
            <form method="POST" action="{{ route('matricula.cancelar') }}">
                @csrf
                <button class="btn btn-soft" type="submit">Cancelar matrícula</button>
            </form>
            <form method="POST" action="{{ route('matricula.confirmar') }}">
                @csrf
                <button class="btn btn-hot" type="submit" @disabled(! $puedeConfirmar)>Confirmar matrícula</button>
            </form>
        </div>
    </div>
@endsection
