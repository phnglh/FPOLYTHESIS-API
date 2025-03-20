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
            'quantity' => (int) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'created_at' =>  $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
