<?php

namespace App\Http\Resources\Categories;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'type' => 'categories',
            'id' => (string) $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'imageUrl' => $this->imageUrl,
            'parentId' => $this->parentId,
            'children' => CategoryResource::collection($this->whenLoaded('children')),
        ];
    }
}
