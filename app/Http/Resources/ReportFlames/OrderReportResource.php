<?php

namespace App\Http\Resources\ReportFlames;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderReportResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'status' => $this->resource['status'],
            'count' => (int) $this->resource['count'],
            'avg_order_value' => (float) $this->resource['avg_order_value'],
            'cancel_rate' => (float) $this->resource['cancel_rate'],
        ];
    }
}
