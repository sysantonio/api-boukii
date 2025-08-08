<?php

namespace App\V5\Exceptions;

use App\V5\Logging\V5Logger;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class V5ExceptionHandler
{
    /**
     * Handle V5 exceptions and return appropriate JSON responses
     */
    public function handle(\Throwable $exception, Request $request): JsonResponse
    {
        // Log the exception
        $this->logException($exception, $request);

        // Handle custom V5 exceptions
        if ($exception instanceof V5Exception) {
            return $exception->toResponse();
        }

        // Handle Laravel validation exceptions
        if ($exception instanceof ValidationException) {
            return $this->handleValidationException($exception);
        }

        // Handle model not found exceptions
        if ($exception instanceof ModelNotFoundException) {
            return $this->handleModelNotFoundException($exception);
        }

        // Handle 404 exceptions
        if ($exception instanceof NotFoundHttpException) {
            return $this->handleNotFoundHttpException();
        }

        // Handle method not allowed exceptions
        if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->handleMethodNotAllowedException();
        }

        // Handle generic exceptions
        return $this->handleGenericException($exception);
    }

    private function logException(\Throwable $exception, Request $request): void
    {
        $context = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => $request->user()?->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'season_id' => $request->get('season_id'),
            'school_id' => $request->get('school_id'),
        ];

        if ($exception instanceof V5Exception) {
            $context['error_code'] = $exception->getErrorCode();
            $context['v5_context'] = $exception->getContext();
        }

        // Use V5Logger for structured logging
        V5Logger::logSystemError($exception, $context);

        // Also log validation errors specifically
        if ($exception instanceof ValidationException) {
            V5Logger::logValidationError($exception->errors(), $context);
        }
    }

    private function handleValidationException(ValidationException $exception): JsonResponse
    {
        return response()->json([
            'error' => true,
            'code' => 'VALIDATION_ERROR',
            'message' => __('exceptions.validation.failed'),
            'errors' => $exception->errors(),
            'timestamp' => now()->toISOString(),
        ], 422);
    }

    private function handleModelNotFoundException(ModelNotFoundException $exception): JsonResponse
    {
        $model = class_basename($exception->getModel());

        return response()->json([
            'error' => true,
            'code' => 'RESOURCE_NOT_FOUND',
            'message' => __('exceptions.resource.not_found', ['resource' => strtolower($model)]),
            'timestamp' => now()->toISOString(),
        ], 404);
    }

    private function handleNotFoundHttpException(): JsonResponse
    {
        return response()->json([
            'error' => true,
            'code' => 'ROUTE_NOT_FOUND',
            'message' => __('exceptions.route.not_found'),
            'timestamp' => now()->toISOString(),
        ], 404);
    }

    private function handleMethodNotAllowedException(): JsonResponse
    {
        return response()->json([
            'error' => true,
            'code' => 'METHOD_NOT_ALLOWED',
            'message' => __('exceptions.route.method_not_allowed'),
            'timestamp' => now()->toISOString(),
        ], 405);
    }

    private function handleGenericException(\Throwable $exception): JsonResponse
    {
        $response = [
            'error' => true,
            'code' => 'INTERNAL_SERVER_ERROR',
            'message' => __('exceptions.server.internal_error'),
            'timestamp' => now()->toISOString(),
        ];

        if (config('app.debug')) {
            $response['debug'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        return response()->json($response, 500);
    }
}
