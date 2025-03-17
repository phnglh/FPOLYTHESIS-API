<?php

namespace App\Http\Resources\Attribute;

use Illuminate\Http\Resources\Json\JsonResource;

class AttributeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'type' => 'attributes',
            'id' => (string) $this->id,
            'name' => $this->name,
            // 'value' => CategoryResource::collection($this->whenLoaded('children')),
        ];
    }
}
