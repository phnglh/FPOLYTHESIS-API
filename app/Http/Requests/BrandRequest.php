<?php

namespace App\Http\Requests;

class BrandRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:brands,name',
            'description' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên nhãn hàng là bắt buộc.',
            'name.string' => 'Tên nhãn hàng phải là chuỗi ký tự.',
            'name.max' => 'Tên nhãn hàng không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên nhãn hàng đã tồn tại.',

            'description.required' => 'Mô tả là bắt buộc.',
            'description.string' => 'Mô tả phải là chuỗi ký tự.',
        ];
    }
}
