<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $result = $this->authService->register($validated);

        if (isset($result['errors'])) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $result['errors']
            ], 422);
        }

        return response()->json([
            'access_token' => $result['access_token'],
            'token_type' => $result['token_type'],
            'user' => $result['user'],
        ], 201);
    }
}
