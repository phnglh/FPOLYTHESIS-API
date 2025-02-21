<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // Lấy danh sách giỏ hàng của người dùng
    public function index()
    {
        $cart = Cart::where('user_id', Auth::id())->with('productVariant')->get();
        return response()->json($cart);
    }

    // Thêm sản phẩm vào giỏ hàng
    public function store(Request $request)
    {
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'product_variant_id' => $request->product_variant_id,
            ],
            ['quantity' => $request->quantity]
        );

        return response()->json(['message' => 'Added to cart', 'cart' => $cart]);
    }

    // Cập nhật số lượng sản phẩm
    public function update(Request $request, $id)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        $cart = Cart::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $cart->update(['quantity' => $request->quantity]);

        return response()->json(['message' => 'Cart updated', 'cart' => $cart]);
    }

    // Xóa sản phẩm khỏi giỏ hàng
    public function destroy($id)
    {
        Cart::where('id', $id)->where('user_id', Auth::id())->delete();
        return response()->json(['message' => 'Item removed from cart']);
    }
}
