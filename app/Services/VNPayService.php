<?php

namespace App\Services;

use App\Models\Order;

class VNPayService
{
    public function createPaymentUrl(Order $order)
    {
        $vnp_TmnCode = env('VNP_TMN_CODE');
        $vnp_HashSecret = env('VNP_HASH_SECRET');
        $vnp_Url = env('VNP_URL');
        $vnp_ReturnUrl = env('VNP_RETURN_URL');

        $vnp_TxnRef = $order->id;
        $vnp_OrderInfo = "Thanh toán đơn hàng #{$order->id}";
        $vnp_OrderType = "billpayment";
        $vnp_Amount = $order->total_amount * 100;
        $vnp_Locale = "vn";
        $vnp_BankCode = "";
        $vnp_IpAddr = request()->ip();

        $inputData = array(
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
        );

        ksort($inputData);
        $query = "";
        $hashdata = "";

        foreach ($inputData as $key => $value) {
            $hashdata .= "&" . $key . "=" . $value;
            $query .= urlencode($key) . "=" . urlencode($value) . "&";
        }

        $vnp_Url .= "?" . $query;
        $vnpSecureHash = hash_hmac("sha512", ltrim($hashdata, "&"), $vnp_HashSecret);
        $vnp_Url .= "vnp_SecureHash=" . $vnpSecureHash;

        return $vnp_Url;
    }

    public function processReturnPayment($request)
    {
        $vnp_HashSecret = env('VNP_HASH_SECRET');
        $inputData = $request->all();
        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']);

        ksort($inputData);
        $hashData = "";
        foreach ($inputData as $key => $value) {
            $hashData .= "&" . $key . "=" . $value;
        }

        $secureHash = hash_hmac('sha512', ltrim($hashData, "&"), $vnp_HashSecret);

        if ($secureHash === $vnp_SecureHash) {
            $order = Order::find($inputData['vnp_TxnRef']);
            if ($inputData['vnp_ResponseCode'] == '00' && $order) {
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
