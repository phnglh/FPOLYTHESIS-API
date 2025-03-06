<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Exception;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function createPayment(Request $request)
    {
        $request->validate([
            'orderId' => 'required|exists:orders,id',
            'method' => 'required|in:cod,vnpay'
        ]);

        try {
            return response()->json($this->paymentService->createPayment($request->order_id, $request->method));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function handleVNPayCallback(Request $request)
    {
        return response()->json($this->paymentService->processVNPayCallback($request));
    }
}
