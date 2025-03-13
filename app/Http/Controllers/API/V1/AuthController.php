<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            return $this->errorResponse("VALIDATION_FAILED", "Validation failed.", 422, $result['errors']);
        }

        return $this->successResponse([
            'access_token' => $result['access_token'],
            'token_type' => $result['token_type'],
            'user' => $result['user'],
        ], "User registered successfully.");
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $result = $this->authService->login($credentials);
        if (!$result) {
            return $this->errorResponse("VALIDATION_FAILED", 'Email hoặc mật khẩu không đúng.', 401);
        };
        return $this->successResponse($result);
    }
    public function logout(Request $request)
    {
        $result = $this->authService->logout($request);
        return $this->successResponse($result, "Logout successful.");
    }


    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse("VALIDATION_FAILED", "Invalid email address.", 422, $validator->errors());
        }

        $result = $this->authService->sendResetLink($request->input('email'));

        if ($result['success']) {
            return $this->successResponse(null, "Password reset link sent successfully.");
        }

        return $this->errorResponse("RESET_LINK_FAILED", "Failed to send password reset link.", 500);
    }
}
