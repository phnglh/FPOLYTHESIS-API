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
        try {
            $order = $this->orderService->createOrder(
                $request->address_id,
                $request->selected_sku_ids,
                $request->voucher_code,
                $request->new_address ?? [],
                $request->payment_method
            );

            if (isset($order['error'])) {
                return $this->errorResponse($order['error'], $order['message'], 400);
            }

            // Nếu phương thức thanh toán là VNPay, trả về link thanh toán
            if ($request->payment_method === 'vnpay') {
                $vnpayUrl = $this->orderService->processVNPayPayment($order['order']);
                return $this->successResponse([
                    'order' => $order['order'],  // Thêm dữ liệu order vào response
                    'payment_url' => $vnpayUrl
                ], 'Chuyển hướng đến trang thanh toán');
            }


            return $this->successResponse($order['order'], 'Đơn hàng đã được tạo thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('SERVER_ERROR', $e->getMessage(), 400);
        }
    }


    public function getOrders(Request $request)
    {
        $role = $request->user()->role ?? 'customer';
        $orders = $this->orderService->getOrderList($role);

        return $this->successResponse($orders, 'Danh sách đơn hàng');
    }


    public function getOrderDetail(Request $request, $orderId)
    {
        $role = $request->user()->role ?? 'customer';
        $order = $this->orderService->getOrderDetail($orderId, $role);

        if (isset($order['error'])) {
            return $this->errorResponse($order['error'], $order['message'], 400);
        }

        return $this->successResponse($order, 'Chi tiết đơn hàng');
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
