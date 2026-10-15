<?php

use App\Models\User;
use Database\Seeders\LegalCloudSeeder;

test('la api responde salud y autentica al consultor', function () {
    $this->seed(LegalCloudSeeder::class);

    $this->getJson('/api/v1/salud')->assertOk()->assertJsonPath('contrato', 'legalcloud-ai.v1');

    $respuesta = $this->postJson('/api/v1/auth/iniciar-sesion', [
        'correo' => 'consultor@legalcloud.com',
        'contrasena' => 'password',
    ]);

    $respuesta->assertOk()->assertJsonPath('tokenType', 'Bearer');

    $token = $respuesta->json('accessToken');

    $this->withToken($token)
        ->getJson('/api/v1/auth/yo')
        ->assertOk()
        ->assertJsonPath('correo', 'consultor@legalcloud.com');

    $this->withToken($token)
        ->getJson('/api/v1/usuarios')
        ->assertForbidden();

    $this->withToken($token)
        ->getJson('/api/v1/auditoria')
        ->assertForbidden();

    $this->withToken($token)
        ->getJson('/api/v1/dashboard')
        ->assertOk()
        ->assertJsonStructure(['expedientesPorEstado', 'documentosPorProcesamiento', 'generadoEn']);
});

test('el administrador puede listar usuarios por la api', function () {
    $this->seed(LegalCloudSeeder::class);

    $admin = User::query()->where('email', 'admin@legalcloud.com')->firstOrFail();

    $token = $this->postJson('/api/v1/auth/iniciar-sesion', [
        'correo' => $admin->email,
        'contrasena' => 'password',
    ])->json('accessToken');

    $this->withToken($token)
        ->getJson('/api/v1/usuarios')
        ->assertOk()
        ->assertJsonStructure(['datos', 'paginacion']);
});
