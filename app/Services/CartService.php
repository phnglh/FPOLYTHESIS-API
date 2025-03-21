<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Sku;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function getCart()
    {
        return Cart::where('user_id', Auth::id())->with('items.sku')->first();
    }

    public function addToCart($skuId, $quantity)
    {
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $sku = Sku::findOrFail($skuId);

        // Kiểm tra tồn kho trước khi thêm vào giỏ hàng
        if ($sku->stock < $quantity) {
            return ['error' => 'OUT_OF_STOCK', 'message' => 'Số lượng sản phẩm không đủ trong kho'];
        }

        $cartItem = CartItem::where('cart_id', $cart->id)->where('sku_id', $skuId)->first();

        if ($cartItem) {
            // Kiểm tra nếu tổng số lượng sau khi cập nhật có vượt quá tồn kho không
            if ($sku->stock < $quantity + $cartItem->quantity) {
                return ['error' => 'STOCK_NOT_ENOUGH', 'message' => 'Số lượng sản phẩm trong kho không đủ'];
            }
            $cartItem->increment('quantity', $quantity);
        } else {
            $cart->items()->create([
                'sku_id' => $skuId,
                'quantity' => $quantity,
                'unit_price' => $sku->price
            ]);
        }

        // Trừ tồn kho sau khi kiểm tra
        $sku->decrement('stock', $quantity);

        return ['success' => true, 'cart' => $cart->load('items.sku')];
    }


    public function updateCartItem($itemId, $quantity = null, $isIncrement = null)
    {
        $cartItem = CartItem::where('id', $itemId)->whereHas('cart', function ($query) {
            $query->where('user_id', Auth::id());
        })->firstOrFail();

        $sku = Sku::findOrFail($cartItem->sku_id);

        // Nếu có `isIncrement`, bỏ qua validate số lượng
        if (!is_null($isIncrement)) {
            $quantity = $isIncrement ? $cartItem->quantity + 1 : $cartItem->quantity - 1;
        } else {
            // Nếu không có isIncrement, kiểm tra quantity
            if (is_null($quantity) || $quantity <= 0) {
                return ['error' => 'INVALID_QUANTITY', 'message' => 'Số lượng không hợp lệ'];
            }
        }

        // Kiểm tra tồn kho
        if ($sku->stock < ($quantity - $cartItem->quantity)) {
            return ['error' => 'STOCK_NOT_ENOUGH', 'message' => 'Số lượng sản phẩm không đủ'];
        }

        // Cập nhật stock
        if ($quantity > $cartItem->quantity) {
            $sku->decrement('stock', $quantity - $cartItem->quantity);
        } elseif ($quantity < $cartItem->quantity) {
            $sku->increment('stock', $cartItem->quantity - $quantity);
        }

        // Nếu số lượng <= 0 thì xóa sản phẩm khỏi giỏ hàng
        if ($quantity <= 0) {
            $cartItem->delete();
            return ['error' => 'ITEM_REMOVED', 'message' => 'Sản phẩm đã được xóa khỏi giỏ hàng'];
        }

        // Cập nhật số lượng trong giỏ hàng
        $cartItem->update(['quantity' => $quantity]);

        return ['success' => true, 'cart' => $cartItem->cart->load('items.sku')];
    }


    public function removeCartItem($itemId)
    {
        $cartItem = CartItem::where('id', $itemId)->whereHas('cart', function ($query) {
            $query->where('user_id', Auth::id());
        })->firstOrFail();

        $cartItem->delete();

        return $cartItem->cart->load('items.sku');
    }

    public function clearCart()
    {
        $cart = Cart::where('user_id', Auth::id())->first();
        if ($cart) {
            $cart->items()->delete();
            $cart->delete();
        }
    }
}
