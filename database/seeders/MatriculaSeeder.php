<?php

namespace Database\Seeders;

use App\Models\Asignatura;
use App\Models\Docente;
use App\Models\Estudiante;
use Illuminate\Database\Seeder;

class MatriculaSeeder extends Seeder
{
    public function run(): void
    {
        $estudiante = Estudiante::query()->updateOrCreate(
            ['matricula' => '20241234'],
            [
                'pin' => '1234',
                'nombre' => 'Luna',
                'apellidos' => 'Vega',
                'programa' => 'Ingeniería de Sistemas',
                'facultad' => 'Ingeniería',
                'semestre' => 5,
                'promedio' => 4.18,
                'estado' => 'Regular',
                'periodo' => '2026-2',
                'creditos_aprobados' => 78,
                'creditos_max' => 18,
            ]
        );

        $estudiante->requisitos()->delete();
        foreach ([
            'Paz y salvo financiero',
            'Encuesta docente del periodo anterior',
            'Carnet institucional vigente',
            'Prerrequisitos académicos del semestre',
            'Actualización de datos de contacto',
        ] as $nombre) {
            $estudiante->requisitos()->create([
                'nombre' => $nombre,
                'cumple' => true,
            ]);
        }

        $catalogo = [
            [
                'codigo' => 'MAT350',
                'nombre' => 'Cálculo III',
                'creditos' => 4,
                'facultad' => 'Ciencias',
                'prerrequisito' => 'Cálculo II',
                'grupos' => [
                    ['codigo_grupo' => 'MAT350-A', 'docente' => 'Dra. Ruiz', 'dias' => ['Lun', 'Mie'], 'inicio' => '08:00', 'fin' => '10:00', 'aula' => 'B-204', 'cupo' => 32, 'tomados' => 28],
                    ['codigo_grupo' => 'MAT350-B', 'docente' => 'Ing. Soto', 'dias' => ['Mar', 'Jue'], 'inicio' => '10:00', 'fin' => '12:00', 'aula' => 'B-110', 'cupo' => 30, 'tomados' => 30],
                ],
            ],
            [
                'codigo' => 'SIS401',
                'nombre' => 'Bases de datos',
                'creditos' => 3,
                'facultad' => 'Ingeniería',
                'prerrequisito' => 'Estructuras de datos',
                'grupos' => [
                    ['codigo_grupo' => 'SIS401-A', 'docente' => 'MSc. Peña', 'dias' => ['Lun', 'Mie'], 'inicio' => '08:00', 'fin' => '10:00', 'aula' => 'LAB-2', 'cupo' => 24, 'tomados' => 19],
                    ['codigo_grupo' => 'SIS401-B', 'docente' => 'MSc. Peña', 'dias' => ['Vie'], 'inicio' => '14:00', 'fin' => '18:00', 'aula' => 'LAB-2', 'cupo' => 24, 'tomados' => 11],
                ],
            ],
            [
                'codigo' => 'SIS420',
                'nombre' => 'Ingeniería de software',
                'creditos' => 3,
                'facultad' => 'Ingeniería',
                'prerrequisito' => 'POO',
                'grupos' => [
                    ['codigo_grupo' => 'SIS420-A', 'docente' => 'PhD. Mora', 'dias' => ['Mar', 'Jue'], 'inicio' => '10:00', 'fin' => '12:00', 'aula' => 'C-301', 'cupo' => 28, 'tomados' => 21],
                ],
            ],
            [
                'codigo' => 'SIS433',
                'nombre' => 'Redes de computadores',
                'creditos' => 3,
                'facultad' => 'Ingeniería',
                'prerrequisito' => 'Sistemas operativos',
                'grupos' => [
                    ['codigo_grupo' => 'SIS433-A', 'docente' => 'Ing. Valle', 'dias' => ['Vie'], 'inicio' => '08:00', 'fin' => '12:00', 'aula' => 'LAB-RED', 'cupo' => 20, 'tomados' => 17],
                    ['codigo_grupo' => 'SIS433-B', 'docente' => 'Ing. Valle', 'dias' => ['Mie'], 'inicio' => '14:00', 'fin' => '18:00', 'aula' => 'LAB-RED', 'cupo' => 20, 'tomados' => 8],
                ],
            ],
            [
                'codigo' => 'HUM210',
                'nombre' => 'Ética profesional',
                'creditos' => 2,
                'facultad' => 'Humanidades',
                'prerrequisito' => 'Ninguno',
                'grupos' => [
                    ['codigo_grupo' => 'HUM210-A', 'docente' => 'Lic. Cárdenas', 'dias' => ['Mie'], 'inicio' => '14:00', 'fin' => '16:00', 'aula' => 'A-12', 'cupo' => 40, 'tomados' => 22],
                ],
            ],
            [
                'codigo' => 'SIS450',
                'nombre' => 'Interfaz y experiencia de usuario',
                'creditos' => 3,
                'facultad' => 'Ingeniería',
                'prerrequisito' => 'Ingeniería de software',
                'grupos' => [
                    ['codigo_grupo' => 'SIS450-A', 'docente' => 'Dis. Navarro', 'dias' => ['Mar'], 'inicio' => '18:00', 'fin' => '21:00', 'aula' => 'LAB-UX', 'cupo' => 22, 'tomados' => 14],
                ],
            ],
            [
                'codigo' => 'EST300',
                'nombre' => 'Estadística aplicada',
                'creditos' => 3,
                'facultad' => 'Ciencias',
                'prerrequisito' => 'Cálculo II',
                'grupos' => [
                    ['codigo_grupo' => 'EST300-A', 'docente' => 'Dra. León', 'dias' => ['Lun', 'Mie'], 'inicio' => '16:00', 'fin' => '18:00', 'aula' => 'B-018', 'cupo' => 35, 'tomados' => 33],
                ],
            ],
        ];

        foreach ($catalogo as $materia) {
            $grupos = $materia['grupos'];
            unset($materia['grupos']);
            $asignatura = Asignatura::query()->updateOrCreate(
                ['codigo' => $materia['codigo']],
                $materia
            );
            foreach ($grupos as $grupo) {
                $docente = Docente::query()->firstOrCreate(
                    ['nombre' => $grupo['docente']],
                    ['facultad' => $materia['facultad']]
                );
                $grupo['docente_id'] = $docente->id;
                $asignatura->grupos()->updateOrCreate(
                    ['codigo_grupo' => $grupo['codigo_grupo']],
                    $grupo
                );
            }
        }
    }
}
