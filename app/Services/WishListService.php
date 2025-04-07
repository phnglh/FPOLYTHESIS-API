<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\WishList;
use Illuminate\Support\Facades\Auth;

class WishListService
{
    public function getAllWishLists()
    {
        $userId = Auth::id();
        if (! $userId) {
            throw new ApiException('Không lấy được dữ liệu', 404);
        }

        return WishList::where('user_id', $userId)
            ->with('product')
            ->get();
    }

    public function addWishList($productId)
    {
        $userId = Auth::id();
        if (! $userId) {
            throw new ApiException('Không lấy được dữ liệu', 404);
        }

        $exists = WishList::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();

        if ($exists) {
            return ['message' => 'Sản phẩm đã có trong danh sách yêu thích', 'status' => 400];
        }

        WishList::create([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);

        return ['message' => 'Đã thêm thành công vào danh sách yêu thích', 'status' => 200];
    }

    public function deleteWishList($productId)
    {
        $userId = Auth::id();
        if (! $userId) {
            throw new ApiException('Không lấy được dữ liệu', 404);
        }

        $listExists = WishList::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if (! $listExists) {
            return ['message' => 'Sản phẩm không tồn tại trong danh sách yêu thích', 'status' => 400];
        }

        $listExists->delete();

        return ['message' => 'Đã xóa thành công sản phẩm khỏi danh sách yêu thích', 'status' => 200];
    }

}
