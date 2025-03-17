<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends BaseController
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
            return $this->errorResponse('VALIDATION_FAILED', 'Validation failed.', 422, $result['errors']);
        }

        return $this->successResponse([
            'access_token' => $result['access_token'],
            'token_type' => $result['token_type'],
            'user' => $result['user'],
        ], 'User registered successfully.');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $result = $this->authService->login($credentials);
        if (! $result) {
            return $this->errorResponse('VALIDATION_FAILED', 'Email hoặc mật khẩu không đúng.', 401);
        }

        return $this->successResponse($result);
    }

    public function logout(Request $request)
    {
        $result = $this->authService->logout($request);

        return $this->successResponse($result, 'Logout successful.');
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|confirmed',
        ]);
        $this->authService->changePassword($request->user(), $data['current_password'], $data['new_password']);
        return response()->json(['message' => 'Password changed successfully']);
    }
}
