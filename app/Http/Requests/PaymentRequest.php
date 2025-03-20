<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InitiatePaymentRequest extends FormRequest
{
    // Chỉ dành cho Customer
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|in:cod,vnpay,bank_transfer',
        ];
    }
}
