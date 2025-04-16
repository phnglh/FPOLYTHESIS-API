<?php

namespace App\Http\Resources\ReportFlames;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerReportResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'name' => $this->resource['name'],
            'email' => $this->resource['email'],
            'order_count' => (int) $this->resource['order_count'],
            'total_spent' => (float) $this->resource['total_spent'],
        ];
    }
}
