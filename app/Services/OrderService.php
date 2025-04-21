<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Sku;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function processVNPayPayment($order)
    {
        $vnpayService = new VNPayService();
        return $vnpayService->createPaymentUrl($order);
    }

    public function createOrder($selectedSkuIds, $addressId = null, $voucherCode = null, $newAddress = [], $paymentMethod = 'cod')
    {
        $userId = Auth::id();
        $cart = Cart::where('user_id', $userId)->with('items.sku')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return ['error' => 'CART_EMPTY', 'message' => 'Giỏ hàng trống'];
        }

        return DB::transaction(function () use ($cart, $addressId, $selectedSkuIds, $voucherCode, $newAddress, $userId, $paymentMethod) {
            $userAddressService = new UserAddressService();

            // Nếu không có addressId, tạo địa chỉ mới
            if (!$addressId && !empty($newAddress)) {
                $newAddress['user_id'] = $userId;
                $address = $userAddressService->createUserAddress($newAddress);
                $addressId = $address->id;
            }

            if (!$addressId) {
                return ['error' => 'ADDRESS_REQUIRED', 'message' => 'Địa chỉ không hợp lệ'];
            }

            $subtotal = 0;
            $orderItems = [];
            if (!is_array($selectedSkuIds) || empty($selectedSkuIds)) {
                return ['error' => 'INVALID_SKU_IDS', 'message' => 'Mã sản phẩm không hợp lệ'];
            }
            foreach ($selectedSkuIds as $skuId) {
                $cartItem = $cart->items->where('sku_id', $skuId)->first();
                if (!$cartItem) {
                    return ['error' => 'ITEM_NOT_FOUND', 'message' => 'Sản phẩm không tồn tại trong giỏ hàng'];
                }

                // Kiểm tra tồn kho trước khi tạo đơn
                if ($cartItem->sku->stock < $cartItem->quantity) {
                    return ['error' => 'OUT_OF_STOCK', 'message' => "Sản phẩm {$cartItem->sku->sku} không đủ tồn kho"];
                }

                $orderItems[] = [
                    'sku_id' => $cartItem->sku_id,
                    'product_name' => $cartItem->sku->product->name,
                    'sku' => $cartItem->sku->sku,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->sku->price,
                    'total_price' => $cartItem->quantity * $cartItem->sku->price,
                ];

                $subtotal += $cartItem->quantity * $cartItem->sku->price;
            }

            $discount = 0;
            $voucherId = null;

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

                    $discount = ($voucher->type === 'percentage')
                        ? ($subtotal * $voucher->discount_value / 100)
                        : $voucher->discount_value;

                    $discount = min($discount, $subtotal);
                    $voucherId = $voucher->id;
                    $voucher->increment('used_count');
                } else {
                    return ['error' => 'VOUCHER_NOT_FOUND', 'message' => 'Mã giảm giá không tồn tại'];
                }
            }

            $finalTotal = max($subtotal - $discount, 0);

            // Tạo đơn hàng
            $order = Order::create([
                'user_id' => $userId,
                'address_id' => $addressId,
                'order_number' => 'FLAMES-' . strtoupper(uniqid()),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'final_total' => $finalTotal,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'voucher_id' => $voucherId,
            ]);

            foreach ($orderItems as $item) {
                OrderItem::create(array_merge(['order_id' => $order->id], $item));
            }

            // Gọi PaymentService để xử lý thanh toán ngay lúc tạo đơn
            $paymentService = app(PaymentService::class);
            $paymentResult = $paymentService->processPayment($order->id, $paymentMethod);

            // Trừ tồn kho cho COD ngay sau khi tạo đơn
            if ($paymentMethod === 'cod') {
                foreach ($orderItems as $item) {
                    $sku = Sku::find($item['sku_id']);
                    $sku->decrement('stock', $item['quantity']);
                }
            }

            // Xóa sản phẩm đã mua khỏi giỏ hàng
            CartItem::where('cart_id', $cart->id)->whereIn('sku_id', $selectedSkuIds)->delete();

            // Nếu chọn VNPay, trả về link thanh toán
            if ($paymentMethod === 'vnpay') {
                return [
                    'success' => true,
                    'order' => $order,
                    'payment_url' => $paymentResult['payment_url'],
                    'message' => 'Chuyển hướng đến trang thanh toán'
                ];
            }

            return [
                'success' => true,
                'order' => $order,
                'message' => 'Đơn hàng đã được tạo và thanh toán thành công'
            ];
        });
    }

    public function getOrderList(Request $request, $perPage)
    {
        $query = Order::with([
            'items.sku.product',
            'items.sku.attributeSkus.attribute',
            'items.sku.attributeSkus.attributeValue',
            'user',
            'address',
            'payment',
            'voucher'
        ]);

        if (!Auth::user()->hasRole('admin')) {
            $query->where('user_id', Auth::id());
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $orders = $query->paginate($perPage);

        return $orders;
    }

    public function getOrderDetail($orderId, $role)
    {
        $query = Order::with([
            'items.sku.attributeSkus.attribute',
            'items.sku.attributeSkus.attributeValue',
            'items.sku.product',
            'user',
            'address',
            'payment',
            'voucher'
        ])->where('id', $orderId);

        if ($role !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        $order = $query->first();

        if (!$order) {
            return response()->json([
                'error' => 'ORDER_NOT_FOUND',
                'message' => 'Đơn hàng không tồn tại hoặc bạn không có quyền xem'
            ], 404);
        }

        return $order;
    }

    public function updateOrderStatus($orderId, $status)
    {
        $order = Order::findOrFail($orderId);

        if (Auth::user()->role !== 'admin') {
            return ['error' => 'PERMISSION_DENIED', 'message' => 'Bạn không có quyền cập nhật đơn hàng'];
        }

        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            return ['error' => 'INVALID_STATUS', 'message' => 'Trạng thái không hợp lệ'];
        }

        if ($status === 'shipped' && $order->payment_status === 'unpaid') {
            if ($order->payment->payment_method !== 'cod') {
                return ['error' => 'PAYMENT_REQUIRED', 'message' => 'Không thể vận chuyển đơn hàng chưa thanh toán'];
            }
        }

        if ($status === 'delivered' && $order->status !== 'shipped') {
            return ['error' => 'ORDER_NOT_SHIPPED', 'message' => 'Chỉ có thể hoàn thành đơn hàng sau khi đã vận chuyển'];
        }

        if ($status === 'cancelled' && $order->status === 'delivered') {
            return ['error' => 'ORDER_ALREADY_DELIVERED', 'message' => 'Không thể hủy đơn hàng đã hoàn tất'];
        }

        if ($status === 'delivered') {
            $updateData['delivered_at'] = now();

            if ($order->payment) {
                if ($order->payment->payment_method === 'cod') {
                    $updateData['payment_status'] = 'paid';
                    $order->payment->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
                }

                if ($order->payment->payment_method === 'vnpay') {
                    if ($order->payment_status !== 'paid') {
                        return ['error' => 'VNPay_PAYMENT_REQUIRED', 'message' => 'Đơn hàng VNPay chưa thanh toán, không thể giao hàng'];
                    }
                }
            }
        }

        if ($status === 'shipped') {
            $updateData['shipped_at'] = now();
        }

        $updateData['status'] = $status;
        $order->update($updateData);

        return ['success' => true, 'message' => 'Trạng thái đơn hàng đã được cập nhật', 'order' => $order];
    }

    public function cancelOrder($orderId)
    {
        $order = Order::where('id', $orderId)->where('user_id', Auth::id())->first();

        if (!$order || !in_array($order->status, ['pending', 'processing'])) {
            return ['error' => 'ORDER_CANNOT_BE_CANCELLED', 'message' => 'Không thể hủy đơn hàng'];
        }

        return DB::transaction(function () use ($order) {
            // Hoàn lại tồn kho nếu đã trừ trước đó
            $orderItems = OrderItem::where('order_id', $order->id)->get();
            foreach ($orderItems as $item) {
                $sku = Sku::find($item->sku_id);
                if ($sku) {
                    // Hoàn tồn kho nếu thanh toán COD hoặc VNPay đã thành công
                    if ($order->payment_status === 'paid' || $order->payment->payment_method === 'cod') {
                        $sku->increment('stock', $item->quantity);
                    }
                }
            }

            $order->update([
                'status' => 'cancelled',
                'payment_status' => $order->payment_status === 'paid' ? 'refunded' : 'unpaid'
            ]);

            return ['success' => true, 'order' => $order];
        });
    }

    public function checkVoucher($voucherCode, $selectedSkuIds)
    {
        $userId = Auth::id();
        $cart = Cart::where('user_id', $userId)->with('items.sku')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return ['error' => 'CART_EMPTY', 'message' => 'Giỏ hàng trống'];
        }

        // Tính tổng tiền của các sản phẩm được chọn
        $subtotal = 0;
        foreach ($selectedSkuIds as $skuId) {
            $cartItem = $cart->items->where('sku_id', $skuId)->first();
            if (!$cartItem) {
                return ['error' => 'ITEM_NOT_FOUND', 'message' => 'Sản phẩm không tồn tại trong giỏ hàng'];
            }

            if ($cartItem->sku->stock < $cartItem->quantity) {
                return ['error' => 'OUT_OF_STOCK', 'message' => "Sản phẩm {$cartItem->sku->sku} không đủ tồn kho"];
            }

            $subtotal += $cartItem->quantity * $cartItem->sku->price;
        }

        // Kiểm tra mã giảm giá
        $discount = 0;
        $voucherDetails = null;

        if (empty($voucherCode)) {
            return [
                'success' => true,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'final_total' => $subtotal,
                'message' => 'Không có mã giảm giá được áp dụng'
            ];
        }

        $voucher = Voucher::where('code', strtoupper($voucherCode))->first();
        if (!$voucher) {
            return ['error' => 'VOUCHER_NOT_FOUND', 'message' => 'Mã giảm giá không tồn tại'];
        }

        if (!$voucher->is_active) {
            return ['error' => 'VOUCHER_INACTIVE', 'message' => 'Mã giảm giá đã bị vô hiệu hóa'];
        }

        if ($voucher->usage_limit !== null && $voucher->used_count >= $voucher->usage_limit) {
            return ['error' => 'VOUCHER_EXPIRED', 'message' => 'Mã giảm giá đã đạt giới hạn sử dụng'];
        }

        if ($voucher->min_order_value && $subtotal < $voucher->min_order_value) {
            return ['error' => 'VOUCHER_MIN_ORDER', 'message' => 'Đơn hàng không đủ điều kiện áp dụng mã giảm giá'];
        }

        $discount = ($voucher->type === 'percentage')
            ? ($subtotal * $voucher->discount_value / 100)
            : $voucher->discount_value;

        $discount = min($discount, $subtotal);
        $finalTotal = max($subtotal - $discount, 0);

        return [
            'success' => true,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'final_total' => $finalTotal,
            'voucher' => [
                'code' => $voucher->code,
                'type' => $voucher->type,
                'discount_value' => $voucher->discount_value,
            ],
            'message' => 'Mã giảm giá được áp dụng thành công'
        ];
    }
}
