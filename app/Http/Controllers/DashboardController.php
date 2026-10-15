<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\ConsultaRag;
use App\Models\Documento;
use App\Models\Expediente;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('dashboard', [
            'stats' => [
                'expedientes' => Expediente::count(),
                'documentos' => Documento::count(),
                'ocr_procesados' => Documento::where('ocr_estado', 'procesado')->count(),
                'consultas' => ConsultaRag::count(),
                'usuarios' => User::count(),
                'alertas' => Notificacion::where('user_id', $request->user()->id)->where('leida', false)->count(),
            ],
            'expedientes' => Expediente::with(['categoria', 'responsable'])->latest()->limit(5)->get(),
            'documentos' => Documento::with(['expediente', 'categoria'])->latest()->limit(5)->get(),
            'consultas' => ConsultaRag::with('user')->latest()->limit(4)->get(),
            'auditorias' => Auditoria::with('user')->latest()->limit(6)->get(),
            'equipo' => User::query()->where('activo', true)->orderBy('name')->get(['id', 'name', 'email', 'role', 'cargo']),
        ]);
    }
}
