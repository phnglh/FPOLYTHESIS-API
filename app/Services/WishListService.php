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

        return WishList::where('user_id', $userId)->with('product')->get();
    }
    public function updateWishList($id, $productId)
    {
        $userId = Auth::id();
        if (! $userId) {
            throw new ApiException(
                'Không lấy được dữ liệu',
                404
            );
        }

        $wishlist = WishList::where('id', $id)->where('user_id', $userId)->first();
        if (! $wishlist) {
            return ['message' => 'Sản phẩm không tồn tại trong danh sách yêu thích', 'status' => 404];
        }

        $wishlist->update(['product_id' => $productId]);

        return ['message' => 'Sản phẩm trong danh sách yêu thích đã được cập nhật', 'status' => 200];
    }


    // thêm sản phẩm vào danh sách sản phẩm yêu thích
    public function addWishList($productId)
    {
        $userId = Auth::id();
        if (! $userId) {
            throw new ApiException(
                'Không lấy được dữ liệu',
                404
            );
        }

        // Kiểm tra nếu sản phẩm đã có trong wishlist
        $exists = WishList::where('user_id', $userId)->where('product_id', $productId)->exists();
        if ($exists) {
            return ['message' => 'Sản phẩm đã có trong danh sách yêu thích', 'status' => 400];
        }

        // Nếu chưa có, thêm sản phẩm vào wishlist
        WishList::create([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);

        return ['message' => 'Sản phẩm đã được thêm vào danh sách yêu thích', 'status' => 200];
    }

    // xoá sản phẩm khỏi danh sách sản phẩm yêu thích
    public function deleteWishList($productId)
    {
        $userId = Auth::id();
        if (! $userId) {
            throw new ApiException(
                'Không lấy được dữ liệu',
                404
            );
        }

        // Kiểm tra xem sản phẩm có trong wishlist không
        $listExists = WishList::where('user_id', $userId)->where('product_id', $productId)->first();

        // Nếu không có sản phẩm trong wishlist, trả về thông báo lỗi
        if (!$listExists) {
            return ['message' => 'Sản phẩm không tồn tại trong danh sách yêu thích', 'status' => 400];
        }

        // Xóa sản phẩm khỏi wishlist
        $listExists->delete();

        return ['message' => 'Sản phẩm đã được xóa khỏi danh sách yêu thích', 'status' => 200];
    }
}
