<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\CartRequest;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends BaseController
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    // Lấy danh sách giỏ hàng
    public function index(Request $request)
    {
        $cart = $this->cartService->getUserCart($request->user()->id);
        return $this->successResponse($cart, "Cart retrieved successfully.");
    }

    // Thêm sản phẩm vào giỏ hàng
    public function store(CartRequest $request)
    {
        $cartItem = $this->cartService->addToCart(
            $request->user()->id,
            $request->product_id,
            $request->quantity
        );

        return $this->successResponse($cartItem, "Product added to cart successfully.");
    }

    // Cập nhật số lượng sản phẩm
    public function update(CartRequest $request, $id)
    {
        $cartItem = $this->cartService->updateCartItem($request->user()->id, $id, $request->quantity);

        if (!$cartItem) {
            return $this->errorResponse("CART_ITEM_NOT_FOUND", "Cart item not found.", 404);
        }

        return $this->successResponse($cartItem, "Cart item updated successfully.");
    }

    // Xóa sản phẩm khỏi giỏ hàng
    public function destroy(Request $request, $id)
    {
        $deleted = $this->cartService->removeCartItem($request->user()->id, $id);

        if (!$deleted) {
            return $this->errorResponse("CART_ITEM_NOT_FOUND", "Cart item not found.", 404);
        }

        return $this->successResponse(null, "Cart item removed successfully.");
    }
}