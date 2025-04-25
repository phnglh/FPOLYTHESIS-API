<?php

namespace App\Http\Resources\Auth;

use App\Http\Resources\Orders\OrderItemResource;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount,
            'final_total' => (float) $this->final_total,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
