<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SyncLegacySession::class,
        ]);

        $middleware->alias([
            'modulo' => \App\Http\Middleware\VerificarModulo::class,
        ]);

        $middleware->redirectUsersTo('/inicio');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // ── Respuesta JSON para peticiones AJAX bloqueadas por Rate Limiting ──
        $exceptions->render(function (ThrottleRequestsException $e, $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'resultado' => 'throttled',
                    'message'   => 'Demasiados intentos. Por favor, espera un momento antes de reintentar.',
                ], 429);
            }
        });
    })->create();

