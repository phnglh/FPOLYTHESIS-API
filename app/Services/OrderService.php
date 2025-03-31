<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserAddress;
use App\Models\Voucher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function processVNPayPayment($order)
    {
        $vnpayService = new VNPayService();
        return $vnpayService->createPaymentUrl($order);
    }

    // public function createOrder($addressId = null, $selectedSkuIds, $voucherCode = null, $newAddress = [], $paymentMethod = 'cod')
    // {
    //     $userId = Auth::id();
    //     $cart = Cart::where('user_id', $userId)->with('items.sku')->first();

    //     if (!$cart || $cart->items->isEmpty()) {
    //         return ['error' => 'CART_EMPTY', 'message' => 'Giỏ hàng trống'];
    //     }

    //     return DB::transaction(function () use ($cart, $addressId, $selectedSkuIds, $voucherCode, $newAddress, $userId, $paymentMethod) {
    //         // Nếu không có addressId, tạo địa chỉ mới
    //         if (!$addressId && !empty($newAddress)) {
    //             $userAddressService = new UserAddressService();
    //             $newAddress['user_id'] = $userId;
    //             $address = $userAddressService->createUserAddress($newAddress);
    //             $addressId = $address->id;
    //         }

    //         if (!$addressId) {
    //             return ['error' => 'ADDRESS_REQUIRED', 'message' => 'Địa chỉ không hợp lệ'];
    //         }

    //         $subtotal = 0;
    //         $orderItems = [];

    //         foreach ($selectedSkuIds as $skuId) {
    //             $cartItem = $cart->items->where('sku_id', $skuId)->first();
    //             if (!$cartItem) {
    //                 return ['error' => 'ITEM_NOT_FOUND', 'message' => 'Sản phẩm không tồn tại trong giỏ hàng'];
    //             }

    //             $orderItems[] = [
    //                 'sku_id' => $cartItem->sku_id,
    //                 'product_name' => $cartItem->sku->product->name,
    //                 'sku_code' => "DON",
    //                 'quantity' => $cartItem->quantity,
    //                 'unit_price' => $cartItem->sku->price,
    //                 'total_price' => $cartItem->quantity * $cartItem->sku->price,
    //             ];

    //             $subtotal += $cartItem->quantity * $cartItem->sku->price;
    //         }

    //         $discount = 0;
    //         $voucherId = null;

    //         if (!empty($voucherCode)) {
    //             $voucher = Voucher::where('code', strtoupper($voucherCode))->first();

    //             if ($voucher) {
    //                 if (!$voucher->is_active) {
    //                     return ['error' => 'VOUCHER_INACTIVE', 'message' => 'Mã giảm giá đã bị vô hiệu hóa'];
    //                 }

    //                 if ($voucher->usage_limit !== null && $voucher->used_count >= $voucher->usage_limit) {
    //                     return ['error' => 'VOUCHER_EXPIRED', 'message' => 'Mã giảm giá đã đạt giới hạn sử dụng'];
    //                 }

    //                 if ($voucher->min_order_value && $subtotal < $voucher->min_order_value) {
    //                     return ['error' => 'VOUCHER_MIN_ORDER', 'message' => 'Đơn hàng không đủ điều kiện áp dụng mã giảm giá'];
    //                 }

    //                 $discount = ($voucher->type === 'percentage')
    //                     ? ($subtotal * $voucher->discount_value / 100)
    //                     : $voucher->discount_value;

    //                 $discount = min($discount, $subtotal);
    //                 $voucherId = $voucher->id;
    //                 $voucher->increment('used_count');
    //             } else {
    //                 return ['error' => 'VOUCHER_NOT_FOUND', 'message' => 'Mã giảm giá không tồn tại'];
    //             }
    //         }

    //         $finalTotal = max($subtotal - $discount, 0);

    //         // Tạo đơn hàng
    //         $order = Order::create([
    //             'user_id' => $userId,
    //             'address_id' => $addressId,
    //             'order_number' => 'FLAMES-' . strtoupper(uniqid()),
    //             'subtotal' => $subtotal,
    //             'discount' => $discount,
    //             'final_total' => $finalTotal,
    //             'status' => 'pending',
    //             'payment_status' => 'unpaid',
    //             'voucher_id' => $voucherId,
    //         ]);

    //         foreach ($orderItems as $item) {
    //             OrderItem::create(array_merge(['order_id' => $order->id], $item));
    //         }

    //         // Gọi PaymentService để xử lý thanh toán ngay lúc tạo đơn
    //         $paymentService = app(PaymentService::class);
    //         $paymentResult = $paymentService->processPayment($order->id, $paymentMethod);

    //         // Xóa sản phẩm đã mua khỏi giỏ hàng
    //         CartItem::where('cart_id', $cart->id)->whereIn('sku_id', $selectedSkuIds)->delete();

    //         // Nếu chọn VNPay, trả về link thanh toán
    //         if ($paymentMethod === 'vnpay') {
    //             return [
    //                 'success' => true,
    //                 'order' => $order, // Thêm order để tránh lỗi
    //                 'payment_url' => $paymentResult['payment_url'],
    //                 'message' => 'Chuyển hướng đến trang thanh toán'
    //             ];
    //         }

    //         return [
    //             'success' => true,
    //             'order' => $order,
    //             'message' => 'Đơn hàng đã được tạo và thanh toán thành công'
    //         ];
    //     });
    // }

    public function createOrder($email, $phone, $addressId = null, $selectedSkuIds, $voucherCode = null, $newAddress = [], $paymentMethod = 'cod')
    {
        $userId = Auth::id();

        if ($userId) {
            $cart = Cart::where('user_id', $userId)->with('items.sku')->first();
        } else {
            $cart = Cart::where('guest_email', $email)->with('items.sku')->first();
        }

        if (!$cart || $cart->items->isEmpty()) {
            return ['error' => 'CART_EMPTY', 'message' => 'Giỏ hàng trống'];
        }

        return DB::transaction(function () use ($cart, $email, $phone, $addressId, $selectedSkuIds, $voucherCode, $newAddress, $userId, $paymentMethod) {
            if (!$addressId && !empty($newAddress)) {

                if ($userId) {
                    $newAddress['user_id'] = $userId;
                } else {
                    $newAddress['guest_email'] = $email;
                }

                $address = UserAddress::create($newAddress);
                $addressId = $address->id;
            }



            $subtotal = 0;
            $orderItems = [];

            foreach ($selectedSkuIds as $skuId) {
                $cartItem = $cart->items->where('sku_id', $skuId)->first();
                if (!$cartItem) {
                    return ['error' => 'ITEM_NOT_FOUND', 'message' => 'Sản phẩm không tồn tại trong giỏ hàng'];
                }

                $orderItems[] = [
                    'sku_id' => $cartItem->sku_id,
                    'product_name' => $cartItem->sku->product->name,
                    'sku_code' => "DON",
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

            $order = Order::create([
                'user_id' => $userId,
                'email' => $email,
                'phone' => $phone,
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

            CartItem::where('cart_id', $cart->id)->whereIn('sku_id', $selectedSkuIds)->delete();

            if (!$userId) {
                $existingUser = User::where('email', $email)->first();
                if (!$existingUser) {
                    return [
                        'success' => true,
                        'order' => $order,
                        'ask_register' => true,
                        'message' => 'Đơn hàng đã tạo. Bạn có muốn đăng ký tài khoản không?'
                    ];
                }
            }

            return [
                'success' => true,
                'order' => $order,
                'message' => 'Đơn hàng đã được tạo thành công'
            ];
        });
    }

    public function getOrderList($role)
    {
        $query = Order::with([
            'items.sku.product', // Lấy thông tin sản phẩm từ SKU
            'user', // Lấy thông tin người dùng
            'address', // Lấy địa chỉ giao hàng
            'payment', // Thông tin thanh toán
            'voucher' // Mã giảm giá nếu có
        ]);

        if ($role !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        return $query->paginate(10);
    }

    public function updateOrderStatus($orderId, $status)
    {
        $order = Order::findOrFail($orderId);

        // Kiểm tra quyền admin
        if (Auth::user()->role !== 'admin') {
            return ['error' => 'PERMISSION_DENIED', 'message' => 'Bạn không có quyền cập nhật đơn hàng'];
        }

        // Danh sách trạng thái hợp lệ
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            return ['error' => 'INVALID_STATUS', 'message' => 'Trạng thái không hợp lệ'];
        }

        // Nếu muốn chuyển sang "shipped" nhưng chưa thanh toán (chỉ áp dụng cho online)
        if ($status === 'shipped' && $order->payment_status === 'unpaid') {
            if ($order->payment->payment_method !== 'cod') {
                return ['error' => 'PAYMENT_REQUIRED', 'message' => 'Không thể vận chuyển đơn hàng chưa thanh toán'];
            }
        }

        // Nếu muốn chuyển sang "delivered" nhưng đơn chưa "shipped"
        if ($status === 'delivered' && $order->status !== 'shipped') {
            return ['error' => 'ORDER_NOT_SHIPPED', 'message' => 'Chỉ có thể hoàn thành đơn hàng sau khi đã vận chuyển'];
        }

        // Nếu muốn hủy đơn đã được giao thành công
        if ($status === 'cancelled' && $order->status === 'delivered') {
            return ['error' => 'ORDER_ALREADY_DELIVERED', 'message' => 'Không thể hủy đơn hàng đã hoàn tất'];
        }

        // Nếu trạng thái là "delivered", xử lý logic thanh toán
        if ($status === 'delivered') {
            $updateData['delivered_at'] = now();

            if ($order->payment) {
                // Xử lý đơn hàng COD
                if ($order->payment->payment_method === 'cod') {
                    $updateData['payment_status'] = 'paid';

                    $order->payment->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
                }

                // Xử lý đơn hàng VNPay
                if ($order->payment->payment_method === 'vnpay') {
                    if ($order->payment_status !== 'paid') {
                        return ['error' => 'VNPay_PAYMENT_REQUIRED', 'message' => 'Đơn hàng VNPay chưa thanh toán, không thể giao hàng'];
                    }
                }
            }
        }

        // Nếu trạng thái là "shipped", cập nhật thời gian vận chuyển
        if ($status === 'shipped') {
            $updateData['shipped_at'] = now();
        }

        // Cập nhật trạng thái đơn hàng
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

        $order->update([
            'status' => 'cancelled',
            'payment_status' => $order->payment_status === 'paid' ? 'refunded' : 'unpaid'
        ]);

        return ['success' => true, 'order' => $order];
    }
}
