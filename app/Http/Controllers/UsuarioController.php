<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Support\AuditoriaLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UsuarioController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('usuarios/index', [
            'usuarios' => User::orderBy('name')->get(),
            'roles' => Role::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:administrador,responsable_documental,especialista,consultor'],
            'cargo' => ['nullable', 'string', 'max:120'],
        ]);

        User::create([
            ...$datos,
            'password' => Hash::make('password'),
            'activo' => true,
            'email_verified_at' => now(),
        ]);

        AuditoriaLogger::registrar('alta_usuario', 'usuarios', "Alta de {$datos['email']} ({$datos['role']})");

        return back()->with('success', 'Usuario creado. Contraseña inicial: password');
    }
}
