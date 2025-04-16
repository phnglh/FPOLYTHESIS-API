<?php

namespace App\Http\Resources\ReportFlames;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductReportResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'name' => $this->resource['name'],
            'total_quantity' => (int) $this->resource['total_quantity'],
            'total_revenue' => (float) $this->resource['total_revenue'],
        ];
    }
}
