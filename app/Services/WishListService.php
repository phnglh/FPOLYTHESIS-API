<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\WishList;
use Illuminate\Support\Facades\Auth; // them moi

class WishListService
{
    // lấy danh sách sản phẩm yêu thích
    public function getAllWishLists()
    {
        $userId = Auth::id();
        if (! $userId) {
            throw new ApiException(
                'Không lấy được dữ liệu',
                404
            );
        }

        return WishList::where('user_id', $userId)->with('sku')->get();

    }

    // thêm sản phẩm vào danh sách sản phẩm yêu thích
    public function addWishList($skuId)
    {
        $userId = Auth::id();
        if (! $userId) {
            throw new ApiException(
                'Không lấy được dữ liệu',
                404
            );
        }

        // Kiểm tra nếu sản phẩm đã có trong wishlist
        $exists = WishList::where('user_id', $userId)->where('sku_id', $skuId)->exists();
        if ($exists) {
            return ['message' => 'Sản phẩm đã có trong danh sách yêu thích', 'status' => 400];
        }

        // Nếu chưa có, thêm sản phẩm vào wishlist
        WishList::create([
            'user_id' => $userId,
            'sku_id' => $skuId,
        ]);

        return ['message' => 'Sản phẩm đã được thêm vào danh sách yêu thích', 'status' => 200];
    }


    // xoá sản phẩm khỏi danh sách sản phẩm yêu thích
    public function deleteWishList($skuId)
    {
        $userId = Auth::id();
        if (! $userId) {
            throw new ApiException(
                'Không lấy được dữ liệu',
                404
            );
        }

        // Kiểm tra xem sản phẩm có trong wishlist không
        $listExists = WishList::where('user_id', $userId)->where('sku_id', $skuId)->first();

        // Nếu không có sản phẩm trong wishlist, trả về thông báo lỗi
        if (!$listExists) {
            return ['message' => 'Sản phẩm không tồn tại trong danh sách yêu thích', 'status' => 400];
        }

        // Xóa sản phẩm khỏi wishlist
        $listExists->delete();

        return ['message' => 'Sản phẩm đã được xóa khỏi danh sách yêu thích', 'status' => 200];
    }
}
