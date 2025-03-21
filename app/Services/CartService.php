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

        if ($sku->stock < $quantity) {
            return ['error' => 'OUT_OF_STOCK', 'message' => 'Số lượng sản phẩm không đủ trong kho'];
        }

        $cartItem = CartItem::where('cart_id', $cart->id)->where('sku_id', $skuId)->first();

        if ($cartItem) {
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

        $sku->decrement('stock', $quantity);
        return ['success' => true, 'cart' => $cart->load('items.sku')];
    }

    public function incrementCartItem($itemId)
    {
        return $this->updateCartItemQuantity($itemId, 1);
    }

    public function decrementCartItem($itemId)
    {
        return $this->updateCartItemQuantity($itemId, -1);
    }

    public function updateCartItemQuantity($itemId, $quantityChange)
    {
        $cartItem = CartItem::where('id', $itemId)->whereHas('cart', function ($query) {
            $query->where('user_id', Auth::id());
        })->firstOrFail();

        $sku = Sku::findOrFail($cartItem->sku_id);
        $newQuantity = $cartItem->quantity + $quantityChange;

        if ($newQuantity <= 0) {
            $cartItem->delete();
            return ['error' => 'ITEM_REMOVED', 'message' => 'Sản phẩm đã được xóa khỏi giỏ hàng'];
        }

        if ($sku->stock < $quantityChange) {
            return ['error' => 'STOCK_NOT_ENOUGH', 'message' => 'Số lượng sản phẩm không đủ'];
        }

        $sku->decrement('stock', $quantityChange);
        $cartItem->increment('quantity', $quantityChange);

        return ['success' => true, 'cart' => $cartItem->cart->load('items.sku')];
    }

    public function setCartItemQuantity($itemId, $quantity)
    {
        $cartItem = CartItem::where('id', $itemId)->whereHas('cart', function ($query) {
            $query->where('user_id', Auth::id());
        })->firstOrFail();

        $sku = Sku::findOrFail($cartItem->sku_id);

        if ($quantity <= 0) {
            $cartItem->delete();
            return ['error' => 'ITEM_REMOVED', 'message' => 'Sản phẩm đã được xóa khỏi giỏ hàng'];
        }

        if ($sku->stock < ($quantity - $cartItem->quantity)) {
            return ['error' => 'STOCK_NOT_ENOUGH', 'message' => 'Số lượng sản phẩm không đủ'];
        }

        $difference = $quantity - $cartItem->quantity;
        if ($difference > 0) {
            $sku->decrement('stock', $difference);
        } elseif ($difference < 0) {
            $sku->increment('stock', abs($difference));
        }

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
