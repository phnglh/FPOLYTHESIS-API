<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthService{

    public function register(array $data)
    {
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    public function login(array $data)
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return [
                'success' => false,
                'message' => 'Email hoặc mật khẩu không đúng.',
            ];
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'success' => true,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ];
    }
    public function logout(Request $request, $logoutAll = false)
    {
        $user = $request->user();

        if ($logoutAll) {
            $user->tokens()->delete();
        } else {
            $user->currentAccessToken()->delete();
        }

        return ['message' => 'Logged out successfully!'];
    }
}
