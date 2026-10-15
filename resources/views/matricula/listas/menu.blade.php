@extends('layouts.matricula')

@section('title', 'Ver lista · Matrícula')

@section('content')
    <div class="page-head">
        <span class="badge badge-tide">Catálogo</span>
        <h1>Ver lista</h1>
        <p class="muted">Elige la lista que quieres consultar. Luego puedes agregar o editar registros.</p>
    </div>

    <div class="picks">
        <a class="card pick" href="{{ route('matricula.listas.ver', 'estudiantes') }}">
            <span class="badge badge-hot">Estudiantes</span>
            <strong>{{ $totales['estudiantes'] }}</strong>
            <span class="muted">Quién se matricula</span>
        </a>
        <a class="card pick" href="{{ route('matricula.listas.ver', 'cursos') }}">
            <span class="badge badge-aqua">Cursos</span>
            <strong>{{ $totales['cursos'] }}</strong>
            <span class="muted">Oferta y grupos</span>
        </a>
        <a class="card pick" href="{{ route('matricula.listas.ver', 'docentes') }}">
            <span class="badge badge-rose">Docentes</span>
            <strong>{{ $totales['docentes'] }}</strong>
            <span class="muted">Quién dicta cada curso</span>
        </a>
    </div>

    <div class="actions" style="justify-content:flex-start">
        <a class="btn btn-soft" href="{{ route('matricula.inicio') }}">Volver a matricularse</a>
    </div>
@endsection
