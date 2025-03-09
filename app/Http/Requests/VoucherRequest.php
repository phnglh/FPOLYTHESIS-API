<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoucherRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Middleware sẽ kiểm tra role
    }

    public function rules()
{
    return [
        'code' => 'required|string|unique:vouchers,code',
        'type' => 'required|in:percentage,fixed',
        'discount_value' => 'required|numeric|min:0',
        'min_order_value' => 'nullable|numeric|min:0',
        'usage_limit' => 'nullable|integer|min:1',
        'start_date' => 'nullable|date_format:Y-m-d H:i:s',
        'end_date' => 'nullable|date_format:Y-m-d H:i:s|after_or_equal:start_date',
        'is_active' => 'boolean',
    ];
}

}