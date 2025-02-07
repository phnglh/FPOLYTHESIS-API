<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    // đăng ký
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


    // đăng nhập
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        $result = $this->authService->login($credentials);

        if ($result['success']) {
            return response()->json([
                'access_token' => $result['access_token'],
                'token_type' => $result['token_type'],
                'user' => $result['user'],
            ], 200);
        }

        return response()->json(['error' => $result['message']], 401);
    }

    // đăng xuất
    public function logout(Request $request)
    {
        $result = $this->authService->logout($request);

        return response()->json($result, 200);
    }

    // đổi mật khẩu
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $result = $this->authService->changePassword(
            $request->user(),
            $validated['old_password'],
            $validated['new_password']
        );

        return response()->json($result, $result['success'] ? 200 : 400);

    }

    // lấy lại mật khẩu
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email|unique:users,email']);

        $result = $this->authService->sendResetLinkEmail($request->email);

        return response()->json($result, $result['success'] ? 200 : 400);
    }
}
