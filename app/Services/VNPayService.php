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
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);//
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return $vnp_Url;
    }

    public function createPaymentUrlRetry($order)
    {
        $vnp_TxnRef = $order->id;
        $vnp_Amount = $order->final_total * 100;
        $vnp_IpAddr = request()->ip();
        $vnp_OrderInfo = "Retry Payment for Order #{$order->order_number}";

        $vnp_TmnCode = env('VNP_TMN_CODE');
        $vnp_HashSecret = env('VNP_HASH_SECRET');
        $vnp_Url = env('VNP_URL');
        $vnp_ReturnUrl = env('VNP_RETURN_URL');

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_Command" => "pay",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_CurrCode" => "VND",
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => "billpayment",
            "vnp_Locale" => "vn",
            "vnp_ReturnUrl" => $vnp_ReturnUrl,
            "vnp_IpAddr" => $vnp_IpAddr,
        ];

        ksort($inputData);
        $query = "";
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            $hashdata .= $key . "=" . $value . "&";
            $query .= urlencode($key) . "=" . urlencode($value) . "&";
        }
        $query = rtrim($query, "&");
        $hashdata = rtrim($hashdata, "&");

        $vnp_SecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $paymentUrl = $vnp_Url . "?" . $query . "&vnp_SecureHash=" . $vnp_SecureHash;

        return $paymentUrl;
    }



}
