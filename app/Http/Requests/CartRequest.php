<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartRequest extends FormRequest
{
    /**
     * Xác thực người dùng có quyền sử dụng request này không.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Định nghĩa quy tắc validation.
     */
    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ];
    }

    /**
     * Định nghĩa thông báo lỗi tùy chỉnh.
     */
    public function messages()
    {
        return [
            'product_id.required' => 'Sản phẩm không được để trống.',
            'product_id.exists' => 'Sản phẩm không tồn tại.',
            'quantity.required' => 'Số lượng không được để trống.',
            'quantity.integer' => 'Số lượng phải là số nguyên.',
            'quantity.min' => 'Số lượng tối thiểu là 1.',
        ];
    }
}
