<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;

class ReportService
{
    public function getRevenueReport($filters)
    {
        $startDate = $filters['start_date'] ?? Carbon::today()->startOfDay();
        $endDate = $filters['end_date'] ?? Carbon::today()->endOfDay();
        $groupBy = $filters['group_by'] ?? null;

        // Đảm bảo múi giờ là Asia/Ho_Chi_Minh
        $timezone = config('app.timezone', 'Asia/Ho_Chi_Minh');
        if (!($startDate instanceof Carbon)) {
            $startDate = Carbon::parse($startDate, $timezone)->startOfDay();
        }
        if (!($endDate instanceof Carbon)) {
            $endDate = Carbon::parse($endDate, $timezone)->endOfDay();
        }

        if ($startDate->gt($endDate)) {
            $temp = $startDate;
            $startDate = $endDate;
            $endDate = $temp;
        }

        $cacheKey = 'revenue_report_' . md5(serialize($filters));
        $cacheMinutes = 10;

        $result = Cache::remember($cacheKey, $cacheMinutes, function () use ($startDate, $endDate, $groupBy) {
            // Truy vấn dữ liệu, đảm bảo múi giờ khớp
            $query = Order::whereBetween('ordered_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                ->whereIn('status', ['pending', 'processing', 'completed', 'delivered']);

            if ($groupBy === 'day') {
                $rawData = $query->groupByRaw('DATE(ordered_at)')
                    ->selectRaw('DATE(ordered_at) as date, SUM(final_total) as revenue')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [$item->date => (float) $item->revenue];
                    })->toArray();

                $period = CarbonPeriod::create($startDate, $endDate);
                $data = [];

                foreach ($period as $date) {
                    $dateStr = $date->format('Y-m-d');
                    $data[] = [
                        'date' => $dateStr,
                        'revenue' => isset($rawData[$dateStr]) ? (float) $rawData[$dateStr] : 0.0,
                    ];
                }

                Log::info('Revenue report by day', [
                    'start_date' => $startDate->toDateTimeString(),
                    'end_date' => $endDate->toDateTimeString(),
                    'raw_data' => $rawData,
                    'final_data' => $data
                ]);
                return $data;
            }

            $revenue = $query->sum('final_total');
            Log::info('Revenue report', [
                'start_date' => $startDate->toDateTimeString(),
                'end_date' => $endDate->toDateTimeString(),
                'revenue' => $revenue
            ]);
            return (float) $revenue;
        });

        return $groupBy === 'day' && !is_array($result) ? [] : $result;
    }

    public function getOrderReport($filters)
    {
        $startDate = $filters['start_date'] ?? Carbon::today()->startOfDay();
        $endDate = $filters['end_date'] ?? Carbon::today()->endOfDay();

        $timezone = config('app.timezone', 'Asia/Ho_Chi_Minh');
        if (!($startDate instanceof Carbon)) {
            $startDate = Carbon::parse($startDate, $timezone)->startOfDay();
        }
        if (!($endDate instanceof Carbon)) {
            $endDate = Carbon::parse($endDate, $timezone)->endOfDay();
        }

        $cacheKey = 'order_report_' . md5(serialize($filters));
        $cacheMinutes = 10;

        $result = Cache::remember($cacheKey, $cacheMinutes, function () use ($startDate, $endDate) {
            $orders = Order::whereBetween('ordered_at', [$startDate, $endDate])
                ->whereIn('status', ['pending', 'processing', 'completed', 'delivered'])
                ->count();

            Log::info('Order report', ['start_date' => $startDate, 'end_date' => $endDate, 'orders' => $orders]);
            return $orders;
        });

        return $result;
    }

    public function getCancelRate($filters)
    {
        $startDate = $filters['start_date'] ?? Carbon::today()->startOfDay();
        $endDate = $filters['end_date'] ?? Carbon::today()->endOfDay();

        $timezone = config('app.timezone', 'Asia/Ho_Chi_Minh');
        if (!($startDate instanceof Carbon)) {
            $startDate = Carbon::parse($startDate, $timezone)->startOfDay();
        }
        if (!($endDate instanceof Carbon)) {
            $endDate = Carbon::parse($endDate, $timezone)->endOfDay();
        }

        $cacheKey = 'cancel_rate_' . md5(serialize($filters));
        $cacheMinutes = 10;

        $result = Cache::remember($cacheKey, $cacheMinutes, function () use ($startDate, $endDate) {
            $totalOrders = Order::whereBetween('ordered_at', [$startDate, $endDate])->count();
            $cancelledOrders = Order::whereBetween('ordered_at', [$startDate, $endDate])
                ->where('status', 'cancelled')
                ->count();

            $rate = $totalOrders ? ($cancelledOrders / $totalOrders) * 100 : 0;
            Log::info('Cancel rate report', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_orders' => $totalOrders,
                'cancelled_orders' => $cancelledOrders,
                'rate' => $rate
            ]);
            return $rate;
        });

        return $result;
    }

    public function getRevenueByCategory($filters)
    {
        $startDate = $filters['start_date'] ?? Carbon::today()->startOfDay();
        $endDate = $filters['end_date'] ?? Carbon::today()->endOfDay();

        $timezone = config('app.timezone', 'Asia/Ho_Chi_Minh');
        if (!($startDate instanceof Carbon)) {
            $startDate = Carbon::parse($startDate, $timezone)->startOfDay();
        }
        if (!($endDate instanceof Carbon)) {
            $endDate = Carbon::parse($endDate, $timezone)->endOfDay();
        }

        $cacheKey = 'revenue_by_category_' . md5(serialize($filters));
        $cacheMinutes = 10;

        $result = Cache::remember($cacheKey, $cacheMinutes, function () use ($startDate, $endDate) {
            $revenueByCategory = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereBetween('orders.ordered_at', [$startDate, $endDate])
                ->whereIn('orders.status', ['pending', 'processing', 'completed', 'delivered'])
                ->groupBy('order_items.product_name')
                ->selectRaw('order_items.product_name, SUM(order_items.total_price) as total_revenue')
                ->get();

            Log::info('Revenue by category report', ['start_date' => $startDate, 'end_date' => $endDate, 'data' => $revenueByCategory]);
            return $revenueByCategory;
        });

        return $result;
    }

    public function getTopProductReport($filters)
    {
        $startDate = $filters['start_date'] ?? Carbon::today()->startOfDay();
        $endDate = $filters['end_date'] ?? Carbon::today()->endOfDay();

        $timezone = config('app.timezone', 'Asia/Ho_Chi_Minh');
        if (!($startDate instanceof Carbon)) {
            $startDate = Carbon::parse($startDate, $timezone)->startOfDay();
        }
        if (!($endDate instanceof Carbon)) {
            $endDate = Carbon::parse($endDate, $timezone)->endOfDay();
        }

        $cacheKey = 'top_product_report_' . md5(serialize($filters));
        $cacheMinutes = 10;

        $result = Cache::remember($cacheKey, $cacheMinutes, function () use ($startDate, $endDate) {
            $topProducts = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereBetween('orders.ordered_at', [$startDate, $endDate])
                ->whereIn('orders.status', ['pending', 'processing', 'completed', 'delivered'])
                ->groupBy('order_items.product_name')
                ->orderByRaw('SUM(order_items.quantity) DESC')
                ->selectRaw('order_items.product_name, SUM(order_items.quantity) as total_quantity')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    $obj = new stdClass();
                    $obj->product_name = $item->product_name ?? 'Không xác định';
                    $obj->total_quantity = (int) $item->total_quantity;
                    return $obj;
                });

            Log::info('Top product report', ['start_date' => $startDate, 'end_date' => $endDate, 'data' => $topProducts]);
            return $topProducts;
        });

        return $result;
    }

    public function getTopCustomerReport($filters)
    {
        $startDate = $filters['start_date'] ?? Carbon::today()->startOfDay();
        $endDate = $filters['end_date'] ?? Carbon::today()->endOfDay();

        $timezone = config('app.timezone', 'Asia/Ho_Chi_Minh');
        if (!($startDate instanceof Carbon)) {
            $startDate = Carbon::parse($startDate, $timezone)->startOfDay();
        }
        if (!($endDate instanceof Carbon)) {
            $endDate = Carbon::parse($endDate, $timezone)->endOfDay();
        }

        $cacheKey = 'top_customer_report_' . md5(serialize($filters));
        $cacheMinutes = 10;

        $result = Cache::remember($cacheKey, $cacheMinutes, function () use ($startDate, $endDate) {
            $topCustomers = Order::join('users', 'orders.user_id', '=', 'users.id')
                ->whereBetween('orders.ordered_at', [$startDate, $endDate])
                ->whereIn('orders.status', ['pending', 'processing', 'completed', 'delivered'])
                ->whereNotIn('users.email', ['admin@example.com', 'admin12@example.com'])
                ->groupBy('users.id', 'users.name')
                ->orderByRaw('SUM(orders.final_total) DESC')
                ->selectRaw('users.name, SUM(orders.final_total) as total_spent')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    $obj = new stdClass();
                    $obj->name = $item->name ?? 'Không xác định';
                    $obj->total_spent = (float) $item->total_spent;
                    return $obj;
                });

            Log::info('Top customer report', ['start_date' => $startDate, 'end_date' => $endDate, 'data' => $topCustomers]);
            return $topCustomers;
        });

        return $result;
    }

    public function getRevenueStatistics($request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Lấy thống kê doanh thu
        $statistics = Order::whereNotIn('status', ['cancelled'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(final_total) as total_revenue'),
                DB::raw('COUNT(id) as order_count')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get();

        // Tính tổng doanh thu và số đơn hàng
        $totalRevenue = $statistics->sum('total_revenue');
        $totalOrders = $statistics->sum('order_count');

        return [
            'daily_statistics' => $statistics,
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'date_range' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ];
    }
}
