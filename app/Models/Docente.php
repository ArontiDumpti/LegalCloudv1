<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Docente extends Model
{
    protected $fillable = [
        'nombre',
        'facultad',
    ];

    public function grupos(): HasMany
    {
        return $this->hasMany(GrupoAsignatura::class);
    }
}
