<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Http\JsonResponse;
use Throwable;

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
            //
        });

        $this->renderable(function (Throwable $e, $request) {
            // Se a requisição espera JSON ou está na rota da API
            if ($request->expectsJson() || $request->is('api/*')) {

                // Erro de validação
                if ($e instanceof ValidationException) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Validation errors',
                        'errors' => $e->validator->errors()
                    ], 422);
                }

                // Erro de autenticação
                if ($e instanceof AuthenticationException) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Unauthenticated',
                        'errors' => ['auth' => 'You must be logged in to access this resource']
                    ], 401);
                }

                // Modelo não encontrado
                if ($e instanceof ModelNotFoundException) {
                    $model = strtolower(class_basename($e->getModel()));
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Resource not found',
                        'errors' => ["$model" => "No $model found with the specified ID"]
                    ], 404);
                }

                // Rota não encontrada
                if ($e instanceof NotFoundHttpException) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Route not found',
                        'errors' => ['route' => 'The requested URL was not found']
                    ], 404);
                }

                // Método HTTP não permitido
                if ($e instanceof MethodNotAllowedHttpException) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Method not allowed',
                        'errors' => ['method' => 'The HTTP method used is not allowed for this route']
                    ], 405);
                }

                // Outros erros
                return new JsonResponse([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Server Error',
                    'errors' => (config('app.debug')) ? [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTrace()
                    ] : ['server' => 'An unexpected error occurred']
                ], $this->isHttpException($e) ? $e->getStatusCode() : 500);
            }

            return null;
        });
    }
}
