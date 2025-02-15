<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttributeRequest extends FormRequest {
    public function rules() {
        return [
            'name' => 'required|string|max:255|unique:attributes,name',
        ];
    }
}
