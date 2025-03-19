<?php

namespace App\Http\Resources\Products;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductOptionResource extends JsonResource
{
    public function toArray($request)
    {
        $options = [];

        foreach ($this->whenLoaded('skus') as $sku) {
            foreach ($sku->attribute_values as $attrValue) {
                $attributeId = $attrValue->pivot->attribute_id;
                $attributeName = $attrValue->attribute->name ?? null;
                $value = $attrValue->pivot->value;

                if (! isset($options[$attributeId])) {
                    $options[$attributeId] = [
                        'attribute_id' => $attributeId,
                        'attribute_name' => $attributeName,
                        'values' => [],
                    ];
                }

                if (! in_array(['value' => $value], $options[$attributeId]['values'])) {
                    $options[$attributeId]['values'][] = ['value' => $value];
                }
            }
        }

        return array_values($options);
    }
}
