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
                'items.sku.attributeSkus.attributeValue'
            ])
            ->first();

        if (!$cart) {
            return [
                'status' => 'error',
                'message' => 'Giỏ hàng trống',
                'data' => []
            ];
        }

        $cartItems = $cart->items->map(function ($item) {
            return [
                'id' => $item->id,
                'cart_id' => $item->cart_id,
                'sku_id' => $item->sku_id,
                'product_name' => $item->sku->product->name ?? 'N/A',
                'attributes' => $item->sku->attributeSkus->map(function ($attrSku) {
                    return [
                        'name' => $attrSku->attribute->name,
                        'value' => $attrSku->attributeValue->value
                    ];
                }),
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'sku' => [
                    'id' => $item->sku->id,
                    'sku' => $item->sku->sku,
                    'product_id' => $item->sku->product_id,
                    'image_url' => $item->sku->image_url,
                    'price' => $item->sku->price,
                    'stock' => $item->sku->stock,

                ]
            ];
        });

        return [
            'id' => $cart->id,
            'user_id' => $cart->user_id,
            'created_at' => $cart->created_at,
            'updated_at' => $cart->updated_at,
            'items' => $cartItems
        ];
    }




    public function addToCart($skuId, $quantity)
    {
        $userId = Auth::id();
        $cart = Cart::firstOrCreate(['user_id' => $userId]);

        // Lấy SKU kèm theo product để có product_name
        $sku = Sku::with('product')->findOrFail($skuId);

        if ($sku->stock < $quantity) {
            return response()->json([
                'error' => 'OUT_OF_STOCK',
                'message' => 'Số lượng sản phẩm không đủ trong kho'
            ], 400);
        }

        // Kiểm tra sản phẩm đã có trong giỏ hàng chưa
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('sku_id', $skuId)
            ->first();

        if ($cartItem) {
            // Kiểm tra tổng số lượng có vượt kho không
            if ($sku->stock < $cartItem->quantity + $quantity) {
                return response()->json([
                    'error' => 'STOCK_NOT_ENOUGH',
                    'message' => 'Số lượng sản phẩm trong kho không đủ'
                ], 400);
            }
            $cartItem->increment('quantity', $quantity);
        } else {
            $cartItem = $cart->items()->create([
                'sku_id' => $skuId,
                'quantity' => $quantity,
                'unit_price' => $sku->price
            ]);
        }

        // Cập nhật kho
        $sku->decrement('stock', $quantity);

        // Lấy danh sách giỏ hàng sau khi cập nhật
        $cartItems = $cart->items()->with('sku.product')->get()->map(function ($item) {
            return [
                'sku_id' => $item->sku_id,
                'product_name' => $item->sku->product->name ?? 'N/A',
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ];
        });

        return response()->json([
            'success' => true,
            'cart' => $cartItems
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

    // public function updateCartItemQuantity($itemId, $quantityChange)
    // {
    //     $cartItem = CartItem::where('id', $itemId)->whereHas('cart', function ($query) {
    //         $query->where('user_id', Auth::id());
    //     })->first();

    //     if (!$cartItem) {
    //         return response()->json([
    //             'error' => 'ITEM_NOT_FOUND',
    //             'message' => 'Không tìm thấy sản phẩm trong giỏ hàng'
    //         ], 404);
    //     }

    //     $sku = Sku::find($cartItem->sku_id);
    //     if (!$sku) {
    //         return response()->json([
    //             'error' => 'SKU_NOT_FOUND',
    //             'message' => 'Không tìm thấy SKU sản phẩm'
    //         ], 404);
    //     }

    //     $newQuantity = $cartItem->quantity + $quantityChange;

    //     if ($newQuantity <= 0) {
    //         $cartItem->delete();
    //         return response()->json([
    //             'error' => 'ITEM_REMOVED',
    //             'message' => 'Sản phẩm đã được xóa khỏi giỏ hàng'
    //         ], 200);
    //     }

    //     if ($sku->stock < $quantityChange) {
    //         return response()->json([
    //             'error' => 'STOCK_NOT_ENOUGH',
    //             'message' => 'Số lượng sản phẩm không đủ'
    //         ], 400);
    //     }

    //     $sku->decrement('stock', $quantityChange);
    //     $cartItem->increment('quantity', $quantityChange);

    //     return response()->json(['success' => true, 'cart' => $cartItem->cart->load('items.sku')]);
    // }
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
                'message' => 'Không tìm thấy sản phẩm trong giỏ hàng'
            ], 404);
        }

        $sku = Sku::find($cartItem->sku_id);
        if (!$sku) {
            return response()->json([
                'error' => 'SKU_NOT_FOUND',
                'message' => 'Không tìm thấy SKU sản phẩm'
            ], 404);
        }

        $newQuantity = $cartItem->quantity + $quantityChange;

        // Nếu số lượng <= 0, xóa sản phẩm khỏi giỏ hàng
        if ($newQuantity <= 0) {
            $cartItem->delete();
            return response()->json([
                'success' => true,
                'message' => 'Sản phẩm đã được xóa khỏi giỏ hàng'
            ], 200);
        }

        // Kiểm tra tồn kho
        if ($newQuantity > $sku->stock) {
            return response()->json([
                'error' => 'STOCK_NOT_ENOUGH',
                'message' => 'Số lượng sản phẩm không đủ'
            ], 400);
        }

        // Dùng transaction để đảm bảo cả hai thao tác thành công
        DB::beginTransaction();
        try {
            $sku->decrement('stock', $quantityChange); // Chỉ giảm stock khi quantityChange là số dương
            $cartItem->update(['quantity' => $newQuantity]);

            DB::commit();

            return response()->json([
                'success' => true,
                'cart' => $cartItem->cart->load('items.sku')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'UPDATE_FAILED',
                'message' => 'Cập nhật giỏ hàng thất bại'
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
                'message' => 'Không tìm thấy sản phẩm trong giỏ hàng'
            ], 404);
        }

        $sku = Sku::find($cartItem->sku_id);
        if (!$sku) {
            return response()->json([
                'error' => 'SKU_NOT_FOUND',
                'message' => 'Không tìm thấy SKU sản phẩm'
            ], 404);
        }

        if ($quantity <= 0) {
            $cartItem->delete();
            return response()->json([
                'error' => 'ITEM_REMOVED',
                'message' => 'Sản phẩm đã được xóa khỏi giỏ hàng'
            ], 200);
        }

        if ($sku->stock < ($quantity - $cartItem->quantity)) {
            return response()->json([
                'error' => 'STOCK_NOT_ENOUGH',
                'message' => 'Số lượng sản phẩm không đủ'
            ], 400);
        }

        $difference = $quantity - $cartItem->quantity;
        if ($difference > 0) {
            $sku->decrement('stock', $difference);
        } elseif ($difference < 0) {
            $sku->increment('stock', abs($difference));
        }

        $cartItem->update(['quantity' => $quantity]);
        return response()->json(['success' => true, 'cart' => $cartItem->cart->load('items.sku')]);
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
