<?php

namespace App\Http\Resources\ReportFlames;

use Illuminate\Http\Resources\Json\JsonResource;

class DailyAverageOrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'date' => $this->date,
            'average_order_value' => $this->avg_order_value,
        ];
    }
}
