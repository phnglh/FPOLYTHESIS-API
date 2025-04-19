<?php

namespace App\Http\Resources\Voucher;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoucherResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type,
            'percentage' => (int) $this->percentage, // Thêm trường percentage
            'discount_value' => (float) $this->discount_value,
            'min_order_value' => (int) $this->min_order_value,
            'usage_limit' => (int) $this->usage_limit,
            'used_count' => (int) $this->used_count,
            'start_date' => $this->start_date?->format('Y-m-d H:i:s'), // Sử dụng null-safe operator
            'end_date' => $this->end_date?->format('Y-m-d H:i:s'), // Sử dụng null-safe operator
            'is_active' => $this->is_active, // Giữ nguyên kiểu boolean (hoặc để (int) nếu bạn muốn)
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
