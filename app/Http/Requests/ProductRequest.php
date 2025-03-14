<?php

namespace App\Http\Requests;

class ProductRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string',
            'description' => 'required|string',
            'brandId' => 'required|exists:brands,id',
            'price' => 'required|numeric',
            'sku' => 'required|array',
            'sku.*.sku' => 'required|string',
            'sku.*.stock' => 'required|numeric',
            'sku.*.price' => 'required|numeric',
            'sku.*.attributes' => 'required|array',
            'sku.*.attributes.*.attribute_id' => 'required|exists:attributes,id',
            'sku.*.attributes.*.value' => 'required|string',
            'sku.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên sản phẩm là bắt buộc.',
            'name.string' => 'Tên sản phẩm phải là chuỗi ký tự.',

            'description.required' => 'Mô tả là bắt buộc.',
            'description.string' => 'Mô tả phải là chuỗi ký tự.',

            'brand_id.required' => 'Thương hiệu là bắt buộc.',
            'brand_id.exists' => 'Thương hiệu không tồn tại.',

            'price.required' => 'Giá là bắt buộc.',
            'price.numeric' => 'Giá phải là số.',

            'sku.required' => 'SKU là bắt buộc.',
            'sku.array' => 'SKU phải là mảng.',

            'sku.*.sku.required' => 'SKU là bắt buộc.',
            'sku.*.sku.string' => 'SKU phải là chuỗi ký tự.',

            'sku.*.stock.required' => 'Số lượng là bắt buộc.',
            'sku.*.stock.numeric' => 'Số lượng phải là số.',

            'sku.*.price.required' => 'Giá là bắt buộc.',
            'sku.*.price.numeric' => 'Giá phải là số.',

            'sku.*.attributes.required' => 'Thuộc tính là bắt buộc.',
            'sku.*.attributes.array' => 'Thuộc tính phải là mảng.',

            'sku.*.attributes.*.attribute_id.required' => 'Thuộc tính là bắt buộc.',
            'sku.*.attributes.*.attribute_id.exists' => 'Thuộc tính không tồn tại.',

            'sku.*.attributes.*.value.required' => 'Giá trị thuộc tính là bắt buộc.',
            'sku.*.attributes.*.value.string' => 'Giá trị thuộc tính phải là chuỗi ký tự.',
        ];
    }
}
