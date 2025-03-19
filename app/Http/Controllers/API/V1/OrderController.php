<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Api\BaseController;
use App\Services\OrderService;
use Illuminate\Http\Request;
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


        $order = $this->orderService->createOrder(
            $request
        );



        return $this->successResponse($order, 'Order created successfully.');
    }

    public function getOrderDetails($id)
    {
        $order = $this->orderService->getOrderDetails($id);

        return $this->successResponse($order, 'Order details retrieved successfully.');
    }

    public function getOrderHistory($id)
    {
        $history = $this->orderService->getOrderHistory($id);
        return $this->successResponse($history, 'Order history retrieved successfully.');
    }

    // Khách hàng hủy đơn hàng
    public function cancelOrder($id)
    {
        $response = $this->orderService->cancelOrder($id, Auth::id());

        return $this->successResponse($response, 'Order cancelled successfully.');
    }

    public function listOrders(Request $request)
    {
        $orders = $this->orderService->listOrders(Auth::user(), $request->all());

        return $this->successResponse($orders, 'Orders retrieved successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $order = $this->orderService->updateOrderStatus($id, $request->status, Auth::id());

        return $this->successResponse($order, 'Order status updated successfully.');
    }

    public function deleteOrder($id)
    {
        $response = $this->orderService->deleteOrder($id);

        return $this->successResponse($response, 'Order deleted successfully.');
    }
}
