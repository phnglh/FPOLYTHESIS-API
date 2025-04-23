<?php

namespace App\Http\Resources\ReportFlames;

use Illuminate\Http\Resources\Json\JsonResource;

class RevenueStatisticsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'daily_statistics' => $this->resource['daily_statistics']->map(function ($item) {
                return [
                    'date' => $item->date,
                    'total_revenue' => (float) $item->total_revenue,
                    'order_count' => (int) $item->order_count,
                ];
            }),
            'total_revenue' => (float) $this->resource['total_revenue'],
            'total_orders' => (int) $this->resource['total_orders'],
            'date_range' => [
                'start_date' => $this->resource['date_range']['start_date'],
                'end_date' => $this->resource['date_range']['end_date'],
            ],
        ];
    }
}
