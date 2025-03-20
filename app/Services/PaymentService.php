<?php

namespace App\Services;

use App\Models\Order;
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
            ->where('payment_status', 'unpaid')
            ->first();

        if (!$order) {
            return ['error' => 'ORDER_NOT_FOUND', 'message' => 'Order not found or already paid'];
        }

        return DB::transaction(function () use ($order, $paymentMethod) {
            if ($paymentMethod === 'cod') {
                // Xử lý thanh toán COD
                $order->update([
                    'payment_status' => 'pending',
                    'status' => 'processing',
                ]);

                return ['success' => true, 'order' => $order, 'message' => 'Order placed successfully. Pay on delivery.'];
            } elseif ($paymentMethod === 'vnpay') {
                // Xử lý thanh toán VNPay
                $paymentUrl = $this->vnpayService->createPaymentUrl($order);
                return ['success' => true, 'payment_url' => $paymentUrl];
            }

            return ['error' => 'INVALID_PAYMENT_METHOD', 'message' => 'Unsupported payment method'];
        });
    }
}
