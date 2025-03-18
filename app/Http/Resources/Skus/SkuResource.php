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
            // 'image_url' => json_decode($this->image_url),

            // Attributes with null-safe access
            'attributes' => $this->attribute_values->map(function ($attrValue) {
                return [
                    'id' => optional($attrValue->pivot)->attribute_value_id,
                    'name' => optional($attrValue->attribute)->name,
                    'value' => optional($attrValue->pivot)->value,
                ];
            }),

            // Images mapping
            'images' => $this->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => $image->url,
                ];
            }),
        ];
    }
}
