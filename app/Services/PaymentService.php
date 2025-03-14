<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Exception;

class PaymentService
{
    protected $vnPayService;

    public function __construct(VNPayService $vnPayService)
    {
        $this->vnPayService = $vnPayService;
    }

    public function createPayment($orderId, $method)
    {
        $order = Order::findOrFail($orderId);

        if ($order->status !== 'pending') {
            throw new Exception('Đơn hàng không thể thanh toán.');
        }

        if ($method === 'cod') {
            return $this->handleCODPayment($order);
        } elseif ($method === 'vnpay') {
            return $this->vnPayService->createPaymentUrl($order);
        } else {
            throw new Exception('Phương thức thanh toán không hợp lệ.');
        }
    }

    private function handleCODPayment($order)
    {
        $payment = Payment::create([
            'orderId' => $order->id,
            'paymentMethod' => 'cod',
            'amount' => $order->finalTotal,
            'status' => 'pending',
        ]);

        return [
            'message' => "Đơn hàng {$order->order_number} sẽ được thanh toán khi nhận hàng.",
            'payment_status' => $payment->status,
        ];
    }

    public function processVNPayCallback($request)
    {
        $callbackData = $this->vnPayService->handleCallback($request);
        $order = Order::where('order_number', $callbackData['order_number'])->firstOrFail();

        if ($callbackData['status'] === 'paid') {
            $order->update(['status' => 'processing']);
            Payment::create([
                'orderId' => $order->id,
                'paymentMethod' => 'vnpay',
                'transactionId' => $callbackData['transaction_id'],
                'amount' => $callbackData['amount'],
                'status' => 'paid',
                'paidAt' => now(),
            ]);

            return ['message' => 'Thanh toán VNPay thành công!'];
        } else {
            return ['error' => 'Thanh toán thất bại!'];
        }
    }
}
