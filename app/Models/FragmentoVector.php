<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FragmentoVector extends Model
{
    protected $table = 'fragmentos_vector';

    protected $fillable = [
        'documento_id',
        'orden',
        'texto',
        'embedding',
        'modelo',
    ];

    protected function casts(): array
    {
        return [
            'embedding' => 'array',
        ];
    }

    public function documento(): BelongsTo
    {
        return $this->belongsTo(Documento::class);
    }
}
