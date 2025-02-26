<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Http\Requests\CartRequest;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    // Lấy danh sách giỏ hàng
    public function index(Request $request)
    {
        $cart = $this->cartService->getUserCart($request->user()->id);
        return response()->json($cart);
    }

    // Thêm sản phẩm vào giỏ hàng
    public function store(CartRequest $request)
    {
        $cartItem = $this->cartService->addToCart(
            $request->user()->id,
            $request->product_id,
            $request->quantity
        );

        return response()->json(['message' => 'Thêm vào giỏ hàng thành công', 'cart' => $cartItem]);
    }

    // Cập nhật số lượng sản phẩm
    public function update(CartRequest $request, $id)
    {
        $cartItem = $this->cartService->updateCartItem($request->user()->id, $id, $request->quantity);

        if (!$cartItem) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm trong giỏ hàng'], 404);
        }

        return response()->json(['message' => 'Cập nhật thành công', 'cart' => $cartItem]);
    }

    // Xóa sản phẩm khỏi giỏ hàng
    public function destroy(Request $request, $id)
    {
        $deleted = $this->cartService->removeCartItem($request->user()->id, $id);

        if (!$deleted) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm trong giỏ hàng'], 404);
        }

        return response()->json(['message' => 'Xóa thành công']);
    }
}
