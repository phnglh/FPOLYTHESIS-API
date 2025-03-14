<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderLog;
use App\Models\OrderStatusHistory;
use App\Models\Sku;
use Exception; // them moi
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder($userID, $items, $shippingAddress, $notes = null)
    {
        return
            DB::transaction(function () use ($userID, $items, $shippingAddress, $notes) {
                $total = 0;

                $sku = Sku::with('product')->whereIn('id', collect($items)->pluck('skuId'))->get()->keyBy('id');

                foreach ($items as $item) {

                    $sku = Sku::find($item['skuId']);

                    if (! $sku || $sku->stock < $item['quantity']) {
                        throw new Exception("Sản phẩm '{$sku->product->name}' (SKU: {$item['skuId']}) không đủ hàng");
                    }

                    $total += $sku->price * $item['quantity'];
                }

                $order = Order::create([
                    'userId' => $userID,
                    'orderNumber' => 'ORD'.time(),
                    'total' => $total,
                    'finalTotal' => $total,
                    'shippingAddress' => $shippingAddress,
                    'notes' => $notes,
                    'status' => 'pending',
                ]);

                foreach ($items as $item) {
                    $sku = Sku::find($item['skuId']);

                    OrderDetail::create([
                        'orderId' => $order->id,
                        'skuId' => $item['skuId'],
                        'quantity' => $item['quantity'],
                        'price' => $sku->price,
                        'totalPrice' => $sku->price * $item['quantity'],
                        'productName' => $sku->product->name ?? 'không tìm thấy sản phẩm',
                        'productAttributes' => json_encode([
                            'color' => $sku->color ?? 'N/A',
                            'size' => $sku->size ?? 'N/A',
                        ]),
                    ]);

                    $sku->decrement('stock', $item['quantity']);
                }

                // OrderStatusHistory::create([
                //     'orderId' => $order->id,
                //     'oldStatus' => null,
                //     'newStatus' => 'pending',
                //     'changedBy' => $userID,
                //     'reason' => 'Đơn hàng mới được tạo.'
                // ]);

                // OrderLog::create([
                //     'orderId' => $order->id,
                //     'userId' => $userID,
                //     'action' => 'create_order',
                //     'description' => 'Khách hàng tạo đơn hàng mới',
                //     'logged_at' => now()
                // ]);

                return $order;
            });
    }

    // lấy đơn hàng chi tiết
    public function getOrderDetails($orderId)
    {
        $OrderDetail = Order::with(['orderDetail.sku', 'orderStatusHistories', 'orderLogs'])->findOrFail($orderId);

        if (! $OrderDetail) {
            throw new ApiException(
                'không tìm thấy sản phẩm',
                404
            );
        }

        return $OrderDetail;

    }

    // lấy lịch sử trạng thái đơn hàng
    public function getOrderHistory($orderId)
    {
        $OrderHistory = OrderStatusHistory::where('order_id', $orderId)->orderBy('created_at', 'asc')->get();

        if (! $OrderHistory) {
            throw new ApiException(
                'không tìm thấy sản phẩm',
                404
            );
        }

        return $OrderHistory;

    }

    // Khách hàng hủy đơn hàng
    public function cancelOrder($orderID, $userID)
    {
        return DB::transaction(function () use ($orderID, $userID) {
            $order = Order::where('id', $orderID)->where('user_id', $userID)->firstOrFail();

            if ($order->status === 'shipped' || $order->status === 'delivered') {
                throw new Exception('Không thể hủy đơn hàng đã giao.');
            }

            if ($order->status === 'cancelled') {
                throw new Exception('Đơn hàng này đã bị hủy trước đó.QƯ');
            }

            $order->update(['status' => 'cancelled']);

            foreach ($order->orderDetails as $detail) {
                $sku = Sku::find($detail->skuId);
                if ($sku) {
                    $sku->increment('stock', $detail->quantity);
                }
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'old_status' => $order->status,
                'new_status' => 'cancelled',
                'changed_by' => $userID,
                'reason' => 'Khách hàng đã hủy đơn hàng.',
            ]);

            OrderLog::create([
                'order_id' => $order->id,
                'user_id' => $userID,
                'action' => 'order_cancelled',
                'description' => "Khách hàng đã hủy đơn hàng {$order->order_number}",
            ]);

            return ['message' => 'Đơn hàng đã được hủy thành công.'];
        });
    }

    // lấy danh sách đơn hàng
    public function listOrders($user, $filters)
    {
        $query = Order::query();

        if ($user->role === 'customer') {
            $query->where('user_id', $user->id);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->with('orderDetails.sku')->paginate(10);
    }

    // Admin cập nhật đơn hàng
    public function updateOrderStatus($orderId, $newStatus, $adminId)
    {
        return DB::transaction(function () use ($orderId, $newStatus, $adminId) {
            $order = Order::findOrFail($orderId);
            $oldStatus = $order->status;

            if ($oldStatus === 'delivered' || $oldStatus === 'cancelled') {
                throw new Exception('Không thể cập nhật trạng thái đơn hàng đã hoàn thành hoặc bị hủy.');
            }

            $order->update(['status' => $newStatus]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $adminId,
                'reason' => 'Admin cập nhật trạng thái.',
            ]);

            return $order;
        });
    }

    // xóa đơn hàng
    public function deleteOrder($orderId)
    {
        return DB::transaction(function () use ($orderId) {
            $order = Order::findOrFail($orderId);

            if ($order->status === 'shipped' || $order->status === 'delivered') {
                throw new Exception('Không thể xóa đơn hàng đã giao.');
            }

            $order->delete();

            return ['message' => 'Đơn hàng đã được xóa thành công.'];
        });
    }
}
