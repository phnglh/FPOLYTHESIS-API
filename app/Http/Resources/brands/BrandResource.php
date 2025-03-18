<?php

namespace App\Http\Resources\Brands;

use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'type' => 'brands',
            'id' => (string) $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image_url' => $this->image_url,
        ];
    }
}
