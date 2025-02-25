<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Services\OrderService;
use Exception;
use Illuminate\Support\Facades\Auth;


class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    // tạo đơn hàng mới
    public function createOrder(OrderRequest $request)
    {
        try {
            $order = $this->orderService->createOrder(
                Auth::id(),
                $request->validated()['items'],
                $request->validated()['shipping_address'],
                $request->validated()['notes'] ?? null
            );

            return response()->json(['order' => $order, 'message' => 'Đơn hàng tạo thành công'], 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // lấy đơn hàng chi tiết
    public function getOrderDetails($id)
    {
        try {
            $order = $this->orderService->getOrderDetails($id);
            return response()->json(['order' => $order], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    //xem lịch sử trạng thái đơn hàng
    public function getOrderHistory($id)
    {
        try {
            $history = $this->orderService->getOrderHistory($id);
            return response()->json(['history' => $history], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    // Khách hàng hủy đơn hàng

    public function cancelOrder($id)
    {
        try {
            $response = $this->orderService->cancelOrder($id,  Auth::id());
            return response()->json($response, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}