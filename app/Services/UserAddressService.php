<?php

namespace App\Services;

use App\Models\UserAddress;
use Illuminate\Support\Facades\Auth;

class UserAddressService
{
    public function getUserAddresses()
    {
        return Auth::user()->userAddresses;
    }

    public function createUserAddress($data)
    {
        $user = Auth::user();

        // Kiểm tra nếu địa chỉ đã tồn tại
        $exists = $user->userAddresses()->where('address', $data['address'])->exists();

        if ($exists) {
            throw new \Exception('Địa chỉ này đã tồn tại trong hệ thống', 400);
        }

        // Nếu địa chỉ mới được đặt là mặc định, bỏ mặc định các địa chỉ khác
        if (isset($data['is_default']) && $data['is_default']) {
            $user->userAddresses()->update(['is_default' => false]);
        }

        return $user->userAddresses()->create($data);
    }



    public function updateUserAddress($id, $data)
    {
        $address = UserAddress::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        if (isset($data['is_default']) && $data['is_default']) {
            UserAddress::where('user_id', Auth::id())->update(['is_default' => false]);
        }

        $address->update($data);
        return $address;
    }

    public function deleteUserAddress($id)
    {
        $address = UserAddress::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $address->delete();
        return true;
    }
}
