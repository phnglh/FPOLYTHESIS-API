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

    public function index(Request $request)
    {
        $cart = $this->cartService->getUserCart($request->user()->id);

        return $this->successResponse($cart, 'Cart retrieved successfully.');
    }

    public function store(CartRequest $request)
    {
        $cartItem = $this->cartService->addToCart(
            $request->user()->id,
            $request->product_id,
            $request->quantity
        );

        return $this->successResponse($cartItem, 'Product added to cart successfully.');
    }

    public function update(CartRequest $request, $id)
    {
        $cartItem = $this->cartService->updateCartItem($request->user()->id, $id, $request->quantity);

        return $this->successResponse($cartItem, 'Cart item updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $deleted = $this->cartService->removeCartItem($request->user()->id, $id);

        return $this->successResponse($deleted, 'Cart item removed successfully.');
    }
}
