<?php

namespace App\Http\Resources\Carts;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'cart_id' => $this->cart_id,
            'sku_id' => $this->sku_id,
            'product_name' => $this->sku->product->name ?? 'N/A',
            'attributes' => $this->sku->attributeSkus->map(function ($attrSku) {
                return [
                    'name' => $attrSku->attribute->name,
                    'value' => $attrSku->attributeValue->value,
                ];
            }),
            'quantity' => (float) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'sku' => [
                'id' => $this->sku->id,
                'sku' => $this->sku->sku,
                'product_id' => (int) $this->sku->product_id,
                'image_url' => $this->sku->image_url,
                'price' => (float) $this->sku->price,
                'stock' => (float) $this->sku->stock,
            ],
        ];
    }
}
