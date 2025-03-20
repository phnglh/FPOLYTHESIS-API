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
            'address_id' => 'required|exists:user_addresses,id',
            'items' => 'required|array|min:1',
            'items.*.sku_id' => 'required|exists:skus,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }
}
