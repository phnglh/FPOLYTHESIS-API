<?php

namespace App\Services;

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
                'success' => false,
                'cart' => [
                    'id' => null,
                    'user_id' => Auth::id(),
                    'items' => [],
                ],
            ];
        }

        return $cart;
    }

    public function addToCart($skuId, $quantity)
    {
        $userId = Auth::id();
        $cart = Cart::firstOrCreate(['user_id' => $userId]);

        $sku = Sku::with('product')->findOrFail($skuId);

        // Kiểm tra kho trước khi thêm
        if ($sku->stock < $quantity) {
            return [
                'error' => 'OUT_OF_STOCK',
                'message' => 'Số lượng sản phẩm không đủ trong kho',
            ];
        }

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('sku_id', $skuId)
            ->first();

        if ($cartItem) {
            // Kiểm tra số lượng trong giỏ hàng cộng thêm số lượng mới
            if ($sku->stock < $cartItem->quantity + $quantity) {
                return [
                    'error' => 'STOCK_NOT_ENOUGH',
                    'message' => 'Số lượng sản phẩm trong kho không đủ',
                ];
            }
            // Cập nhật số lượng của sản phẩm trong giỏ hàng
            $cartItem->increment('quantity', $quantity);
        } else {
            // Thêm sản phẩm mới vào giỏ hàng
            $cartItem = $cart->items()->create([
                'sku_id' => $skuId,
                'quantity' => $quantity,
                'unit_price' => $sku->price,
            ]);
        }

        // Không trừ stock tại đây nữa
        $cart = $cartItem->cart()->with('items.sku.product')->first();

        return $cart;
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
            // Không trừ stock tại đây
            $cartItem->update(['quantity' => $newQuantity]);

            DB::commit();

            $cart = $cartItem->cart()->with('items.sku')->first();

            return $cart;

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

        // Không điều chỉnh stock tại đây
        $cartItem->update(['quantity' => $quantity]);
        $cart = $cartItem->cart()->with('items.sku')->first();

        return $cart;

    }

    public function removeCartItem($itemId)
    {
        $cartItem = CartItem::where('id', $itemId)->whereHas('cart', function ($query) {
            $query->where('user_id', Auth::id());
        })->firstOrFail();

        $cartItem->delete();
        $cart = $cartItem->cart()->with('items.sku')->first();

        return $cart;
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
