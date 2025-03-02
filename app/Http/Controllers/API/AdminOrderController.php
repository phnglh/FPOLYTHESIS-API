<?php

namespace App\Http\Controllers;

use App\Services\AdminOrderService;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;


class AdminOrderController extends Controller
{
    protected $adminOrderService;

    public function __construct(AdminOrderService $adminOrderService)
    {
        $this->adminOrderService = $adminOrderService;
    }

    /**
     * Lấy danh sách đơn hàng (có filter)
     */
    public function listOrders(Request $request)
    {
        try {
            $orders = $this->adminOrderService->listOrders($request->all());
            return response()->json(['orders' => $orders], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Xem chi tiết đơn hàng
     */
    public function getOrderDetails($id)
    {
        try {
            $order = $this->adminOrderService->getOrderDetails($id);
            return response()->json(['order' => $order], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Xem lịch sử trạng thái đơn hàng
     */
    public function getOrderHistory($id)
    {
        try {
            $history = $this->adminOrderService->getOrderHistory($id);
            return response()->json(['history' => $history], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $order = $this->adminOrderService->updateOrderStatus($id, $request->status, Auth::id());
            return response()->json(['order' => $order], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Xóa đơn hàng (Chỉ xóa nếu chưa giao)
     */
    public function deleteOrder($id)
    {
        try {
            $response = $this->adminOrderService->deleteOrder($id);
            return response()->json($response, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}