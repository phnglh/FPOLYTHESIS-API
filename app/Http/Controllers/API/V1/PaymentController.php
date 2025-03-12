<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Api\BaseController;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Exception;

class PaymentController extends BaseController
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

        // Create a payment
    public function createPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'method' => 'required|in:cod,vnpay'
        ]);

        try {
            $payment = $this->paymentService->createPayment($request->order_id, $request->method);
            return $this->successResponse($payment, "Payment created successfully.");
        } catch (Exception $e) {
            return $this->errorResponse("PAYMENT_CREATION_FAILED", $e->getMessage());
        }
    }

    
        // Handle VNPay callback
    public function handleVNPayCallback(Request $request)
    {
        try {
            $paymentResponse = $this->paymentService->processVNPayCallback($request);
            return $this->successResponse($paymentResponse, "VNPay payment processed successfully.");
        } catch (Exception $e) {
            return $this->errorResponse("VNPAY_PROCESSING_FAILED", $e->getMessage());
        }
    }
}