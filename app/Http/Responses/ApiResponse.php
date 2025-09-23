<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
    /**
     * Return a success response
     */
    public static function success(
        $data = null,
        string $message = 'Operation completed successfully',
        int $statusCode = 200,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return an error response
     */
    public static function error(
        string $message = 'An error occurred',
        int $statusCode = 400,
        $errors = null,
        $data = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a paginated response
     */
    public static function paginated(
        LengthAwarePaginator $paginator,
        string $message = 'Data retrieved successfully',
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more_pages' => $paginator->hasMorePages(),
                'links' => [
                    'first' => $paginator->url(1),
                    'last' => $paginator->url($paginator->lastPage()),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ]
            ],
            'timestamp' => now()->toISOString(),
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response);
    }

    /**
     * Return a resource response
     */
    public static function resource(
        JsonResource $resource,
        string $message = 'Resource retrieved successfully',
        int $statusCode = 200,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $resource,
            'timestamp' => now()->toISOString(),
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a validation error response
     */
    public static function validationError(
        array $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return self::error($message, 422, $errors);
    }

    /**
     * Return an unauthorized response
     */
    public static function unauthorized(
        string $message = 'Unauthorized access'
    ): JsonResponse {
        return self::error($message, 401);
    }

    /**
     * Return a forbidden response
     */
    public static function forbidden(
        string $message = 'Access forbidden'
    ): JsonResponse {
        return self::error($message, 403);
    }

    /**
     * Return a not found response
     */
    public static function notFound(
        string $message = 'Resource not found'
    ): JsonResponse {
        return self::error($message, 404);
    }

    /**
     * Return a server error response
     */
    public static function serverError(
        string $message = 'Internal server error'
    ): JsonResponse {
        return self::error($message, 500);
    }

    /**
     * Return a created response
     */
    public static function created(
        $data = null,
        string $message = 'Resource created successfully'
    ): JsonResponse {
        return self::success($data, $message, 201);
    }

    /**
     * Return a no content response
     */
    public static function noContent(
        string $message = 'Operation completed successfully'
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ], 204);
    }
}
