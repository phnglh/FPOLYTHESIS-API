<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends BaseController
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function createOrder(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:user_addresses,id',
        ]);

        $order = $this->orderService->createOrder($request->address_id, $request->selected_sku_ids, $request->voucher_code);

        if (isset($order['error'])) {
            return $this->errorResponse($order['error'], $order['message'], 400);
        }

        return $this->successResponse($order['order'], 'Đơn hàng đã được tạo thành công');
    }

    public function getOrders(Request $request)
    {
        $role = $request->user()->role ?? 'customer';
        $orders = $this->orderService->getOrderList($role);

        return $this->successResponse($orders, 'Danh sách đơn hàng');
    }

    public function updateStatus(Request $request, $orderId)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $result = $this->orderService->updateOrderStatus($orderId, $request->status);

        if (isset($result['error'])) {
            return $this->errorResponse($result['error'], $result['message'], 400);
        }

        return $this->successResponse($result['order'], 'Trạng thái đơn hàng đã được cập nhật');
    }

    public function cancelOrder($orderId)
    {
        $result = $this->orderService->cancelOrder($orderId);

        if (isset($result['error'])) {
            return $this->errorResponse($result['error'], $result['message'], 400);
        }

        return $this->successResponse($result['order'], 'Đơn hàng đã được hủy');
    }
}
