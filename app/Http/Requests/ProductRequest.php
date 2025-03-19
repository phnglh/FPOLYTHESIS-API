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
            'name' => 'required|unique:products,name',
            'description' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'image_url' => 'nullable|url',
            'skus*.sku' => 'required|string',
            'skus.*.stock' => 'required|numeric',
            'skus.*.price' => 'required|numeric',
            'skus.*.image_url' => 'nullable|url',
            'skus.*.attributes' => 'required|array',
            'skus.*.attributes.*.attribute_id' => 'required|exists:attributes,id',
            'skus.*.attributes.*.value' => 'required|string',
            // 'sku.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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

            'skus.required' => 'SKU là bắt buộc.',
            'skus.array' => 'SKU phải là mảng.',

            'skus.*.sku.required' => 'SKU là bắt buộc.',
            'skus.*.sku.string' => 'SKU phải là chuỗi ký tự.',

            'skus.*.stock.required' => 'Số lượng là bắt buộc.',
            'skus.*.stock.numeric' => 'Số lượng phải là số.',

            'skus.*.price.required' => 'Giá là bắt buộc.',
            'skus.*.price.numeric' => 'Giá phải là số.',

            'skus.*.attributes.required' => 'Thuộc tính là bắt buộc.',
            'skus.*.attributes.array' => 'Thuộc tính phải là mảng.',

            'skus.*.attributes.*.attribute_id.required' => 'Thuộc tính là bắt buộc.',
            'skus.*.attributes.*.attribute_id.exists' => 'Thuộc tính không tồn tại.',

            'skus.*.attributes.*.value.required' => 'Giá trị thuộc tính là bắt buộc.',
            'skus.*.attributes.*.value.string' => 'Giá trị thuộc tính phải là chuỗi ký tự.',
        ];
    }
}
