<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;

class VNPayService
{
    public function createPaymentUrl(Order $order)
    {
        $vnp_TmnCode = env('VNP_TMN_CODE');
        $vnp_HashSecret = env('VNP_HASH_SECRET');
        $vnp_Url = env('VNP_URL');
        $vnp_ReturnUrl = env('VNP_RETURN_URL');

        $vnp_TxnRef = $order->order_number;
        $vnp_OrderInfo = "Thanh toán đơn hàng #{$order->id}";
        $vnp_OrderType = "billpayment";
        $vnp_Amount = $order->final_total * 100;
        $vnp_Locale = "vn";
        $vnp_BankCode = "";
        $vnp_IpAddr = request()->ip();

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => now()->format('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef
        ];

        ksort($inputData);
        $query = http_build_query($inputData);
        $hashdata = urldecode($query);

        $vnpSecureHash = hash_hmac("sha512", $hashdata, $vnp_HashSecret);
        $paymentUrl = $vnp_Url . "?" . $query . "&vnp_SecureHash=" . $vnpSecureHash;

        return $paymentUrl;
    }

    public function processReturnPayment(Request $request)
    {
        $vnp_HashSecret = env('VNP_HASH_SECRET');
        $inputData = $request->except('vnp_SecureHash');
        $vnp_SecureHash = $request->input('vnp_SecureHash');

        ksort($inputData);
        $hashData = urldecode(http_build_query($inputData));
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash === $vnp_SecureHash) {
            $order = Order::where('order_number', $inputData['vnp_TxnRef'])->first();
            if ($order && $inputData['vnp_ResponseCode'] == '00') {
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'processing'
                ]);
                return ['success' => true, 'order' => $order];
            }
        }

        return ['error' => 'PAYMENT_FAILED', 'message' => 'Giao dịch không hợp lệ'];
    }
}
