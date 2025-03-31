<?php

namespace App\Services;

use App\Models\UserAddress;
use Illuminate\Support\Facades\Auth;

class UserAddressService
{
    public function getUserAddresses()
    {
        $user = Auth::user();
        $guestEmail = session('guest_email');

        return UserAddress::where(function ($query) use ($user, $guestEmail) {
            if ($user) {
                $query->where('user_id', $user->id);
            } else {
                $query->where('guest_email', $guestEmail);
            }
        })->get();
    }

    public function createUserAddress($data)
    {
        $user = Auth::user();
        $guestEmail = session('guest_email');

        if (!$user && !$guestEmail) {
            throw new \Exception('Bạn cần cung cấp email để lưu địa chỉ.', 400);
        }

        // Kiểm tra nếu địa chỉ đã tồn tại
        $exists = UserAddress::where(function ($query) use ($user, $guestEmail, $data) {
            if ($user) {
                $query->where('user_id', $user->id);
            } else {
                $query->where('guest_email', $guestEmail);
            }
        })->where('address', $data['address'])->exists();

        if ($exists) {
            throw new \Exception('Địa chỉ này đã tồn tại trong hệ thống', 400);
        }

        // Nếu địa chỉ mới được đặt là mặc định, bỏ mặc định các địa chỉ khác
        if (isset($data['is_default']) && $data['is_default']) {
            UserAddress::where(function ($query) use ($user, $guestEmail) {
                if ($user) {
                    $query->where('user_id', $user->id);
                } else {
                    $query->where('guest_email', $guestEmail);
                }
            })->update(['is_default' => false]);
        }

        return UserAddress::create([
            'user_id' => $user?->id, // Nếu có user thì gán user_id
            'guest_email' => $user ? null : $guestEmail, // Nếu là guest thì gán email
            'receiver_name' => $data['receiver_name'],
            'receiver_email' => $user?->email ?? $guestEmail,
            'receiver_phone' => $data['receiver_phone'],
            'address' => $data['address'],
            'city' => $data['city'],
            'state' => $data['state'],
            'is_default' => $data['is_default'] ?? false,
        ]);
    }

    public function updateUserAddress($id, $data)
    {
        $user = Auth::user();
        $guestEmail = session('guest_email');

        $address = UserAddress::where('id', $id)
            ->where(function ($query) use ($user, $guestEmail) {
                if ($user) {
                    $query->where('user_id', $user->id);
                } else {
                    $query->where('guest_email', $guestEmail);
                }
            })->firstOrFail();

        if (isset($data['is_default']) && $data['is_default']) {
            UserAddress::where(function ($query) use ($user, $guestEmail) {
                if ($user) {
                    $query->where('user_id', $user->id);
                } else {
                    $query->where('guest_email', $guestEmail);
                }
            })->update(['is_default' => false]);
        }

        $address->update($data);
        return $address;
    }

    public function deleteUserAddress($id)
    {
        $user = Auth::user();
        $guestEmail = session('guest_email');

        $address = UserAddress::where('id', $id)
            ->where(function ($query) use ($user, $guestEmail) {
                if ($user) {
                    $query->where('user_id', $user->id);
                } else {
                    $query->where('guest_email', $guestEmail);
                }
            })->firstOrFail();

        $address->delete();
        return true;
    }
}
