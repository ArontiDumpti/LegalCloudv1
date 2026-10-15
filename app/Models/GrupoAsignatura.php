<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrupoAsignatura extends Model
{
    protected $table = 'grupos_asignatura';

    protected $fillable = [
        'asignatura_id',
        'codigo_grupo',
        'docente_id',
        'docente',
        'dias',
        'inicio',
        'fin',
        'aula',
        'cupo',
        'tomados',
    ];

    protected function casts(): array
    {
        return [
            'dias' => 'array',
        ];
    }

    public function asignatura(): BelongsTo
    {
        return $this->belongsTo(Asignatura::class);
    }

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    public function preselecciones(): HasMany
    {
        return $this->hasMany(Preseleccion::class, 'grupo_id');
    }

    public function cuposLibres(): int
    {
        return max(0, $this->cupo - $this->tomados);
    }

    public function estaLleno(): bool
    {
        return $this->cuposLibres() <= 0;
    }

    public function horario(): string
    {
        return implode(' / ', $this->dias).' '.$this->inicio.'–'.$this->fin.' · '.$this->aula;
    }
}
