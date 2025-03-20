<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
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

        return $this->successResponse($paymentResult, 'Payment initiated successfully');
    }

    public function vnpayReturn(Request $request)
    {
        $result = $this->vnpayService->processReturnPayment($request);

        if (isset($result['error'])) {
            return $this->errorResponse($result['error'], $result['message'], 400);
        }

        return $this->successResponse($result['order'], 'Payment successful');
    }
}
