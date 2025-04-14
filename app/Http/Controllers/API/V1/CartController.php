<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use App\Services\CartService;
use Illuminate\Http\Request;

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
        return $this->successResponse($cart, 'GET_TO_CART_SUCCESS');
    }

    public function store(Request $request)
    {
        try {
            $cart = $this->cartService->addToCart($request->sku_id, $request->quantity);

            // Kiểm tra nếu kết quả là mảng và chứa lỗi
            if (isset($cart['error'])) {
                return response()->json([
                    'error' => $cart['error'],
                    'message' => $cart['message'],
                ], 400); // Trả về lỗi nếu có
            }

            return $this->successResponse($cart, 'ADD_TO_CART_SUCCESS');
        } catch (\Exception $e) {
            return $this->errorResponse('ADD_TO_CART_ERROR', $e->getMessage(), 500);
        }
    }


    public function increment($itemId)
    {
        try {
            $cart = $this->cartService->incrementCartItem($itemId);
            return $this->successResponse($cart, 'INCREMENT_CART_SUCCESS');
        } catch (\Exception $e) {
            return $this->errorResponse('INCREMENT_CART_ERROR', $e->getMessage(), 500);
        }
    }

    public function decrement($itemId)
    {
        try {
            $cart = $this->cartService->decrementCartItem($itemId);
            return $this->successResponse($cart, 'DECREMENT_CART_SUCCESS');
        } catch (\Exception $e) {
            return $this->errorResponse('DECREMENT_CART_ERROR', $e->getMessage(), 500);
        }
    }

    public function updateQuantity(Request $request, $itemId)
    {
        try {
            $cart = $this->cartService->setCartItemQuantity($itemId, $request->quantity);
            return $this->successResponse($cart, 'UPDATE_CART_SUCCESS');
        } catch (\Exception $e) {
            return $this->errorResponse('UPDATE_CART_ERROR', $e->getMessage(), 500);
        }
    }

    public function destroy($itemId)
    {
        try {
            $cart = $this->cartService->removeCartItem($itemId);
            return $this->successResponse($cart, 'REMOVE_CART_ITEM_SUCCESS');
        } catch (\Exception $e) {
            return $this->errorResponse('REMOVE_CART_ITEM_ERROR', $e->getMessage(), 500);
        }
    }

    public function clear()
    {
        try {
            $this->cartService->clearCart();
            return $this->successResponse(null, 'CLEAR_CART_SUCCESS');
        } catch (\Exception $e) {
            return $this->errorResponse('CLEAR_CART_ERROR', $e->getMessage(), 500);
        }
    }
}
