<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoucherRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Hoặc kiểm tra quyền tại đây
    }

    public function rules()
    {
        $voucherId = $this->route('voucher'); // Lấy ID của voucher từ route (PUT /vouchers/{voucher})

        return [
            'code' => [
                'required',
                'string',
                // Bỏ qua bản ghi hiện tại khi kiểm tra unique
                'unique:vouchers,code,' . ($voucherId ?? 'NULL'),
            ],
            'type' => 'required|string|in:percentage,fixed',
            'percentage' => 'nullable|integer|min:0|max:100',
            'discount_value' => 'required|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'code.unique' => 'The code has already been taken.',
            // Thêm các thông báo lỗi khác nếu cần
        ];
    }
}
