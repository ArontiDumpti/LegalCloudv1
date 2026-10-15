<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use Inertia\Inertia;
use Inertia\Response;

class AuditoriaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('auditoria/index', [
            'eventos' => Auditoria::with('user')->latest()->limit(80)->get(),
        ]);
    }
}
