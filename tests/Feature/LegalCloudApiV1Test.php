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
        ->assertJsonStructure(['datos', 'paginacion' => ['limit', 'pagina', 'total', 'paginas']]);

    $this->withToken($token)
        ->getJson('/api/v1/usuarios?q=admin&limite=5&pagina=1')
        ->assertOk()
        ->assertJsonPath('paginacion.limit', 5);
});

test('el consultor puede cargar un pdf por la api', function () {
    $this->seed(LegalCloudSeeder::class);

    $token = $this->postJson('/api/v1/auth/iniciar-sesion', [
        'correo' => 'consultor@legalcloud.com',
        'contrasena' => 'password',
    ])->json('accessToken');

    expect($this->withToken($token)->getJson('/api/v1/auth/yo')->json('permisos'))
        ->toContain('documentos.crear');

    $expedienteId = $this->withToken($token)
        ->getJson('/api/v1/expedientes')
        ->assertOk()
        ->json('datos.0.id');

    $pdf = "%PDF-1.1\nstream\n(Texto de prueba del consultor) Tj\nendstream\n%%EOF";
    $archivo = Illuminate\Http\UploadedFile::fake()->createWithContent('consulta.pdf', $pdf);

    $this->withToken($token)
        ->post("/api/v1/expedientes/{$expedienteId}/documentos/archivo", [
            'titulo' => 'PDF del consultor',
            'tipo' => 'oficio',
            'archivo' => $archivo,
        ], ['Accept' => 'application/json'])
        ->assertCreated()
        ->assertJsonPath('titulo', 'PDF del consultor');
});

test('el administrador puede cargar un pdf y aparece en el expediente', function () {
    $this->seed(LegalCloudSeeder::class);

    $token = $this->postJson('/api/v1/auth/iniciar-sesion', [
        'correo' => 'admin@legalcloud.com',
        'contrasena' => 'password',
    ])->json('accessToken');

    $expedienteId = $this->withToken($token)
        ->getJson('/api/v1/expedientes')
        ->assertOk()
        ->json('datos.0.id');

    $pdf = "%PDF-1.1\nstream\n(Texto de prueba del reglamento municipal) Tj\nendstream\n%%EOF";
    $archivo = Illuminate\Http\UploadedFile::fake()->createWithContent('norma.pdf', $pdf);

    $this->withToken($token)
        ->post("/api/v1/expedientes/{$expedienteId}/documentos/archivo", [
            'titulo' => 'Norma de prueba',
            'tipo' => 'reglamento',
            'archivo' => $archivo,
        ], ['Accept' => 'application/json'])
        ->assertCreated()
        ->assertJsonPath('titulo', 'Norma de prueba');

    $this->withToken($token)
        ->getJson("/api/v1/expedientes/{$expedienteId}/documentos")
        ->assertOk()
        ->assertJsonFragment(['titulo' => 'Norma de prueba']);
});
