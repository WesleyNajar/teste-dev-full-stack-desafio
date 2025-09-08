<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Illuminate\Http\JsonResponse;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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

        // Tratar exceções de validação
        $this->renderable(function (ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $e->errors(),
                    'details' => 'Verifique os campos destacados e corrija os erros'
                ], 422);
            }
        });

        // Tratar exceções de modelo não encontrado
        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Recurso não encontrado',
                    'details' => 'O recurso solicitado não existe no sistema'
                ], 404);
            }
        });

        // Tratar exceções de rota não encontrada
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rota não encontrada',
                    'details' => 'A URL solicitada não foi encontrada'
                ], 404);
            }
        });

        // Tratar exceções de método não permitido
        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Método não permitido',
                    'details' => 'O método HTTP utilizado não é permitido para esta rota'
                ], 405);
            }
        });

        // Tratar exceções de autenticação
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não autenticado',
                    'details' => 'Você precisa estar autenticado para acessar este recurso'
                ], 401);
            }
        });

        // Tratar exceções de autorização
        $this->renderable(function (AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado',
                    'details' => 'Você não tem permissão para acessar este recurso'
                ], 403);
            }
        });

        // Tratar exceções de banco de dados
        $this->renderable(function (QueryException $e, $request) {
            if ($request->expectsJson()) {
                // Log do erro para debug
                \Log::error('Database Query Exception: ' . $e->getMessage(), [
                    'sql' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Erro no banco de dados',
                    'details' => 'Ocorreu um erro ao processar sua solicitação'
                ], 500);
            }
        });

        // Tratar exceções HTTP genéricas
        $this->renderable(function (HttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Erro HTTP',
                    'details' => 'Ocorreu um erro durante o processamento da requisição'
                ], $e->getStatusCode());
            }
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e): JsonResponse|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
    {
        // Se for uma requisição JSON e não foi tratada pelos renderables acima
        if ($request->expectsJson() && !$this->isHttpException($e)) {
            // Log do erro para debug
            \Log::error('Unhandled Exception: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'details' => 'Ocorreu um erro inesperado. Nossa equipe foi notificada.'
            ], 500);
        }

        return parent::render($request, $e);
    }
}
