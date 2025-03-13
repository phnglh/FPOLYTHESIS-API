<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Exceptions\ApiException; // them moi

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
        // Kiểm tra sản phẩm có tồn tại không
        $product = Product::find($productId);
        if (!$product) {
            throw new ApiException("Sản phẩm không tồn tại!", 404);
        }

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
<<<<<<< Updated upstream
        $cartItem = Cart::where('id', $cartId)->where('user_id', $user_id)->first();
        if ($cartItem) {
=======
        $cartItem = $this->findCartItem($userId, $cartId);

        if (!$cartItem) {
            throw new ApiException('Sản phẩm không tìm thấy trong giỏ hàng!', 404);

        }
        else{
>>>>>>> Stashed changes
            $cartItem->update(['quantity' => $quantity]);
            return $cartItem;
        }

    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng.
     */
    public function removeCartItem($user_id, $cartId)
    {
<<<<<<< Updated upstream
        $cartItem = Cart::where('id', $cartId)->where('user_id', $user_id)->first();
        if ($cartItem) {
=======
        $cartItem = $this->findCartItem($userId, $cartId);

        if (!$cartItem) {
            throw new ApiException('Sản phẩm không tìm thấy trong giỏ hàng!', 404);
        }
        else {
>>>>>>> Stashed changes
            $cartItem->delete();
            return true;
        }

    }

    /**
     * Tìm kiếm một sản phẩm trong giỏ hàng của người dùng.
     */
    private function findCartItem($userId, $cartId)
    {
        return Cart::where('id', $cartId)->where('user_id', $userId)->first();
    }
}