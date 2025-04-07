<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Api\BaseController;
use App\Services\WishListService;
use Illuminate\Http\Request;

class WishListController extends BaseController
{
    protected $wishListService;

    public function __construct(WishListService $wishListService)
    {
        $this->wishListService = $wishListService;
    }

    /**
     * Lấy toàn bộ danh sách yêu thích của người dùng hiện tại
     */
    public function getWishList()
    {
        $wishlists = $this->wishListService->getAllWishLists();
        return $this->successResponse($wishlists, 'Wishlist retrieved successfully.');
    }

    /**
     * Thêm một sản phẩm vào danh sách yêu thích
     */
    public function addWishList(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id', // Kiểm tra product_id tồn tại trong bảng products
        ]);

        $response = $this->wishListService->addWishList($request->product_id);
        return $this->successResponse(null, $response['message']);
    }

    /**
     * Xóa một sản phẩm khỏi danh sách yêu thích
     */
    public function deleteWishList($id)
    {
        $response = $this->wishListService->deleteWishList($id);
        return $this->successResponse(null, $response['message']);
    }

}
