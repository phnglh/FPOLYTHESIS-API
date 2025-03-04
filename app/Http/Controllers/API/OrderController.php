<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;


class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function store(Request $request)
    {
        try {
            $order = $this->orderService->createOrder(
                Auth::id(),
                $request['items'],
                $request['shippingAddress'],
                $request['notes'] ?? null
            );

            return response()->json(['order' => $order, 'message' => 'Đơn hàng tạo thành công'], 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

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
    // lấy danh sách đơn hàng
    public function listOrders(Request $request)
    {
        try {
            $orders = $this->orderService->listOrders(Auth::user(), $request->all());
            return response()->json(['orders' => $orders], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // Admin cập nhật đơn hàng
    public function updateStatus(Request $request, $id)
    {
        try {
            $order = $this->orderService->updateOrderStatus($id, $request->status, Auth::id());
            return response()->json(['order' => $order], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // Admin xóa đơn hàng
    public function deleteOrder($id)
    {
        try {
            $response = $this->orderService->deleteOrder($id);
            return response()->json($response, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
