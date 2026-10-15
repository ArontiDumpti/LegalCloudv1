<?php

namespace App\Support;

use App\Models\Auditoria;
use Illuminate\Support\Facades\Auth;

class AuditoriaLogger
{
    public static function registrar(string $accion, string $modulo, string $descripcion): void
    {
        Auditoria::create([
            'user_id' => Auth::id(),
            'accion' => $accion,
            'modulo' => $modulo,
            'descripcion' => $descripcion,
            'ip' => request()?->ip(),
        ]);
    }
}
