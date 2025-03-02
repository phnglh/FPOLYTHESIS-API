<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\DB;
use Exception;

class AdminOrderService
{
    /**
     * Lấy danh sách đơn hàng (có filter)
     */
    public function listOrders($filters)
    {
        $query = Order::query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['date_range'])) {
            $dates = explode(',', $filters['date_range']);
            if (count($dates) === 2) {
                $query->whereBetween('created_at', [$dates[0], $dates[1]]);
            }
        }

        return $query->with('orderDetails.sku')->paginate(10);
    }

    /**
     * Xem chi tiết đơn hàng
     */
    public function getOrderDetails($orderId)
    {
        return Order::with(['orderDetails.sku', 'orderStatusHistories'])->findOrFail($orderId);
    }

    /**
     * Lấy lịch sử trạng thái đơn hàng
     */
    public function getOrderHistory($orderId)
    {
        return OrderStatusHistory::where('order_id', $orderId)->orderBy('created_at', 'asc')->get();
    }

    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function updateOrderStatus($orderId, $newStatus, $adminId)
    {
        return DB::transaction(function () use ($orderId, $newStatus, $adminId) {
            $order = Order::findOrFail($orderId);
            $oldStatus = $order->status;

            if ($oldStatus === 'delivered' || $oldStatus === 'cancelled') {
                throw new Exception("Không thể cập nhật trạng thái đơn hàng đã hoàn thành hoặc bị hủy.");
            }

            $order->update(['status' => $newStatus]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $adminId,
                'reason' => "Admin cập nhật trạng thái.",
            ]);

            return $order;
        });
    }

    /**
     * Xóa đơn hàng (chỉ xóa nếu chưa giao)
     */
    public function deleteOrder($orderId)
    {
        return DB::transaction(function () use ($orderId) {
            $order = Order::findOrFail($orderId);

            if ($order->status === 'shipped' || $order->status === 'delivered') {
                throw new Exception("Không thể xóa đơn hàng đã giao.");
            }

            $order->delete();

            return ['message' => 'Đơn hàng đã được xóa thành công.'];
        });
    }
}