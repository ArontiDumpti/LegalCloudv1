<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asignatura extends Model
{
    protected $fillable = [
        'codigo',
        'nombre',
        'creditos',
        'facultad',
        'prerrequisito',
    ];

    public function grupos(): HasMany
    {
        return $this->hasMany(GrupoAsignatura::class);
    }
}
