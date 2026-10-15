<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ([
            'preselecciones',
            'matricula_cursos',
            'matriculas',
            'estudiante_requisitos',
            'grupos_asignatura',
            'asignaturas',
            'docentes',
            'estudiantes',
            'capacitaciones',
            'cursos',
            'personas',
        ] as $tabla) {
            Schema::dropIfExists($tabla);
        }

        Schema::enableForeignKeyConstraints();

        $comentarios = [
            'users' => 'Usuarios de LegalCloud AI (administrador, documental, especialista, consultor)',
            'roles' => 'Catálogo de roles y permisos de la plataforma',
            'categorias' => 'Clasificación documental: reglamento, resolución, circular, contrato',
            'expedientes' => 'Carpetas de trámite y seguimiento de estado',
            'documentos' => 'Acervo documental: PDF, OCR e indexación',
            'documento_versiones' => 'Historial de versiones de cada documento',
            'consultas_rag' => 'Preguntas al asistente y fuentes recuperadas (demo)',
            'notificaciones' => 'Avisos de carga, OCR y consultas',
            'auditorias' => 'Bitácora de operaciones de usuarios',
            'sessions' => 'Sesiones de inicio de sesión',
            'cache' => 'Caché de la aplicación',
            'jobs' => 'Cola de trabajos en segundo plano',
        ];

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            foreach ($comentarios as $tabla => $comentario) {
                if (Schema::hasTable($tabla)) {
                    $seguro = str_replace(["\\", "'"], ["\\\\", "\\'"], $comentario);
                    DB::unprepared("ALTER TABLE `{$tabla}` COMMENT '{$seguro}'");
                }
            }
        }
    }

    public function down(): void
    {
        // Las tablas heredadas no se recrean: no pertenecen a LegalCloud.
    }
};
