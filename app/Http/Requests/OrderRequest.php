<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.sku_id' => 'required|integer|exists:skus,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_address' => 'required|string|max:255',
            'shipping_method' => 'required|string|in:Standard Shipping,Express Shipping',
            'payment_status' => 'required|string|in:unpaid,paid,refunded,failed',
            'notes' => 'nullable|string|max:500',
            'coupon_code' => 'nullable|string|exists:vouchers,code',
        ];
    }
}
