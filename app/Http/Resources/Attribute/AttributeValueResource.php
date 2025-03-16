<?php

namespace App\Http\Resources\Attribute;

use Illuminate\Http\Resources\Json\JsonResource;

class AttributeValueResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'type' => 'attribute_value',
            'id' => (string) $this->id,
            'value' => $this->value,
        ];
    }
}
