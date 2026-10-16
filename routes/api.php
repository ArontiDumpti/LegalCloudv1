<?php

use App\Http\Controllers\Api\V1\ArquitecturaLegalController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DocumentoApiController;
use App\Http\Controllers\Api\V1\ExpedienteApiController;
use App\Http\Controllers\Api\V1\InteligenciaApiController;
use App\Http\Controllers\Api\V1\NotificacionAuditoriaApiController;
use App\Http\Controllers\Api\V1\OrganizacionController;
use App\Http\Controllers\Api\V1\UsuarioApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/salud', [OrganizacionController::class, 'salud']);
    Route::post('/auth/iniciar-sesion', [AuthController::class, 'iniciarSesion']);
    Route::post('/auth/registrar', [AuthController::class, 'registrar']);
    Route::post('/auth/renovar', [AuthController::class, 'renovar']);
    Route::post('/auth/aceptar-invitacion', [AuthController::class, 'aceptarInvitacion']);
    Route::post('/auth/recuperar-contrasena', [AuthController::class, 'recuperarContrasena']);
    Route::put('/cargas/{token}', [DocumentoApiController::class, 'recibirCarga']);

    Route::middleware('api.v1')->group(function () {
        Route::post('/auth/cerrar-sesion', [AuthController::class, 'cerrarSesion']);
        Route::get('/auth/yo', [AuthController::class, 'yo']);
        Route::post('/auth/cambiar-contrasena', [AuthController::class, 'cambiarContrasena']);

        Route::get('/permisos', [OrganizacionController::class, 'permisos']);
        Route::get('/roles', [OrganizacionController::class, 'roles']);
        Route::get('/roles/{rolId}', [OrganizacionController::class, 'rol']);
        Route::patch('/roles/{rolId}', [OrganizacionController::class, 'actualizarRol']);
        Route::put('/roles/{rolId}/permisos', [OrganizacionController::class, 'actualizarPermisosRol']);

        Route::get('/usuarios', [UsuarioApiController::class, 'index']);
        Route::post('/usuarios', [UsuarioApiController::class, 'store']);
        Route::get('/usuarios/{usuarioId}', [UsuarioApiController::class, 'show']);
        Route::patch('/usuarios/{usuarioId}', [UsuarioApiController::class, 'update']);
        Route::put('/usuarios/{usuarioId}/roles', [UsuarioApiController::class, 'reemplazarRoles']);
        Route::post('/usuarios/{usuarioId}/suspender', [UsuarioApiController::class, 'suspender']);
        Route::post('/usuarios/{usuarioId}/reactivar', [UsuarioApiController::class, 'reactivar']);

        Route::get('/categorias', [OrganizacionController::class, 'categorias']);
        Route::post('/categorias', [OrganizacionController::class, 'crearCategoria']);
        Route::get('/categorias/{categoriaId}', [OrganizacionController::class, 'categoria']);
        Route::patch('/categorias/{categoriaId}', [OrganizacionController::class, 'actualizarCategoria']);
        Route::post('/categorias/{categoriaId}/desactivar', [OrganizacionController::class, 'desactivarCategoria']);

        Route::get('/expedientes', [ExpedienteApiController::class, 'index']);
        Route::post('/expedientes', [ExpedienteApiController::class, 'store']);
        Route::get('/expedientes/{expedienteId}', [ExpedienteApiController::class, 'show']);
        Route::patch('/expedientes/{expedienteId}', [ExpedienteApiController::class, 'update']);
        Route::post('/expedientes/{expedienteId}/transiciones', [ExpedienteApiController::class, 'transicionar']);
        Route::get('/expedientes/{expedienteId}/participantes', [ExpedienteApiController::class, 'participantes']);
        Route::post('/expedientes/{expedienteId}/participantes', [ExpedienteApiController::class, 'asignarParticipante']);
        Route::delete('/expedientes/{expedienteId}/participantes/{usuarioId}', [ExpedienteApiController::class, 'quitarParticipante']);
        Route::get('/expedientes/{expedienteId}/trazabilidad', [ExpedienteApiController::class, 'trazabilidad']);
        Route::get('/expedientes/{expedienteId}/documentos', [ExpedienteApiController::class, 'documentos']);
        Route::post('/expedientes/{expedienteId}/documentos/archivo', [DocumentoApiController::class, 'subirDirecto']);
        Route::post('/expedientes/{expedienteId}/documentos', [DocumentoApiController::class, 'iniciar']);

        Route::post('/documentos/archivo', [DocumentoApiController::class, 'subirAlAcervo']);
        Route::get('/documentos/{documentoId}', [DocumentoApiController::class, 'show']);
        Route::patch('/documentos/{documentoId}', [DocumentoApiController::class, 'update']);
        Route::post('/documentos/{documentoId}/archivar', [DocumentoApiController::class, 'archivar']);
        Route::get('/documentos/{documentoId}/versiones', [DocumentoApiController::class, 'versiones']);
        Route::get('/documentos/{documentoId}/versiones/{versionId}', [DocumentoApiController::class, 'version']);
        Route::post('/documentos/{documentoId}/versiones/{versionId}/confirmar', [DocumentoApiController::class, 'confirmar']);
        Route::post('/documentos/{documentoId}/versiones/{versionId}/restaurar', [DocumentoApiController::class, 'restaurar']);
        Route::get('/documentos/{documentoId}/versiones/{versionId}/descarga', [DocumentoApiController::class, 'descarga']);
        Route::get('/documentos/{documentoId}/versiones/{versionId}/texto', [DocumentoApiController::class, 'texto']);
        Route::post('/documentos/{documentoId}/versiones/{versionId}/procesamiento', [DocumentoApiController::class, 'reprocesar']);
        Route::get('/trabajos/{trabajoId}', [DocumentoApiController::class, 'trabajo']);

        Route::get('/busquedas', [InteligenciaApiController::class, 'listarBusquedas']);
        Route::post('/busquedas', [InteligenciaApiController::class, 'crearBusqueda']);
        Route::get('/busquedas/{busquedaId}', [InteligenciaApiController::class, 'obtenerBusqueda']);

        Route::get('/conversaciones', [InteligenciaApiController::class, 'listarConversaciones']);
        Route::post('/conversaciones', [InteligenciaApiController::class, 'crearConversacion']);
        Route::get('/conversaciones/{conversacionId}', [InteligenciaApiController::class, 'obtenerConversacion']);
        Route::post('/conversaciones/{conversacionId}/mensajes', [InteligenciaApiController::class, 'crearMensaje']);

        Route::post('/ingestions', [ArquitecturaLegalController::class, 'ingestions']);
        Route::post('/queries', [ArquitecturaLegalController::class, 'queries']);

        Route::post('/resumenes', [InteligenciaApiController::class, 'crearResumen']);
        Route::get('/resumenes/{resumenId}', [InteligenciaApiController::class, 'obtenerResumen']);

        Route::get('/notificaciones', [NotificacionAuditoriaApiController::class, 'notificaciones']);
        Route::get('/notificaciones/no-leidas/conteo', [NotificacionAuditoriaApiController::class, 'noLeidas']);
        Route::post('/notificaciones/{notificacionId}/lectura', [NotificacionAuditoriaApiController::class, 'marcarLectura']);
        Route::post('/notificaciones/lectura', [NotificacionAuditoriaApiController::class, 'marcarTodas']);

        Route::get('/auditoria', [NotificacionAuditoriaApiController::class, 'auditoria']);
        Route::get('/auditoria/{eventoId}', [NotificacionAuditoriaApiController::class, 'evento']);

        Route::get('/dashboard', [OrganizacionController::class, 'dashboard']);
    });
});
