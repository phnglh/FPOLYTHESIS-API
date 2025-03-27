<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Services\VNPayService;
use Illuminate\Http\Request;

class PaymentController extends BaseController
{
    protected $paymentService;
    protected $vnpayService;

    public function __construct(PaymentService $paymentService, VNPayService $vnpayService)
    {
        $this->paymentService = $paymentService;
        $this->vnpayService = $vnpayService;
    }

    public function payOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|in:cod,vnpay'
        ]);

        $paymentResult = $this->paymentService->processPayment($request->order_id, $request->payment_method);

        if (isset($paymentResult['error'])) {
            return $this->errorResponse($paymentResult['error'], $paymentResult['message'], 400);
        }

        // Nếu thanh toán VNPay, đảm bảo có URL thanh toán
        if ($request->payment_method === 'vnpay' && isset($paymentResult['payment_url'])) {
            return $this->successResponse([
                'order' => $paymentResult['order'] ?? null,
                'payment_url' => $paymentResult['payment_url']
            ], 'Chuyển hướng đến trang thanh toán');
        }

        // Nếu thanh toán COD, trả về thông tin đơn hàng
        return $this->successResponse([
            'order' => $paymentResult['order'] ?? null
        ], 'Đơn hàng đã được tạo thành công');
    }


    public function vnPayCallback(Request $request)
    {
        $vnp_HashSecret = env('VNP_HASH_SECRET');
        $inputData = $request->all();

        // Kiểm tra mã bảo mật
        if (!isset($inputData['vnp_SecureHash'])) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu mã bảo mật',
                'data' => null,
                'errors' => ['secure_hash' => 'vnp_SecureHash không tồn tại']
            ], 400);
        }

        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']); // Loại bỏ trước khi hash

        ksort($inputData);
        $hashData = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        if ($secureHash !== $vnp_SecureHash) {
            return response()->json([
                'success' => false,
                'message' => 'Giao dịch không hợp lệ!',
                'data' => null,
                'errors' => ['secure_hash' => 'Sai mã bảo mật']
            ], 400);
        }

        // Kiểm tra đơn hàng
        $order = Order::where('order_number', $inputData['vnp_TxnRef'])->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng',
                'data' => null,
                'errors' => ['order' => 'Order not found']
            ], 404);
        }

        // Lấy thông tin thanh toán
        $payment = Payment::where('order_id', $order->id)->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy giao dịch thanh toán',
                'data' => null,
                'errors' => ['payment' => 'Payment record not found']
            ], 404);
        }

        if ($inputData['vnp_ResponseCode'] == '00') {

            // Cập nhật trạng thái thanh toán của đơn hàng
            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing'
            ]);

            // Cập nhật bảng payments
            $payment->update([
                'transaction_id' => $inputData['vnp_TransactionNo'] ?? null, // Mã giao dịch VNPay
                'status' => 'paid',
                'paid_at' => now(),
                'payment_details' => json_encode($inputData),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thanh toán thành công',
                'data' => $order,
                'errors' => null
            ]);
        } else {

            // Nếu giao dịch thất bại
            $payment->update([
                'status' => 'failed',
                'payment_details' => json_encode($inputData),
            ]);

            $order->update([
                'payment_status' => 'failed'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Thanh toán thất bại',
                'data' => $order,
                'errors' => ['payment' => 'Transaction failed']
            ]);
        }
    }


    public function retryPayment(Request $request, $orderId)
    {
        $order = Order::with('payment')->find($orderId);

        if (!$order || !$order->payment) {
            return response()->json([
                'success' => false,
                'message' => 'Order or payment not found',
                'errors' => ['payment' => 'Invalid order or payment']
            ], 400);
        }

        // Kiểm tra trạng thái thanh toán trước đó
        if ($order->payment->status !== 'failed') {
            return response()->json([
                'success' => false,
                'message' => 'Only failed payments can be retried',
                'errors' => ['payment' => 'Payment retry is not allowed']
            ], 400);
        }

        // Xác nhận phương thức thanh toán là VNPay
        if ($order->payment->payment_method !== 'vnpay') {
            return response()->json([
                'success' => false,
                'message' => 'This payment method does not support retry',
                'errors' => ['payment' => 'Invalid payment method']
            ], 400);
        }

        // Gọi lại VNPayService để tạo yêu cầu thanh toán mới
        $vnpayService = new VNPayService();
        $paymentUrl = $vnpayService->createPaymentUrl($order);

        return response()->json([
            'success' => true,
            'message' => 'Retry payment initiated',
            'data' => ['redirect_url' => $paymentUrl],
            'errors' => null
        ]);
    }



}
