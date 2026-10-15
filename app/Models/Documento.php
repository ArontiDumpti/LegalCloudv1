<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Documento extends Model
{
    protected $fillable = [
        'expediente_id',
        'categoria_id',
        'codigo',
        'titulo',
        'tipo',
        'descripcion',
        'archivo_nombre',
        'archivo_ruta',
        'mime',
        'tamano',
        'version_actual',
        'ocr_estado',
        'contenido_ocr',
        'embedding_estado',
        'subido_por',
    ];

    public function expediente(): BelongsTo
    {
        return $this->belongsTo(Expediente::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    public function versiones(): HasMany
    {
        return $this->hasMany(DocumentoVersion::class)->orderByDesc('numero');
    }
}
