<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificacionController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('notificaciones/index', [
            'notificaciones' => Notificacion::where('user_id', $request->user()->id)->latest()->get(),
        ]);
    }

    public function leer(Request $request, Notificacion $notificacion): RedirectResponse
    {
        abort_unless($notificacion->user_id === $request->user()->id, 403);

        $notificacion->update(['leida' => true]);

        return back();
    }
}
