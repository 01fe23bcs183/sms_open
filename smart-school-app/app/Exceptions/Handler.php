<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Exception Handler
 * 
 * Prompt 297: Standardize Validation Errors for Web and JSON
 * 
 * Provides consistent error responses for both web and API requests.
 * Detects request type and returns appropriate format.
 */
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

        // Handle validation exceptions
        $this->renderable(function (ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->validationErrorResponse($e);
            }
        });

        // Handle model not found exceptions
        $this->renderable(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $modelName = class_basename($e->getModel());
                return $this->errorResponse(
                    "{$modelName} not found",
                    [],
                    404
                );
            }
        });

        // Handle 404 exceptions
        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->errorResponse(
                    'Resource not found',
                    [],
                    404
                );
            }
        });

        // Handle authorization exceptions
        $this->renderable(function (AuthorizationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->errorResponse(
                    $e->getMessage() ?: 'You are not authorized to perform this action',
                    [],
                    403
                );
            }
        });

        // Handle authentication exceptions
        $this->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->errorResponse(
                    'Unauthenticated',
                    [],
                    401
                );
            }
        });

        // Handle HTTP exceptions
        $this->renderable(function (HttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->errorResponse(
                    $e->getMessage() ?: 'An error occurred',
                    [],
                    $e->getStatusCode()
                );
            }
        });
    }

    /**
     * Return a validation error response.
     *
     * @param ValidationException $exception
     * @return JsonResponse
     */
    protected function validationErrorResponse(ValidationException $exception): JsonResponse
    {
        $errors = $exception->errors();
        
        // Format errors for consistent structure
        $formattedErrors = [];
        foreach ($errors as $field => $messages) {
            $formattedErrors[$field] = [
                'field' => $field,
                'messages' => $messages,
                'first' => $messages[0] ?? null,
            ];
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $formattedErrors,
            'error_count' => count($formattedErrors),
        ], 422);
    }

    /**
     * Return a generic error response.
     *
     * @param string $message
     * @param array $errors
     * @param int $code
     * @return JsonResponse
     */
    protected function errorResponse(string $message, array $errors = [], int $code = 500): JsonResponse
    {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param Request $request
     * @param AuthenticationException $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|JsonResponse
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->errorResponse('Unauthenticated', [], 401);
        }

        return redirect()->guest(route('login'));
    }
}
