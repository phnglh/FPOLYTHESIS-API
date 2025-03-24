<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    public function register(array $data)
    {

        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'] ?? 'customer',
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function login(array $data)
    {
        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return [
                'success' => false,
                'message' => 'Email hoặc mật khẩu không đúng.',
            ];
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'access_token' => $token,
        ];
    }

    public function logout(Request $request, $logoutAll = false)
    {
        $user = $request->user();

        if ($logoutAll) {
            // Xóa tất cả token của user
            $user->tokens()->delete();
        } else {
            // Lấy token từ request
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'status_code' => 401,
                    'message' => 'No active token found.',
                    'error_code' => 'TOKEN_NOT_FOUND',
                ], 401);
            }

            // Tìm token trong database và xóa nó
            $tokenInstance = PersonalAccessToken::findToken($token);
            if ($tokenInstance) {
                $tokenInstance->delete();
            } else {
                return response()->json([
                    'status' => 'error',
                    'status_code' => 401,
                    'message' => 'Invalid token.',
                    'error_code' => 'INVALID_TOKEN',
                ], 401);
            }
        }

        return response()->json([
            'status' => 'success',
            'status_code' => 200,
            'message' => 'Logged out successfully!',
        ]);
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword)
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw new \Exception('Current password is incorrect.');
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }
}
