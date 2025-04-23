<?php

namespace App\Http\Requests;

class SkuRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'attributes' => 'required|array|min:1',
            'attributes.*.attribute_id' => 'required|integer|exists:attributes,id',
            'attributes.*.value' => 'required|string|max:255',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Hỗ trợ file upload
            'image_url.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Hỗ trợ nhiều ảnh
        ];
    }

    public function messages()
    {
        return [
            'price.required' => 'Giá là bắt buộc.',
            'price.numeric' => 'Giá phải là số.',
            'price.min' => 'Giá không được nhỏ hơn 0.',
            'stock.required' => 'Số lượng là bắt buộc.',
            'stock.integer' => 'Số lượng phải là số nguyên.',
            'stock.min' => 'Số lượng không được nhỏ hơn 0.',
            'attributes.required' => 'Thuộc tính là bắt buộc.',
            'attributes.array' => 'Thuộc tính phải là mảng.',
            'attributes.min' => 'Phải có ít nhất 1 thuộc tính.',
            'attributes.*.attribute_id.required' => 'ID thuộc tính là bắt buộc.',
            'attributes.*.attribute_id.integer' => 'ID thuộc tính phải là số nguyên.',
            'attributes.*.attribute_id.exists' => 'ID thuộc tính không tồn tại.',
            'attributes.*.value.required' => 'Giá trị thuộc tính là bắt buộc.',
            'attributes.*.value.string' => 'Giá trị thuộc tính phải là chuỗi.',
            'attributes.*.value.max' => 'Giá trị thuộc tính không được vượt quá 255 ký tự.',
            'image_url.image' => 'Ảnh SKU phải là file ảnh.',
            'image_url.mimes' => 'Ảnh SKU phải có định dạng jpeg, png, jpg hoặc gif.',
            'image_url.max' => 'Ảnh SKU không được vượt quá 2MB.',
            'image_url.*.image' => 'Ảnh SKU phải là file ảnh.',
            'image_url.*.mimes' => 'Ảnh SKU phải có định dạng jpeg, png, jpg hoặc gif.',
            'image_url.*.max' => 'Ảnh SKU không được vượt quá 2MB.',
        ];
    }
}
