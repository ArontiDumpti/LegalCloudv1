<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$usuarios = [
    ['email' => 'admin@legalcloud.com', 'name' => 'Armas López Tatiana de Fátima Lucía', 'role' => 'administrador', 'cargo' => 'Administradora'],
    ['email' => 'documental@legalcloud.com', 'name' => 'Arteaga Benites Almendra Nicolle', 'role' => 'responsable_documental', 'cargo' => 'Responsable documental'],
    ['email' => 'analista@legalcloud.com', 'name' => 'Domínguez Alba Carlos Daniel', 'role' => 'especialista', 'cargo' => 'Especialista / Analista'],
    ['email' => 'consultor@legalcloud.com', 'name' => 'Lozano Díaz Omar Jack', 'role' => 'consultor', 'cargo' => 'Usuario consultor'],
    ['email' => 'williams@legalcloud.com', 'name' => 'Segura Venturo Williams Aaron', 'role' => 'administrador', 'cargo' => 'Administrador'],
];

foreach ($usuarios as $datos) {
    User::updateOrCreate(
        ['email' => $datos['email']],
        [
            'name' => $datos['name'],
            'password' => Hash::make('password'),
            'role' => $datos['role'],
            'cargo' => $datos['cargo'],
            'activo' => true,
            'email_verified_at' => now(),
        ]
    );
    echo $datos['email'].' => '.$datos['name'].PHP_EOL;
}
