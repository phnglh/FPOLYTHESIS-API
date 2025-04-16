<?php

namespace App\Http\Resources\ReportFlames;

use Illuminate\Http\Resources\Json\JsonResource;

class InventoryReportResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'name' => $this->resource['name'],
            'sku' => $this->resource['sku'],
            'stock' => (int) $this->resource['stock'],
            'sold_quantity' => (int) $this->resource['sold_quantity'],
        ];
    }
}
