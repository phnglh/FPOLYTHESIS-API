<?php

namespace App\Services;

class VNPayService
{
    protected $vnp_TmnCode;

    protected $vnp_HashSecret;

    protected $vnp_Url;

    protected $vnp_ReturnUrl;

    public function __construct()
    {
        $this->vnp_TmnCode = env('VNPAY_TMN_CODE');
        $this->vnp_HashSecret = env('VNPAY_HASH_SECRET');
        $this->vnp_Url = env('VNPAY_URL');
        $this->vnp_ReturnUrl = env('VNPAY_RETURN_URL');
    }

    public function createPaymentUrl($order)
    {
        try {
            // dd($order);

            $inputData = [
                'vnp_Version' => '2.1.0',
                'vnp_TmnCode' => $this->vnp_TmnCode,
                'vnp_Amount' => (int) ($order->finalTotal * 100),
                'vnp_Command' => 'pay',
                'vnp_CreateDate' => date('YmdHis'),
                'vnp_CurrCode' => 'VND',
                'vnp_IpAddr' => request()->ip(),
                'vnp_Locale' => 'vn',
                'vnp_OrderInfo' => "Thanh toán đơn hàng #{$order->order_number}",
                'vnp_OrderType' => 'other',
                'vnp_ReturnUrl' => $this->vnp_ReturnUrl,
                'vnp_TxnRef' => $order->order_number,

            ];

            ksort($inputData);
            // dd($inputData);
            $hashData = '';
            foreach ($inputData as $key => $value) {
                $hashData .= $key.'='.$value.'&';
            }
            $hashData = rtrim($hashData, '&');

            // dd($hashData);

            $vnpSecureHash = strtoupper(hash_hmac('sha512', $hashData, $this->vnp_HashSecret));

            // dd($vnpSecureHash);

            $paymentUrl = "{$this->vnp_Url}?{$hashData}&vnp_SecureHash={$vnpSecureHash}";

            return ['payment_url' => $paymentUrl];
        } catch (\Exception $e) {

            return $e->getMessage();
        }
    }

    public function handleCallback($request)
    {
        $inputData = $request->all();

        // kiểm tra xem 'vnp_SecureHash' có tồn tại không
        if (! isset($inputData['vnp_SecureHash'])) {
            return response()->json([
                'error' => true,
                'message' => "Thiếu tham số 'vnp_SecureHash' trong callback.",
                'received_data' => $inputData, // Log lại dữ liệu nhận được
            ], 400);
        }

        // lấy chữ ký VNPay gửi về
        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']); // Xóa chữ ký khỏi dữ liệu để kiểm tra

        // sắp xếp key theo thứ tự A-Z
        ksort($inputData);

        // tạo chuỗi dữ liệu để hash
        $hashData = '';
        foreach ($inputData as $key => $value) {
            $hashData .= $key.'='.$value.'&';
        }
        $hashData = rtrim($hashData, '&'); // Bỏ dấu & cuối cùng

        // tạo lại chữ ký
        $secureHashCheck = strtoupper(hash_hmac('sha512', $hashData, $this->vnp_HashSecret));

        // so sánh hash
        if ($secureHashCheck !== $vnp_SecureHash) {
            return response()->json([
                'error' => true,
                'message' => 'Chữ ký không hợp lệ!',
                'expected_hash' => $secureHashCheck,
                'received_hash' => $vnp_SecureHash,
                'hash_data' => $hashData,
            ], 400);
        }

        return [
            'order_number' => $inputData['vnp_TxnRef'],
            'amount' => $inputData['vnp_Amount'] / 100,
            'transaction_id' => $inputData['vnp_TransactionNo'] ?? null,
            'status' => $inputData['vnp_ResponseCode'] == '00' ? 'paid' : 'failed',
        ];
    }
}
