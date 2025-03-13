<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;

class CartService
{
    /**
     * Lấy danh sách giỏ hàng của người dùng.
     */
    public function getUserCart($userId)
    {
        return Cart::where('user_id', $userId)->with('product')->get();
    }

    /**
     * Thêm sản phẩm vào giỏ hàng hoặc cập nhật số lượng.
     */
    public function addToCart($user_id, $product_id, $quantity)
    {
        return Cart::updateOrCreate(
            [
                'user_id' => $user_id,
                'product_id' => $product_id,
                'quantity' => $quantity
            ]

        );
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng.
     */
    public function updateCartItem($user_id, $cartId, $quantity)
    {
        $cartItem = Cart::where('id', $cartId)->where('user_id', $user_id)->first();
        if ($cartItem) {
            $cartItem->update(['quantity' => $quantity]);
            return $cartItem;
        }
        return null;
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng.
     */
    public function removeCartItem($user_id, $cartId)
    {
        $cartItem = Cart::where('id', $cartId)->where('user_id', $user_id)->first();
        if ($cartItem) {
            $cartItem->delete();
            return true;
        }
        return false;
    }
}