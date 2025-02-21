<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


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
    public function logout(Request $request)
    {
        $result = $this->authService->logout($request);

        return response()->json($result, 200);
    }


    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email không hợp lệ!',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->authService->sendResetLink($request->input('email'));
        return response()->json($result, $result['success'] ? 200 : 500);
    }
    
}
