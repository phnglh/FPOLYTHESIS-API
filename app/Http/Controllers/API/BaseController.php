<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    /**
     * Success response
     */
    public function successResponse($data = null, $message = 'Success', $statusCode = 200)
    {
        return response()->json([
            'status' => 'success',
            'status_code' => $statusCode,
            'message' => $message,
            'data' => $data,
            'errors' => null,
            'error_code' => null,
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
            'data' => null,
            'errors' => $errors,
            'error_code' => $errorCode,
            'timestamp' => now()->timestamp,
        ], $statusCode);
    }

    /**
     * Paginated response
     */
    public function paginatedResponse($resourceCollection, $message = 'Success')
    {
        $resourceResponse = $resourceCollection->response()->getData(true);

        return response()->json([
            'status' => 'success',
            'status_code' => 200,
            'message' => $message,
            'data' => $resourceResponse['data'],
            'links' => $resourceResponse['links'],
            'meta' => $resourceResponse['meta'],
            'errors' => null,
            'error_code' => null,
            'timestamp' => now()->timestamp,
        ]);
    }
}
