<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstudianteRequisito extends Model
{
    protected $table = 'estudiante_requisitos';

    protected $fillable = [
        'estudiante_id',
        'nombre',
        'cumple',
    ];

    protected function casts(): array
    {
        return [
            'cumple' => 'boolean',
        ];
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }
}
