<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartRequest extends FormRequest
{
    public function rules()
    {
        return [
            'sku_id' => 'required|exists:skus,id',
            'quantity' => 'required|integer|min:1'
        ];
    }

    public function messages()
    {
        return [
            'sku_id.required' => 'Vui lòng chọn sản phẩm.',
            'sku_id.exists' => 'Sản phẩm không tồn tại.',
            'quantity.required' => 'Số lượng không được để trống.',
            'quantity.integer' => 'Số lượng phải là số nguyên.',
            'quantity.min' => 'Số lượng tối thiểu là 1.',
        ];
    }
}
