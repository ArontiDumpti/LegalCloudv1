<?php

namespace App\Http\Middleware;

use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'role_label' => $user instanceof User ? $user->rolNombre() : null,
                'puede_usuarios_auditoria' => $user instanceof User && $user->puedeVerUsuariosYAuditoria(),
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
            ],
            'notificaciones_count' => $user
                ? Notificacion::where('user_id', $user->id)->where('leida', false)->count()
                : 0,
        ];
    }
}
