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

        return $this->successResponse($result, 'User registered successfully.');
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không đúng.',
            ], 401);
        }

        return $this->successResponse([
            'user' => [
                'id' => $result['user']->id,
                'email' => $result['user']->email,
                'name' => $result['user']->name,
                "role" => $result['user']->role,

            ],
            'access_token' => $result['access_token'],
        ]);
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

        return $this->successResponse('Password changed successfully');
    }
}
