<?php

namespace App\Http\Resources\ReportFlames;

use Illuminate\Http\Resources\Json\JsonResource;

class DailyAverageOrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'date' => $this->resource['date'],
            'order_count' => (int) ($this->resource['order_count'] ?? 0),
            'total' => (float) ($this->resource['total'] ?? 0),
            'avg_order_value' => (float) ($this->resource['avg_order_value'] ?? 0),
        ];
    }
}
