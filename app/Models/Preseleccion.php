<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Preseleccion extends Model
{
    protected $table = 'preselecciones';

    protected $fillable = [
        'estudiante_id',
        'grupo_id',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoAsignatura::class, 'grupo_id');
    }
}
