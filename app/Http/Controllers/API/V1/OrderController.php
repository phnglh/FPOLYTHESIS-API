<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Api\BaseController;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;

class OrderController extends BaseController
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
                $request['shipping_address'],
                $request['notes'] ?? null
            );

            return $this->successResponse($order, "Order created successfully.");
        } catch (Exception $e) {
            return $this->errorResponse("ORDER_CREATION_FAILED", $e->getMessage());
        }
    }

    public function getOrderDetails($id)
    {
        try {
            $order = $this->orderService->getOrderDetails($id);
            return $this->successResponse($order, "Order details retrieved successfully.");
        } catch (Exception $e) {
            return $this->errorResponse("ORDER_NOT_FOUND", $e->getMessage(), 404);
        }
    }

    //xem lịch sử trạng thái đơn hàng
    public function getOrderHistory($id)
    {
        try {
            $history = $this->orderService->getOrderHistory($id);
            return $this->successResponse($history, "Order history retrieved successfully.");
        } catch (Exception $e) {
            return $this->errorResponse("ORDER_HISTORY_ERROR", $e->getMessage());
        }
    }


    // Khách hàng hủy đơn hàng
    public function cancelOrder($id)
    {
        try {
            $response = $this->orderService->cancelOrder($id, Auth::id());
            return $this->successResponse($response, "Order cancelled successfully.");
        } catch (Exception $e) {
            return $this->errorResponse("ORDER_CANCELLATION_FAILED", $e->getMessage());
        }
    }
    // lấy danh sách đơn hàng
    public function listOrders(Request $request)
    {
        try {
            $orders = $this->orderService->listOrders(Auth::user(), $request->all());
            return $this->successResponse($orders, "Orders retrieved successfully.");
        } catch (Exception $e) {
            return $this->errorResponse("ORDER_LIST_ERROR", $e->getMessage());
        }
    }

    // Admin cập nhật đơn hàng
    public function updateStatus(Request $request, $id)
    {
        try {
            $order = $this->orderService->updateOrderStatus($id, $request->status, Auth::id());
            return $this->successResponse($order, "Order status updated successfully.");
        } catch (Exception $e) {
            return $this->errorResponse("ORDER_UPDATE_FAILED", $e->getMessage());
        }
    }

    // Admin xóa đơn hàng
    public function deleteOrder($id)
    {
        try {
            $response = $this->orderService->deleteOrder($id);
            return $this->successResponse($response, "Order deleted successfully.");
        } catch (Exception $e) {
            return $this->errorResponse("ORDER_DELETION_FAILED", $e->getMessage());
        }
    }
}