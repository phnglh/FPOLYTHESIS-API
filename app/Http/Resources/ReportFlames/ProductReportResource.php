<?php

namespace App\Http\Resources\ReportFlames;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductReportResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'product_name' => $this->product_name ?? 'Không xác định',
            'total_quantity' => (int) $this->total_quantity,
            'stock' => (int) $this->stock,
            'variant' => $this->variant
        ];
    }
}
