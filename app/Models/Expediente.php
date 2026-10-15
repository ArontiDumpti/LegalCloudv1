<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expediente extends Model
{
    protected $fillable = [
        'codigo',
        'titulo',
        'descripcion',
        'estado',
        'prioridad',
        'categoria_id',
        'responsable_id',
        'creado_por',
        'fecha_apertura',
        'fecha_cierre',
    ];

    protected function casts(): array
    {
        return [
            'fecha_apertura' => 'date',
            'fecha_cierre' => 'date',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }
}
