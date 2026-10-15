<?php

namespace App\Http\Controllers;

use App\Models\ConsultaRag;
use App\Services\MockRagService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AsistenteController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('asistente/index', [
            'consultas' => ConsultaRag::with('user')
                ->where('user_id', $request->user()->id)
                ->latest()
                ->limit(12)
                ->get()
                ->reverse()
                ->values(),
            'sugeridas' => [
                '¿Cuáles son los requisitos establecidos para realizar determinado procedimiento?',
                '¿Qué plazos aplican para la presentación de recursos?',
                '¿Quién puede firmar la solicitud?',
            ],
        ]);
    }

    public function store(Request $request, MockRagService $rag): RedirectResponse
    {
        $datos = $request->validate([
            'pregunta' => ['required', 'string', 'max:1000'],
        ]);

        $rag->consultar($datos['pregunta'], $request->user());

        return back();
    }
}
