<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Support\LegalCloudApi;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'sin.consultor' => \App\Http\Middleware\DenyConsultorAdminModules::class,
            'api.v1' => \App\Http\Middleware\AuthenticateApiV1::class,
        ]);
        $middleware->api(prepend: [
            \App\Http\Middleware\ApiRequestId::class,
        ]);
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return LegalCloudApi::problema(422, 'Entidad improcesable', 'Revise los campos enviados.', $e->errors());
            }

            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return LegalCloudApi::problema(404, 'No encontrado', 'El recurso no existe.');
            }

            if ($e instanceof AuthenticationException) {
                return LegalCloudApi::problema(401, 'No autenticado', 'Token ausente o inválido.');
            }

            if ($e instanceof AccessDeniedHttpException) {
                return LegalCloudApi::problema(403, 'Prohibido', $e->getMessage() ?: 'No autorizado.');
            }

            return null;
        });
    })->create();
