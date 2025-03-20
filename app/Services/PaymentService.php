<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    protected $vnpayService;

    public function __construct(VNPayService $vnpayService)
    {
        $this->vnpayService = $vnpayService;
    }

    public function processPayment($orderId, $paymentMethod)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', Auth::id())
            ->where('payment_status', 'unpaid') // Chỉ xử lý đơn hàng chưa thanh toán
            ->first();

        if (!$order) {
            return ['error' => 'ORDER_NOT_FOUND', 'message' => 'Order not found or already paid'];
        }

        return DB::transaction(function () use ($order, $paymentMethod) {
            if ($paymentMethod === 'cod') {
                // Xử lý thanh toán COD (Trạng thái mặc định là 'pending' để xác nhận giao hàng)
                $order->update([
                    'payment_status' => 'pending', // Đợi thanh toán khi giao hàng
                    'status' => 'processing', // Đơn hàng đang được xử lý
                ]);

                // Lưu vào bảng payments
                Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => 'cod',
                    'amount' => $order->final_total,
                    'status' => 'pending', // Đợi khách nhận hàng rồi thanh toán
                    'transaction_id' => null,
                    'paid_at' => null,
                    'payment_details' => null,
                ]);

                return ['success' => true, 'order' => $order, 'message' => 'Order placed successfully. Pay on delivery.'];
            } elseif ($paymentMethod === 'vnpay') {
                // Xử lý thanh toán VNPay
                $paymentUrl = $this->vnpayService->createPaymentUrl($order);

                // Lưu vào bảng payments
                Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => 'vnpay',
                    'amount' => $order->final_total,
                    'status' => 'pending', // Đang chờ khách thanh toán qua VNPay
                    'transaction_id' => null,
                    'paid_at' => null,
                    'payment_details' => null,
                ]);

                // Cập nhật trạng thái đơn hàng
                $order->update([
                    'payment_status' => 'pending', // Chờ xác nhận từ VNPay
                ]);

                return ['success' => true, 'payment_url' => $paymentUrl];
            }

            return ['error' => 'INVALID_PAYMENT_METHOD', 'message' => 'Unsupported payment method'];
        });
    }
}
