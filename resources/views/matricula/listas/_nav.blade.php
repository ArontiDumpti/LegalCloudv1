<div class="list-switch">
    <a class="btn btn-soft" href="{{ route('matricula.listas') }}">Elegir lista</a>
    <a class="{{ ($lista ?? '') === 'estudiantes' ? 'btn btn-aqua' : 'btn btn-ghost' }}" href="{{ route('matricula.listas.ver', 'estudiantes') }}">Estudiantes</a>
    <a class="{{ ($lista ?? '') === 'cursos' ? 'btn btn-aqua' : 'btn btn-ghost' }}" href="{{ route('matricula.listas.ver', 'cursos') }}">Cursos</a>
    <a class="{{ ($lista ?? '') === 'docentes' ? 'btn btn-aqua' : 'btn btn-ghost' }}" href="{{ route('matricula.listas.ver', 'docentes') }}">Docentes</a>
    <a class="btn btn-soft" href="{{ route('matricula.inicio') }}">Matricularse</a>
</div>
