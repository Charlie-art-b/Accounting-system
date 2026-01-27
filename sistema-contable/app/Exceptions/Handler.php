<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Throwable;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Loggear errores específicos
            if ($e instanceof QueryException) {
                Log::error('Error de base de datos: ' . $e->getMessage(), [
                    'sql' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        });

        $this->renderable(function (Throwable $e, $request) {
            // Manejar errores de validación para respuestas JSON
            if ($request->is('api/*') && $e instanceof ValidationException) {
                return response()->json([
                    'message' => 'Errores de validación',
                    'errors' => $e->errors(),
                ], 422);
            }

            // Para errores de BD en el panel admin (Filament)
            if ($e instanceof QueryException && $request->is('admin/*')) {
                return response()->view('errors.database', [], 500);
            }
        });
    }
}