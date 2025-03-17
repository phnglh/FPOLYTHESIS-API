<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserService
{
    /**
     * Lấy thông tin user hiện tại (profile).
     */
    public function getCurrentUser(User $user): User
    {
        // Có thể load thêm quan hệ nếu cần, ví dụ:
        // $user->load('roles', 'permissions');
        return $user;
    }

    /**
     * Lấy tất cả user (cho admin).
     */
    public function getAllUsers(): Collection
    {
        return User::all();
    }

    /**
     * Lấy user theo ID.
     *
     * @throws ModelNotFoundException
     */
    public function getUserById(int $id): User
    {
        return User::findOrFail($id); // Viết gọn hơn, tự throw ModelNotFoundException
    }

    /**
     * Tạo mới user (cho admin).
     */
    public function createUser(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return User::create($data);
    }

    /**
     * Cập nhật thông tin user theo ID (cho admin hoặc user tự sửa).
     */
    public function updateUser(int $id, array $data): User
    {
        $user = $this->getUserById($id);

        // Xử lý mật khẩu nếu có
        if (isset($data['password']) && $data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']); // tránh ghi đè password bằng null
        }

        $user->update($data);
        return $user;
    }

    /**
     * Xóa user theo ID (admin).
     */
    public function deleteUser(int $id): bool
    {
        $user = $this->getUserById($id);
        return $user->delete();
    }

    /**
     * Tìm user theo email.
     */
    public function findUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
}
