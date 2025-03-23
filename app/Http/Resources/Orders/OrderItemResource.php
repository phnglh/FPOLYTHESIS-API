<?php

namespace App\Http\Resources\Orders;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_id' =>  $this->order_id,
            'sku_id' => $this->sku_id,
            'product_name' =>  $this->product_name,
            'sku_code' => $this->sku_code,
            'unit_price' => (float) $this->unit_price,
            'quantity' => (int) $this->quantity,
            'total_price' => (float) $this->total_price,
            'created_at' =>  $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
