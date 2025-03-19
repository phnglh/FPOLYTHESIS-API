<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttributeValueRequest extends FormRequest
{
    public function rules()
    {
        return [
            'value' => 'required|string|max:255',
        ];
    }
}
