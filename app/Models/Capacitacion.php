<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Capacitacion extends Model
{
    protected $table = 'capacitaciones';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'persona_id',
        'capacitacion',
        'fecha',
        'duracion',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }
}
