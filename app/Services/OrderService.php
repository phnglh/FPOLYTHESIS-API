<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Sku;
use App\Models\OrderStatusHistory;
use App\Models\OrderLog;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    // Tạo đơn hàng mới

    public function createOrder($userID, $items, $shippingAddress, $notes = null)
    {
        return
            DB::transaction(function () use ($userID, $items, $shippingAddress, $notes) {
                $total = 0;

                $sku = Sku::with('product')->whereIn('id', collect($items)->pluck('skuId'))->get()->keyBy('id');

                foreach ($items as $item) {

                    $sku = Sku::find($item['skuId']);

                    if (!$sku || $sku->stock < $item['quantity']) {
                        throw new Exception("Sản phẩm '{$sku->product->name}' (SKU: {$item['skuId']}) không đủ hàng");
                    }

                    $total += $sku->price * $item['quantity'];
                }

                $order = Order::create([
                    'user_id' => $userID,
                    'order_number' => 'Flames-' . strtoupper(uniqid()), // flames - render ra 1 mã ngẫu nhiên và duy nhất 
                    'total' => $total,
                    'final_total' => $total,
                    'shipping_address' => $shippingAddress,
                    'notes' => $notes,
                    'status' => 'pending',
                ]);

                foreach ($items as $item) {

                    $sku = $sku->get($item['skuId']);

                    OrderDetail::create([
                        'order_id' => $order->id,
                        'skuId' => $item['skuId'],
                        'quantity' => $item['quantity'],
                        'price' => $sku->price,
                        'total_price' => $sku->price * $item['quantity'],
                        'product_name' => $sku->product->name ?? 'không tìm thấy sản phẩm',
                        'product_attributes' => json_encode([
                            'color' => $sku->color ?? 'N/A',
                            'size' => $sku->size ?? 'N/A'
                        ]),
                    ]);

                    $sku->decrement('stock', $item['quantity']); // giảm tồn kho
                }

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'old_status' => null,
                    'new_status' => 'pending',
                    'changed_by' => $userID,
                    'reason' => 'Đơn hàng mới được tạo.'
                ]);

                OrderLog::create([
                    'order_id' => $order->id,
                    'user_id' => $userID,
                    'action' => 'create_order',
                    'description' => 'Khách hàng tạo đơn hàng mới',
                    'logged_at' => now()
                ]);

                return $order;
            });
    }


    // lấy đơn hàng chi tiết
    public function getOrderDetails($orderId)
    {
        return Order::with(['orderDetail.sku', 'orderStatusHistories', 'orderLogs'])->findOrFail($orderId);
    }


    // lấy lịch sử trạng thái đơn hàng
    public function getOrderHistory($orderId)
    {
        return OrderStatusHistory::where('order_id', $orderId)->orderBy('created_at', 'asc')->get();
    }


    //Khách hàng hủy đơn hàng
    public function cancelOrder($orderID, $userID)
    {
        return DB::transaction(function () use ($orderID, $userID) {
            $order = Order::where('id', $orderID)->where('user_id', $userID)->firstOrFail();

            if ($order->status === 'shipped' || $order->status === 'delivered') {
                throw new Exception("Không thể hủy đơn hàng đã giao.");
            }

            if ($order->status === 'cancelled') {
                throw new Exception("Đơn hàng này đã bị hủy trước đó.QƯ");
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
                'reason' => 'Khách hàng đã hủy đơn hàng.'
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
}