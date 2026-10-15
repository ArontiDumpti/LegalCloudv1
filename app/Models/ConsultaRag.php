<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultaRag extends Model
{
    protected $table = 'consultas_rag';

    protected $fillable = [
        'user_id',
        'pregunta',
        'respuesta',
        'fuentes',
        'modelo_simulado',
    ];

    protected function casts(): array
    {
        return [
            'fuentes' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
