<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Handler extends ExceptionHandler
{
    // ... other methods ...

    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'errors' => []
            ], 401);
        }

        return redirect()->guest($exception->redirectTo() ?? route('login'));
    }

    protected function renderApiException($request, \Exception $exception)
    {
        if ($exception instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $exception->errors()
            ], 422);
        }

        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
                'errors' => []
            ], 404);
        }

        // Add more exception types as needed

        return response()->json([
            'success' => false,
            'message' => $exception->getMessage(),
            'errors' => []
        ], 500);
    }
} 