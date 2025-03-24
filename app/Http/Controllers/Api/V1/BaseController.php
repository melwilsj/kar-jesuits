<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    /**
     * Return a success response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    public function successResponse($data, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $this->getPaginationMeta($data)
        ], $code);
    }

    /**
     * Return an error response.
     *
     * @param string $message
     * @param array $errors
     * @param int $code
     * @return JsonResponse
     */
    public function errorResponse(string $message, array $errors = [], int $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }

    /**
     * Get pagination metadata if the data is paginated.
     *
     * @param mixed $data
     * @return array|null
     */
    private function getPaginationMeta($data): ?array
    {
        if (is_object($data) && method_exists($data, 'toArray') && isset($data->resource) && method_exists($data->resource, 'hasPages')) {
            return [
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'last_page' => $data->lastPage()
            ];
        }
        
        return null;
    }
}