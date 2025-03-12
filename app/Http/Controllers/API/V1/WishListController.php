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
    // lấy danh sách
    public function getWishList()
    {
        $wishlists = $this->wishListService->getAllWishLists();
        return $this->successResponse($wishlists, "Wishlist retrieved successfully.");
    }
    public function addWishList(Request $request)
    {
        $request->validate([
            'sku_id' => 'required|exists:skus,id',
        ]);

        $response = $this->wishListService->addWishList($request->sku_id);

        if (!$response['status']) {
            return $this->errorResponse("WISHLIST_ADD_FAILED", $response['message'], 400);
        }

        return $this->successResponse(null, $response['message']);
    }
    public function deleteWishList($id)
    {
        $response = $this->wishListService->deleteWishList($id);

        if (!$response['status']) {
            return $this->errorResponse("WISHLIST_REMOVE_FAILED", $response['message'], 400);
        }

        return $this->successResponse(null, $response['message']);
    }
}