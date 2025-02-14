<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthService{

    public function register(array $data)
    {

        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
            dd($user);

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

    // đăng nhập
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

    // đăng xuất
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

    // đổi mật khẩu
    public function changePassword($user, $oldPassword, $newPassword)
    {
        if(!Hash::check($oldPassword, $user->password)){
            return [
                'success' => false,
                'message' => 'Mật khẩu cũ không đúng!',
            ];
        }

        $user->update(['password' => Hash::make($newPassword)]);
        return [
            'success' => true,
            'message' => 'Đối mật khẩu thành công!',
        ];
    }

    // quên mật khẩu (gửi email reset)
    public function sendResetLinkEmail($email)
    {
        $status = Password::sendResetLink(['email' => $email]);

        return $status === Password::RESET_LINK_SENT
            ?['success' => true, 'message' => 'Đã gửi thông tin về Email!'] : ['success'=>false, 'message' => 'Không thể gửi email!'];
    }
}
