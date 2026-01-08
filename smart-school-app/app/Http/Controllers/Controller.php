<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

/**
 * Base Controller
 * 
 * Prompt 294: Add Base Controller Response Helpers
 * 
 * Provides standardized response methods for both web and JSON responses.
 * All controllers should extend this class to ensure consistent response formats.
 */
abstract class Controller
{
    /**
     * HTTP Status Codes
     */
    protected const HTTP_OK = 200;
    protected const HTTP_CREATED = 201;
    protected const HTTP_NO_CONTENT = 204;
    protected const HTTP_BAD_REQUEST = 400;
    protected const HTTP_UNAUTHORIZED = 401;
    protected const HTTP_FORBIDDEN = 403;
    protected const HTTP_NOT_FOUND = 404;
    protected const HTTP_UNPROCESSABLE_ENTITY = 422;
    protected const HTTP_INTERNAL_SERVER_ERROR = 500;

    /**
     * Return a success response.
     *
     * @param mixed $data The data to return
     * @param string $message Success message
     * @param array $meta Additional metadata
     * @param int $code HTTP status code
     * @return JsonResponse
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        array $meta = [],
        int $code = self::HTTP_OK
    ): JsonResponse {
        $response = [
            'status' => 'success',
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a created response.
     *
     * @param mixed $data The created resource data
     * @param string $message Success message
     * @return JsonResponse
     */
    protected function createdResponse(
        mixed $data = null,
        string $message = 'Resource created successfully'
    ): JsonResponse {
        return $this->successResponse($data, $message, [], self::HTTP_CREATED);
    }

    /**
     * Return an error response.
     *
     * @param string $message Error message
     * @param array $errors Validation or other errors
     * @param int $code HTTP status code
     * @return JsonResponse
     */
    protected function errorResponse(
        string $message = 'An error occurred',
        array $errors = [],
        int $code = self::HTTP_UNPROCESSABLE_ENTITY
    ): JsonResponse {
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
     * Return a not found response.
     *
     * @param string $message Not found message
     * @return JsonResponse
     */
    protected function notFoundResponse(
        string $message = 'Resource not found'
    ): JsonResponse {
        return $this->errorResponse($message, [], self::HTTP_NOT_FOUND);
    }

    /**
     * Return an unauthorized response.
     *
     * @param string $message Unauthorized message
     * @return JsonResponse
     */
    protected function unauthorizedResponse(
        string $message = 'Unauthorized'
    ): JsonResponse {
        return $this->errorResponse($message, [], self::HTTP_UNAUTHORIZED);
    }

    /**
     * Return a forbidden response.
     *
     * @param string $message Forbidden message
     * @return JsonResponse
     */
    protected function forbiddenResponse(
        string $message = 'Access denied'
    ): JsonResponse {
        return $this->errorResponse($message, [], self::HTTP_FORBIDDEN);
    }

    /**
     * Return a no content response.
     *
     * @return Response
     */
    protected function noContentResponse(): Response
    {
        return response()->noContent();
    }

    /**
     * Redirect with a flash message.
     *
     * @param string $route Route name
     * @param string $message Flash message
     * @param string $type Message type (success, error, warning, info)
     * @param array $routeParams Route parameters
     * @return RedirectResponse
     */
    protected function redirectWithMessage(
        string $route,
        string $message,
        string $type = 'success',
        array $routeParams = []
    ): RedirectResponse {
        return redirect()
            ->route($route, $routeParams)
            ->with($type, $message);
    }

    /**
     * Redirect back with a flash message.
     *
     * @param string $message Flash message
     * @param string $type Message type (success, error, warning, info)
     * @return RedirectResponse
     */
    protected function backWithMessage(
        string $message,
        string $type = 'success'
    ): RedirectResponse {
        return back()->with($type, $message);
    }

    /**
     * Redirect back with errors and input.
     *
     * @param array $errors Validation errors
     * @param string|null $message Error message
     * @return RedirectResponse
     */
    protected function backWithErrors(
        array $errors,
        ?string $message = null
    ): RedirectResponse {
        $redirect = back()->withErrors($errors)->withInput();

        if ($message) {
            $redirect->with('error', $message);
        }

        return $redirect;
    }

    /**
     * Return appropriate response based on request type.
     * 
     * For AJAX/API requests, returns JSON. For web requests, redirects.
     *
     * @param mixed $data Data for JSON response
     * @param string $message Success message
     * @param string $route Route for redirect
     * @param array $routeParams Route parameters
     * @return JsonResponse|RedirectResponse
     */
    protected function respondWithSuccess(
        mixed $data = null,
        string $message = 'Success',
        string $route = '',
        array $routeParams = []
    ): JsonResponse|RedirectResponse {
        if (request()->expectsJson()) {
            return $this->successResponse($data, $message);
        }

        if ($route) {
            return $this->redirectWithMessage($route, $message, 'success', $routeParams);
        }

        return $this->backWithMessage($message, 'success');
    }

    /**
     * Return appropriate error response based on request type.
     *
     * @param string $message Error message
     * @param array $errors Validation errors
     * @param int $code HTTP status code for JSON
     * @return JsonResponse|RedirectResponse
     */
    protected function respondWithError(
        string $message = 'An error occurred',
        array $errors = [],
        int $code = self::HTTP_UNPROCESSABLE_ENTITY
    ): JsonResponse|RedirectResponse {
        if (request()->expectsJson()) {
            return $this->errorResponse($message, $errors, $code);
        }

        if (!empty($errors)) {
            return $this->backWithErrors($errors, $message);
        }

        return $this->backWithMessage($message, 'error');
    }

    /**
     * Return a paginated response.
     *
     * @param mixed $paginator Laravel paginator instance
     * @param string $message Success message
     * @return JsonResponse
     */
    protected function paginatedResponse(
        mixed $paginator,
        string $message = 'Success'
    ): JsonResponse {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Return a Select2-compatible dropdown response.
     *
     * @param array $results Array of items with 'id' and 'text' keys
     * @param bool $more Whether there are more results
     * @return JsonResponse
     */
    protected function dropdownResponse(
        array $results,
        bool $more = false
    ): JsonResponse {
        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => $more,
            ],
        ]);
    }

    /**
     * Return a DataTables-compatible response.
     *
     * @param mixed $paginator Laravel paginator instance
     * @param int $draw DataTables draw counter
     * @return JsonResponse
     */
    protected function dataTablesResponse(
        mixed $paginator,
        int $draw = 1
    ): JsonResponse {
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $paginator->total(),
            'recordsFiltered' => $paginator->total(),
            'data' => $paginator->items(),
        ]);
    }

    /**
     * Return a Chart.js-compatible response.
     *
     * @param array $labels Chart labels
     * @param array $datasets Chart datasets
     * @return JsonResponse
     */
    protected function chartResponse(
        array $labels,
        array $datasets
    ): JsonResponse {
        return response()->json([
            'status' => 'success',
            'data' => [
                'labels' => $labels,
                'datasets' => $datasets,
            ],
        ]);
    }
}
