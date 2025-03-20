<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use App\Http\Requests\CartRequest;
use App\Services\CartService;

class CartController extends BaseController
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        $cart = $this->cartService->getCart();
        return $this->successResponse($cart, 'Cart retrieved successfully');
    }

    public function store(CartRequest $request)
    {
        try {
            $cart = $this->cartService->addToCart($request->sku_id, $request->quantity);
            return $this->successResponse($cart, 'Product added to cart successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('ADD_TO_CART_ERROR', $e->getMessage(), 500);
        }
    }

    public function update(CartRequest $request, $itemId)
    {
        try {
            $cart = $this->cartService->updateCartItem($itemId, $request->quantity);
            return $this->successResponse($cart, 'Cart item updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('UPDATE_CART_ERROR', $e->getMessage(), 500);
        }
    }

    public function destroy($itemId)
    {
        try {
            $cart = $this->cartService->removeCartItem($itemId);
            return $this->successResponse($cart, 'Cart item removed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('REMOVE_CART_ITEM_ERROR', $e->getMessage(), 500);
        }
    }

    public function clear()
    {
        try {
            $this->cartService->clearCart();
            return $this->successResponse(null, 'Cart cleared successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('CLEAR_CART_ERROR', $e->getMessage(), 500);
        }
    }
}
