<?php

namespace App\Services;

use App\Http\Resources\Carts\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Sku;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function getCart()
    {
        $cart = Cart::where('user_id', Auth::id())
            ->with([
                'items.sku.product',
                'items.sku.attributeSkus.attribute',
                'items.sku.attributeSkus.attributeValue',
            ])
            ->first();

        if (!$cart) {
            return [
                'status' => 'error',
                'message' => 'Giỏ hàng trống',
                'data' => [],
            ];
        }

        return new CartResource($cart);
    }




    public function addToCart($skuId, $quantity)
    {
        $userId = Auth::id();
        $cart = Cart::firstOrCreate(['user_id' => $userId]);

        $sku = Sku::with('product')->findOrFail($skuId);

        if ($sku->stock < $quantity) {
            return response()->json([
                'error' => 'OUT_OF_STOCK',
                'message' => 'Số lượng sản phẩm không đủ trong kho',
            ], 400);
        }

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('sku_id', $skuId)
            ->first();

        if ($cartItem) {
            if ($sku->stock < $cartItem->quantity + $quantity) {
                return response()->json([
                    'error' => 'STOCK_NOT_ENOUGH',
                    'message' => 'Số lượng sản phẩm trong kho không đủ',
                ], 400);
            }
            $cartItem->increment('quantity', $quantity);
        } else {
            $cartItem = $cart->items()->create([
                'sku_id' => $skuId,
                'quantity' => $quantity,
                'unit_price' => $sku->price,
            ]);
        }

        $sku->decrement('stock', $quantity);

        return response()->json([
            'success' => true,
            'cart' => new CartResource($cart->load('items.sku.product')),
        ]);
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
        $cartItem = CartItem::where('id', $itemId)
            ->whereHas('cart', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->first();

        if (!$cartItem) {
            return response()->json([
                'error' => 'ITEM_NOT_FOUND',
                'message' => 'Không tìm thấy sản phẩm trong giỏ hàng',
            ], 404);
        }

        $sku = Sku::find($cartItem->sku_id);
        if (!$sku) {
            return response()->json([
                'error' => 'SKU_NOT_FOUND',
                'message' => 'Không tìm thấy SKU sản phẩm',
            ], 404);
        }

        $newQuantity = $cartItem->quantity + $quantityChange;

        if ($newQuantity <= 0) {
            $cartItem->delete();
            return response()->json([
                'success' => true,
                'message' => 'Sản phẩm đã được xóa khỏi giỏ hàng',
            ], 200);
        }

        if ($newQuantity > $sku->stock) {
            return response()->json([
                'error' => 'STOCK_NOT_ENOUGH',
                'message' => 'Số lượng sản phẩm không đủ',
            ], 400);
        }

        DB::beginTransaction();
        try {
            $sku->decrement('stock', $quantityChange);
            $cartItem->update(['quantity' => $newQuantity]);

            DB::commit();

            return response()->json([
                'success' => true,
                'cart' => new CartResource($cartItem->cart->load('items.sku')),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'UPDATE_FAILED',
                'message' => 'Cập nhật giỏ hàng thất bại',
            ], 500);
        }
    }
    public function setCartItemQuantity($itemId, $quantity)
    {
        $cartItem = CartItem::where('id', $itemId)->whereHas('cart', function ($query) {
            $query->where('user_id', Auth::id());
        })->first();

        if (!$cartItem) {
            return response()->json([
                'error' => 'ITEM_NOT_FOUND',
                'message' => 'Không tìm thấy sản phẩm trong giỏ hàng',
            ], 404);
        }

        $sku = Sku::find($cartItem->sku_id);
        if (!$sku) {
            return response()->json([
                'error' => 'SKU_NOT_FOUND',
                'message' => 'Không tìm thấy SKU sản phẩm',
            ], 404);
        }

        if ($quantity <= 0) {
            $cartItem->delete();
            return response()->json([
                'error' => 'ITEM_REMOVED',
                'message' => 'Sản phẩm đã được xóa khỏi giỏ hàng',
            ], 200);
        }

        if ($sku->stock < ($quantity - $cartItem->quantity)) {
            return response()->json([
                'error' => 'STOCK_NOT_ENOUGH',
                'message' => 'Số lượng sản phẩm không đủ',
            ], 400);
        }

        $difference = $quantity - $cartItem->quantity;
        if ($difference > 0) {
            $sku->decrement('stock', $difference);
        } elseif ($difference < 0) {
            $sku->increment('stock', abs($difference));
        }

        $cartItem->update(['quantity' => $quantity]);
        return response()->json([
            'success' => true,
            'cart' => new CartResource($cartItem->cart->load('items.sku')),
        ]);
    }


    public function removeCartItem($itemId)
    {
        $cartItem = CartItem::where('id', $itemId)->whereHas('cart', function ($query) {
            $query->where('user_id', Auth::id());
        })->firstOrFail();

        $cartItem->delete();
        return new CartResource($cartItem->cart->load('items.sku'));
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
