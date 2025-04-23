<?php

namespace App\Http\Resources\ReportFlames;

use Illuminate\Http\Resources\Json\JsonResource;

class MonthlyRevenueReportResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'month' => $this->month,
            'revenue' => $this->revenue,
        ];
    }
}
