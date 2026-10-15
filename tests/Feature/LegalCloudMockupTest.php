<?php

use App\Models\User;
use Database\Seeders\LegalCloudSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('legalcloud mockup pages and sample rag work', function () {
    $this->seed(LegalCloudSeeder::class);

    $user = User::where('email', 'consultor@legalcloud.com')->firstOrFail();

    $this->actingAs($user);

    $this->get('/dashboard')->assertOk()->assertInertia(fn (Assert $page) => $page->component('dashboard'));
    $this->get('/expedientes')->assertOk();
    $this->get('/documentos')->assertOk();
    $this->get('/documentos/1')->assertOk();
    $this->get('/categorias')->assertOk();
    $this->get('/busqueda?q=requisitos')->assertOk();
    $this->get('/ocr')->assertOk();
    $this->get('/asistente')->assertOk();
    $this->get('/usuarios')->assertForbidden();
    $this->get('/notificaciones')->assertOk();
    $this->get('/auditoria')->assertForbidden();

    $this->post('/asistente', [
        'pregunta' => '¿Cuáles son los requisitos establecidos para realizar determinado procedimiento?',
    ])->assertRedirect();

    $this->assertDatabaseHas('consultas_rag', [
        'user_id' => $user->id,
        'pregunta' => '¿Cuáles son los requisitos establecidos para realizar determinado procedimiento?',
    ]);
});

test('can upload a sample pdf and extract its text', function () {
    $this->seed(LegalCloudSeeder::class);

    $user = User::where('email', 'consultor@legalcloud.com')->firstOrFail();
    $expediente = \App\Models\Expediente::firstOrFail();
    $binario = \App\Support\PdfSimple::generar('Reglamento de prueba', \App\Support\DocumentosDemo::reglamentoTramites());

    expect($binario)->toStartWith('%PDF');
    expect(\App\Support\PdfSimple::extraer($binario))->toContain('Artículo 12');

    $archivo = \Illuminate\Http\UploadedFile::fake()->createWithContent('reglamento.pdf', $binario);

    $this->actingAs($user)->post('/documentos', [
        'expediente_id' => $expediente->id,
        'titulo' => 'Reglamento subido',
        'tipo' => 'reglamento',
        'archivo' => $archivo,
    ])->assertRedirect();

    $this->assertDatabaseHas('documentos', [
        'titulo' => 'Reglamento subido',
        'ocr_estado' => 'procesado',
        'mime' => 'application/pdf',
    ]);
});

test('one click example pdf upload works', function () {
    $this->seed(LegalCloudSeeder::class);
    $user = User::where('email', 'consultor@legalcloud.com')->firstOrFail();

    $this->actingAs($user)->post('/documentos/ejemplo')->assertRedirect();

    $this->assertDatabaseHas('documentos', [
        'titulo' => 'Reglamento General de Trámites Administrativos (subido)',
        'ocr_estado' => 'procesado',
    ]);
});

test('admin can open users and audit pages', function () {
    $this->seed(LegalCloudSeeder::class);
    $admin = User::where('email', 'admin@legalcloud.com')->firstOrFail();

    $this->actingAs($admin);
    $this->get('/usuarios')->assertOk();
    $this->get('/auditoria')->assertOk();
});
