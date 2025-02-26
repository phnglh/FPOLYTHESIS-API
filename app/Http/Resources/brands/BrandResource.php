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
            'attributes' => [
                'name' => $this->name,
                'description' => $this->description,
                'image_url' => $this->image_url,
                'parent_id' => $this->parent_id,
                'children' => BrandResource::collection($this->whenLoaded('children')),
            ],
        ];
    }
}
