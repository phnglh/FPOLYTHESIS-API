<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Voucher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder($addressId, $voucherCode = null)
    {
        $userId = Auth::id();
        $cart = Cart::where('user_id', $userId)->with('items.sku')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return ['error' => 'CART_EMPTY', 'message' => 'Giỏ hàng trống'];
        }

        return DB::transaction(function () use ($cart, $addressId, $voucherCode) {
            $subtotal = $cart->items->sum(fn ($item) => $item->quantity * $item->sku->price);
            $discount = 0;
            $voucherId = null;

            // Kiểm tra và áp dụng voucher
            if (!empty($voucherCode)) {
                $voucher = Voucher::where('code', strtoupper($voucherCode))->first();

                if ($voucher) {
                    if (!$voucher->is_active) {
                        return ['error' => 'VOUCHER_INACTIVE', 'message' => 'Mã giảm giá đã bị vô hiệu hóa'];
                    }

                    if ($voucher->usage_limit !== null && $voucher->used_count >= $voucher->usage_limit) {
                        return ['error' => 'VOUCHER_EXPIRED', 'message' => 'Mã giảm giá đã đạt giới hạn sử dụng'];
                    }

                    if ($voucher->min_order_value && $subtotal < $voucher->min_order_value) {
                        return ['error' => 'VOUCHER_MIN_ORDER', 'message' => 'Đơn hàng không đủ điều kiện áp dụng mã giảm giá'];
                    }

                    // Tính giá trị giảm giá
                    $discount = ($voucher->type === 'percentage')
                        ? ($subtotal * $voucher->discount_value / 100)
                        : $voucher->discount_value;

                    // Giảm giá không vượt quá tổng đơn hàng
                    $discount = min($discount, $subtotal);

                    $voucherId = $voucher->id;

                    // Cập nhật số lần sử dụng voucher
                    $voucher->increment('used_count');
                } else {
                    return ['error' => 'VOUCHER_NOT_FOUND', 'message' => 'Mã giảm giá không tồn tại'];
                }
            }

            $finalTotal = max($subtotal - $discount, 0); // Đảm bảo giá trị không âm

            $order = Order::create([
                'user_id' => Auth::id(),
                'address_id' => $addressId,
                'order_number' => 'FLAMES-' . strtoupper(uniqid()),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'final_total' => $finalTotal,
                'status' => 'pending', // Đơn hàng mới tạo luôn ở trạng thái 'pending'
                'payment_status' => 'unpaid', // Chưa thanh toán
                'voucher_id' => $voucherId, // Lưu ID voucher nếu có
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'sku_id' => $item->sku_id,
                    'product_name' => $item->sku->product->name,
                    'sku_code' => "DON",
                    'quantity' => $item->quantity,
                    'unit_price' => $item->sku->price,
                    'total_price' => $item->quantity * $item->sku->price,
                ]);
            }

            // Xóa giỏ hàng sau khi tạo đơn
            $cart->items()->delete();
            $cart->delete();

            return ['success' => true, 'order' => $order];
        });
    }

    public function getOrderList($role)
    {
        if ($role === 'admin') {
            return Order::with('items.sku', 'user', 'address')->paginate(10);
        } else {
            return Order::where('user_id', Auth::id())->with('items.sku', 'address')->paginate(10);
        }
    }

    public function updateOrderStatus($orderId, $status)
    {
        $order = Order::findOrFail($orderId);

        if (Auth::user()->role !== 'admin') {
            return ['error' => 'PERMISSION_DENIED', 'message' => 'Bạn không có quyền cập nhật đơn hàng'];
        }

        if (!in_array($status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
            return ['error' => 'INVALID_STATUS', 'message' => 'Trạng thái không hợp lệ'];
        }

        if ($status === 'shipped' && $order->payment_status === 'unpaid') {
            return ['error' => 'PAYMENT_REQUIRED', 'message' => 'Không thể vận chuyển đơn hàng chưa thanh toán'];
        }

        if ($status === 'delivered' && $order->status !== 'shipped') {
            return ['error' => 'ORDER_NOT_SHIPPED', 'message' => 'Chỉ có thể hoàn thành đơn hàng sau khi đã vận chuyển'];
        }

        $order->update(['status' => $status]);

        return ['success' => true, 'order' => $order];
    }

    public function cancelOrder($orderId)
    {
        $order = Order::where('id', $orderId)->where('user_id', Auth::id())->first();

        if (!$order || !in_array($order->status, ['pending', 'processing'])) {
            return ['error' => 'ORDER_CANNOT_BE_CANCELLED', 'message' => 'Không thể hủy đơn hàng'];
        }

        $order->update([
            'status' => 'cancelled',
            'payment_status' => $order->payment_status === 'paid' ? 'refunded' : 'unpaid'
        ]);

        return ['success' => true, 'order' => $order];
    }
}
