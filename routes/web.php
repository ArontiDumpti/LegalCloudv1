<?php

use App\Http\Controllers\AsistenteController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\BusquedaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\ExpedienteController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\OcrController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/expedientes', [ExpedienteController::class, 'index'])->name('expedientes.index');
    Route::post('/expedientes', [ExpedienteController::class, 'store'])->name('expedientes.store');
    Route::get('/expedientes/{expediente}', [ExpedienteController::class, 'show'])->name('expedientes.show');

    Route::get('/documentos', [DocumentoController::class, 'index'])->name('documentos.index');
    Route::post('/documentos', [DocumentoController::class, 'store'])->name('documentos.store');
    Route::post('/documentos/ejemplo', [DocumentoController::class, 'storeEjemplo'])->name('documentos.ejemplo');
    Route::get('/documentos/{documento}/archivo', [DocumentoController::class, 'archivo'])->name('documentos.archivo');
    Route::get('/documentos/{documento}', [DocumentoController::class, 'show'])->name('documentos.show');

    Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias.index');
    Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store');

    Route::get('/busqueda', [BusquedaController::class, 'index'])->name('busqueda.index');

    Route::get('/asistente', [AsistenteController::class, 'index'])->name('asistente.index');
    Route::post('/asistente', [AsistenteController::class, 'store'])->name('asistente.store');

    Route::get('/ocr', [OcrController::class, 'index'])->name('ocr.index');
    Route::post('/ocr/{documento}', [OcrController::class, 'procesar'])->name('ocr.procesar');

    Route::middleware('sin.consultor')->group(function () {
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
    });

    Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::post('/notificaciones/{notificacion}/leer', [NotificacionController::class, 'leer'])->name('notificaciones.leer');

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
