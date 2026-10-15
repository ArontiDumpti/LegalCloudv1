<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Persona extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'apellidos',
        'email',
        'telefono',
    ];

    public function capacitaciones(): HasMany
    {
        return $this->hasMany(Capacitacion::class);
    }
}
