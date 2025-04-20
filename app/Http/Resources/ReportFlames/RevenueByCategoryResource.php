<?php

namespace App\Http\Resources\ReportFlames;

use Illuminate\Http\Resources\Json\JsonResource;

class RevenueByCategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'category' => $this['category'],
            'revenue' => $this['revenue'],
        ];
    }
}
