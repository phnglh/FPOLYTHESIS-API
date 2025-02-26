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
    public function addToCart($userId, $productId, $quantity)
    {
        return Cart::updateOrCreate(
            ['user_id' => $userId, 'product_id' => $productId],
            ['quantity' => $quantity]
        );
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng.
     */
    public function updateCartItem($userId, $cartId, $quantity)
    {
        $cartItem = Cart::where('id', $cartId)->where('user_id', $userId)->first();
        if ($cartItem) {
            $cartItem->update(['quantity' => $quantity]);
            return $cartItem;
        }
        return null;
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng.
     */
    public function removeCartItem($userId, $cartId)
    {
        $cartItem = Cart::where('id', $cartId)->where('user_id', $userId)->first();
        if ($cartItem) {
            $cartItem->delete();
            return true;
        }
        return false;
    }
}
