<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Services\WishListService;
use Illuminate\Http\Request;

class WishListController extends Controller
{
    protected $wishListService;
    public function __construct(WishListService $wishListService)
    {
        $this->wishListService = $wishListService;
    }
    // lấy danh sách
    public function getWishList()
    {
        return response()->json($this->wishListService->getAllWishLists());
    }
    public function addWishList(Request $request)
    {
        $request->validate([
            'sku_id' => 'required|exists:skus,id',
        ]);
        $response = $this->wishListService->addWishList($request->sku_id);
        return response()->json([
            'message' => $response['message'],
            $response['status'],
        ]);
    }
    public function deleteWishList($id)
    {
        $response = $this->wishListService->deleteWishList($id);
        return response()->json([
            'message' => $response['message'],
            $response['status'],
        ]);
    }
}
