<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

/**
 * Standardized API Response Trait
 *
 * Provides consistent response structure across all API endpoints
 * Following RESTful best practices
 */
trait ApiResponse
{
    /**
     * Send a successful response
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function successResponse($data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        // Merge data at root level for backward compatibility
        if ($data !== null && is_array($data)) {
            $response = array_merge($response, $data);
        }

        $response['meta'] = $this->getMetadata();

        return response()->json($response, $statusCode)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Send an error response
     *
     * @param string $message
     * @param int $statusCode
     * @param mixed $errors
     * @return JsonResponse
     */
    protected function errorResponse(string $message = 'Error occurred', int $statusCode = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        $response['meta'] = $this->getMetadata();

        return response()->json($response, $statusCode);
    }

    /**
     * Send a validation error response
     *
     * @param array $errors
     * @param string $message
     * @return JsonResponse
     */
    protected function validationErrorResponse(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Send a not found response
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Send an unauthorized response
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Send a forbidden response
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Send a server error response
     *
     * @param string $message
     * @param mixed $error
     * @return JsonResponse
     */
    protected function serverErrorResponse(string $message = 'Internal server error', $error = null): JsonResponse
    {
        $errors = null;

        // Only show detailed error in development
        if (config('app.debug') && $error !== null) {
            $errors = [
                'exception' => get_class($error),
                'message' => $error->getMessage(),
                'file' => $error->getFile(),
                'line' => $error->getLine(),
            ];
        }

        return $this->errorResponse($message, 500, $errors);
    }

    /**
     * Send a created response (for POST requests that create resources)
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    protected function createdResponse($data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        // Merge data at root level for backward compatibility
        if ($data !== null && is_array($data)) {
            $response = array_merge($response, $data);
        }

        $response['meta'] = $this->getMetadata();

        return response()->json($response, 201);
    }

    /**
     * Send a no content response (for successful DELETE requests)
     *
     * @return JsonResponse
     */
    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Get metadata for responses
     *
     * @return array
     */
    private function getMetadata(): array
    {
        return [
            'timestamp' => now()->toIso8601String(),
            'api_version' => '1.0',
        ];
    }
}
