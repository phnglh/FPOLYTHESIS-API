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
        $cartItem = CartItem::where('cart_id', $cart->id)->where('sku_id', $skuId)->first();

        if ($cartItem) {
            $cartItem->increment('quantity', $quantity);
        } else {
            $cart->items()->create([
                'sku_id' => $skuId,
                'quantity' => $quantity,
                'unit_price' => $sku->price
            ]);
        }

        $sku->decrement('stock', $quantity);

        return $cart->load('items.sku');
    }

    public function updateCartItem($itemId, $quantity)
    {
        $cartItem = CartItem::where('id', $itemId)->whereHas('cart', function ($query) {
            $query->where('user_id', Auth::id());
        })->firstOrFail();

        $cartItem->update(['quantity' => $quantity]);

        return $cartItem->cart->load('items.sku');
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
