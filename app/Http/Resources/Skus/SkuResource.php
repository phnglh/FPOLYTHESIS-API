<?php

namespace App\Http\Resources\Skus;

use Illuminate\Http\Resources\Json\JsonResource;

class SkuResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'sku' => $this->sku,
            'price' => $this->price,
            'stock' => $this->stock,
            'image_url' => $this->image_url,

            'attributes' => $this->attribute_values->map(function ($attrValue) {
                return [
                    'attribute_id' => $attrValue->pivot->attribute_id,
                    'attribute_name' => $attrValue->attribute->name ?? null,
                    'value' => $attrValue->pivot->value,
                ];
            }),

            'images' => $this->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => $image->url,
                ];
            }),
        ];
    }
}
