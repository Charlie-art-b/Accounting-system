<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (Throwable $e) {
            if ($e instanceof \Illuminate\Database\QueryException) {
                \Illuminate\Support\Facades\Log::error('Error de base de datos en sistema contable: ' . $e->getMessage(), [
                    'sql' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                ]);
            }
        });

        $exceptions->render(function (Throwable $e, $request) {
            if ($e instanceof \Illuminate\Database\QueryException && $request->is('admin/*')) {
                return response()->view('errors.database', ['message' => 'Error interno del servidor. Contacte al administrador.'], 500);
            }

            if ($e instanceof \Illuminate\Validation\ValidationException && $request->wantsJson()) {
                return response()->json([
                    'message' => 'Datos inválidos',
                    'errors' => $e->errors(),
                ], 422);
            }
        });
    })->create();
