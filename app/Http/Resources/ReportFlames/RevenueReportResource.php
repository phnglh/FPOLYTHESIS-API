<?php

namespace App\Http\Resources\ReportFlames;

use Illuminate\Http\Resources\Json\JsonResource;

class RevenueReportResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'date' => $this->resource['date'],
            'revenue' => (float) $this->resource['revenue'],
        ];
    }
}
