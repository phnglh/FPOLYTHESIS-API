<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Sku;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportService
{
    // Option 1: Doanh thu
    public function getRevenueReport(array $filters): array
    {
        $query = Order::query()
            ->where('status', '!=', 'cancelled')
            ->where('payment_status', 'paid')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('skus', 'order_items.sku_id', '=', 'skus.id')
            ->join('products', 'skus.product_id', '=', 'products.id')
            ->select(
                DB::raw('DATE(orders.ordered_at) as date'),
                DB::raw('SUM(orders.final_total) as revenue')
            );

        if (!empty($filters['start_date'])) {
            $query->whereDate('orders.ordered_at', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('orders.ordered_at', '<=', $filters['end_date']);
        }
        if (!empty($filters['category_id'])) {
            $query->where('products.category_id', $filters['category_id']);
        }

        return $query->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    // Option 2: Đơn hàng
    public function getOrderReport(array $filters): array
    {
        $query = Order::query()
            ->select(
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(final_total) as avg_order_value'),
                DB::raw('SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) / COUNT(*) as cancel_rate')
            );

        if (!empty($filters['start_date'])) {
            $query->whereDate('ordered_at', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('ordered_at', '<=', $filters['end_date']);
        }

        return $query->groupBy('status')
            ->get()
            ->toArray();
    }

    // Option 3: Sản phẩm
    public function getProductReport(array $filters): array
    {
        $query = Order::query()
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('skus', 'order_items.sku_id', '=', 'skus.id')
            ->join('products', 'skus.product_id', '=', 'products.id')
            ->select(
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total_price) as total_revenue')
            );

        if (!empty($filters['start_date'])) {
            $query->whereDate('orders.ordered_at', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('orders.ordered_at', '<=', $filters['end_date']);
        }
        if (!empty($filters['category_id'])) {
            $query->where('products.category_id', $filters['category_id']);
        }

        return $query->groupBy('products.id', 'products.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    // Option 4: Khách hàng
    public function getCustomerReport(array $filters): array
    {
        $query = User::query()
            ->where('role', 'customer')
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->select(
                'users.name',
                'users.email',
                DB::raw('COUNT(orders.id) as order_count'),
                DB::raw('SUM(orders.final_total) as total_spent')
            );

        if (!empty($filters['start_date'])) {
            $query->whereDate('orders.ordered_at', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('orders.ordered_at', '<=', $filters['end_date']);
        }

        return $query->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('total_spent', 'desc')
            ->get()
            ->toArray();
    }

    // Option 7: Kho hàng
    public function getInventoryReport(array $filters): array
    {
        $query = Sku::query()
            ->join('products', 'skus.product_id', '=', 'products.id')
            ->select(
                'products.name',
                'skus.sku',
                'skus.stock',
                DB::raw('(SELECT SUM(quantity) FROM order_items WHERE order_items.sku_id = skus.id AND order_items.created_at BETWEEN ? AND ?) as sold_quantity')
            )
            ->setBindings([
                $filters['start_date'] ?? now()->subYear()->toDateString(),
                $filters['end_date'] ?? now()->toDateString()
            ]);

        return $query->where('skus.stock', '<=', 10)
            ->get()
            ->toArray();
    }
}
