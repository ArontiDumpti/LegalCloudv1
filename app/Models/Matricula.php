<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Matricula extends Model
{
    protected $fillable = [
        'estudiante_id',
        'folio',
        'periodo',
        'creditos',
        'confirmada_at',
    ];

    protected function casts(): array
    {
        return [
            'confirmada_at' => 'datetime',
        ];
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function cursos(): HasMany
    {
        return $this->hasMany(MatriculaCurso::class);
    }
}
