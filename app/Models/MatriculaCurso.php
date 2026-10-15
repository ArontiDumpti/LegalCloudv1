<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatriculaCurso extends Model
{
    protected $table = 'matricula_cursos';

    protected $fillable = [
        'matricula_id',
        'grupo_id',
        'codigo',
        'nombre',
        'codigo_grupo',
        'docente',
        'dias',
        'inicio',
        'fin',
        'aula',
        'creditos',
    ];

    protected function casts(): array
    {
        return [
            'dias' => 'array',
        ];
    }

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class);
    }

    public function horario(): string
    {
        return implode(' / ', $this->dias).' '.$this->inicio.'–'.$this->fin.' · '.$this->aula;
    }
}
