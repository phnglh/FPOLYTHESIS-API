<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Có thể thêm kiểm tra quyền tại đây
    }

    public function rules()
    {
        return [
            'shipping_method_id' => 'required|exists:shipping_methods,id',
            'items' => 'required|array|min:1',
            'items.*.sku_id' => 'required|exists:skus,id',
            'items.*.quantity' => 'required|integer|min:1',
            'selected_sku_ids' => 'required|array',
            'selected_sku_ids.*' => 'exists:skus,id',
            'address_id' => 'nullable|exists:user_addresses,id',
            'new_address.receiver_name' => 'nullable|string',
            'new_address.receiver_phone' => 'nullable|string',
            'new_address.address' => 'nullable|string',
            'new_address.city' => 'nullable|string',
            'new_address.state' => 'nullable|string',
            'new_address.zip_code' => 'nullable|string',
            'new_address.is_default' => 'nullable|boolean',
        ];
    }
}
