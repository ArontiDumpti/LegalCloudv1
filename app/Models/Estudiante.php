<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estudiante extends Model
{
    protected $fillable = [
        'matricula',
        'pin',
        'nombre',
        'apellidos',
        'programa',
        'facultad',
        'semestre',
        'promedio',
        'estado',
        'periodo',
        'creditos_aprobados',
        'creditos_max',
    ];

    protected function casts(): array
    {
        return [
            'promedio' => 'float',
            'pin' => 'hashed',
        ];
    }

    public function requisitos(): HasMany
    {
        return $this->hasMany(EstudianteRequisito::class);
    }

    public function preselecciones(): HasMany
    {
        return $this->hasMany(Preseleccion::class);
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class);
    }

    public function nombreCompleto(): string
    {
        return $this->nombre.' '.$this->apellidos;
    }
}
