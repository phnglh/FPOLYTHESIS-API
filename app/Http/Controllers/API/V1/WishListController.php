<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Api\BaseController;
use App\Services\WishListService;
use Illuminate\Http\Request;
use App\Models\WishList;

class WishListController extends BaseController
{
    protected $wishListService;

    public function __construct(WishListService $wishListService)
    {
        $this->wishListService = $wishListService;
    }
    public function show($id)
    {
        $wishlist = WishList::find($id);

        if (!$wishlist) {
            return response()->json([
                'status' => 'error',
                'status_code' => 404,
                'message' => 'Wishlist item not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $wishlist
        ]);
    }


    public function getWishList()
    {
        $wishlists = $this->wishListService->getAllWishLists();

        if ($wishlists->isEmpty()) {
            return $this->errorResponse('No data found', 404);
        }

        return $this->successResponse($wishlists, 'Wishlist retrieved successfully.');
    }


    public function addWishList(Request $request)
    {
        $request->validate([
            'sku_id' => 'required|exists:skus,id',
        ]);

        $response = $this->wishListService->addWishList($request->sku_id);

        return $this->successResponse(null, $response['message']);
    }

    public function deleteWishList($id)
    {
        $response = $this->wishListService->deleteWishList($id);

        return $this->successResponse(null, $response['message']);
    }
}
