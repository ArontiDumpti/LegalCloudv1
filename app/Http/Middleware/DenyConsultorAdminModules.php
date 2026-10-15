<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DenyConsultorAdminModules
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->esConsultor()) {
            abort(403, 'El usuario consultor no tiene acceso a este módulo.');
        }

        return $next($request);
    }
}
