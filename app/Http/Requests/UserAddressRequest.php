<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserAddressRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'receiver_name' => 'required_with:address|string|max:255',
            'receiver_email' => 'required_with:address|string|max:255',
            'receiver_phone' => 'required_with:address|string|max:15',
            'address' => 'required|string|max:255',
            'city' => 'required_with:address|string|max:100',
            'state' => 'nullable|string|max:100',
            'is_default' => 'boolean',
        ];
    }
}
