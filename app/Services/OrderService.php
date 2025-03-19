<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderLog;
use App\Models\OrderStatusHistory;
use App\Models\Sku;
use App\Models\ShippingMethod;
use Exception;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder($request)
    {
        // Kiểm tra và lấy thông tin items
        $items = is_array($request->items) ? $request->items : json_decode($request->items, true);
        if (!is_array($items) || empty($items)) {
            throw new Exception("Danh sách sản phẩm không hợp lệ.");
        }

        $userID = auth()->id();
        $subtotal = 0;

        // Lấy danh sách SKU từ DB
        $skuIds = collect($items)->pluck('sku_id')->toArray();
        $skus = Sku::whereIn('id', $skuIds)->get()->keyBy('id');

        // Kiểm tra tồn kho và tính tổng giá trị đơn hàng
        foreach ($items as $item) {
            if (!isset($skus[$item['sku_id']]) || $skus[$item['sku_id']]->stock < $item['quantity']) {
                throw new Exception("Sản phẩm '{$item['sku_id']}' không đủ hàng.");
            }
            $subtotal += $skus[$item['sku_id']]->price * $item['quantity'];
        }

        $shippingMethod = ShippingMethod::where('is_express', $request->is_express)->first();

        if (!$shippingMethod) {
            throw new Exception("Phương thức vận chuyển không tồn tại.");
        }

        // Kiểm tra & áp dụng mã giảm giá
        $discount = 0;
        if ($request->coupon_code) {
            $voucherService = new VoucherService();
            $voucherResult = $voucherService->apply($request->coupon_code, $subtotal);

            if ($voucherResult['success']) {
                $discount = $voucherResult['discount'];
            } else {
                throw new Exception($voucherResult['message']);
            }
        }
        // Tính tổng tiền đơn hàng
        $finalTotal = max(0, $subtotal - $discount + $shippingMethod->price);

        // Tạo đơn hàng
        $order = Order::create([
            'user_id'           => $userID,
            'order_number'      => 'FLAMES' . time(),
            'subtotal'          => $subtotal,
            'discount'          => $discount,
            'final_total'       => $finalTotal,
            'shipping_address'  => $request->shipping_address,
            'shipping_method_id' => $shippingMethod->id,
            'shipping_fee'      => $shippingMethod->price,
            'status'            => 'pending',
            'notes'             => $request->notes ?? null,
            'coupon_code'       => $request->coupon_code ?? null,
        ]);
        // Thêm sản phẩm vào đơn hàng và cập nhật tồn kho
        foreach ($items as $item) {
            $sku = $skus[$item['sku_id']];
            OrderItem::create([
                'order_id'     => $order->id,
                'sku_id'       => $sku->id,
                'quantity'     => $item['quantity'],
                'unit_price'   => $sku->price,
                'total_price'  => $sku->price * $item['quantity'],
                'product_name' => $sku->product->name ?? 'Sản phẩm không xác định',
                'sku_code'     => $sku->sku,
            ]);

            $sku->decrement('stock', $item['quantity']);
        }

        // Ghi lại lịch sử trạng thái đơn hàng
        OrderStatusHistory::create([
            'order_id'   => $order->id,
            'old_status' => 'pending',
            'new_status' => 'pending',
            'changed_by' => $userID,
            'reason'     => 'Đơn hàng mới được tạo.',
        ]);

        // Ghi log đơn hàng
        OrderLog::create([
            'order_id'    => $order->id,
            'user_id'     => $userID,
            'action'      => 'create_order',
            'description' => 'Khách hàng tạo đơn hàng mới.',
        ]);

        return $order;
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
            if (in_array($order->status, ['shipped', 'delivered', 'cancelled'])) {
                throw new Exception('Không thể hủy đơn hàng đã giao hoặc đã bị hủy.');
            }
            $order->update(['status' => 'cancelled']);
            foreach ($order->orderItems as $item) {
                $sku = Sku::find($item->sku_id);
                if ($sku) {
                    $sku->increment('stock', $item->quantity);
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
