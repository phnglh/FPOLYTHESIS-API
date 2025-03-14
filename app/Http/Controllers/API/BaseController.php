<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    /**
     * Success response
     */
    public function successResponse($data = null, $message = 'Success', $statusCode = 200, $meta = null)
    {
        return response()->json([
            'status' => 'success',
            'status_code' => $statusCode,
            'message' => $message,
            'error_code' => null,
            'data' => $data,
            'errors' => null,
            'meta' => $meta,
            'timestamp' => now()->timestamp,
        ], $statusCode);
    }

    /**
     * Error response
     */
    public function errorResponse($errorCode = 'UNKNOWN_ERROR', $message = 'Error', $statusCode = 400, $errors = null)
    {
        return response()->json([
            'status' => 'error',
            'status_code' => $statusCode,
            'message' => $message,
            'error_code' => $errorCode,
            'data' => null,
            'errors' => $errors,
            'meta' => null,
            'timestamp' => now()->timestamp,
        ], $statusCode);
    }

    /**
     * Paginated response
     */
    public function paginatedResponse($data, $message = 'Success')
    {
        return response()->json([
            'status' => 'success',
            'status_code' => 200,
            'message' => $message,
            'error_code' => null,
            'data' => $data->items(),
            'errors' => null,
            'meta' => [
                'pagination' => [
                    'total' => $data->total(),
                    'per_page' => $data->perPage(),
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'from' => $data->firstItem(),
                    'to' => $data->lastItem()
                ]
            ],
            'timestamp' => now()->timestamp,
        ]);
    }
}
