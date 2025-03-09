<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Exception;

class ApiException extends Exception
{
    public $errorCode;
    public $statusCode;
    public $errors;

    public function __construct(
        string $message = "Error",
        string $errorCode = "UNKNOWN_ERROR",
        int $statusCode = 400,
        array $errors = []
    ) {
        parent::__construct($message);
        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;
        $this->errors = $errors;
    }

    public function render($request): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'status_code' => $this->statusCode,
            'message' => $this->getMessage(),
            'error_code' => $this->errorCode,
            'data' => null,
            'errors' => $this->errors,
            'meta' => null,
            'timestamp' => now()->timestamp,
        ], $this->statusCode);
    }
}
