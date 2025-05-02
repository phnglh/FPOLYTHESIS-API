<?php

namespace App\Http\Resources\ReportFlames;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerReportResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'name' => $this->name ?? 'Không xác định',
            'total_spent' => (float) $this->total_spent,
            'order_count' => (int) $this->order_count,
            'arpu' => (float) $this->arpu
        ];
    }
}
