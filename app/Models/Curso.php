<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'instructor',
        'duracion_horas',
        'descripcion',
    ];
}
