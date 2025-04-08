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
        $id = $this->route('id'); // Lấy ID từ route để bỏ qua bản ghi hiện tại khi cập nhật
        return [
            'name' => "required|unique:products,name,{$id}",
            'description' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'is_published' => 'nullable|boolean',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Hỗ trợ file upload
            'skus' => 'required|array',
            'skus.*.price' => 'required|numeric',
            'skus.*.stock' => 'required|numeric',
            'skus.*.image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Hỗ trợ file upload
            'skus.*.attributes' => 'required|array',
            'skus.*.attributes.*.attribute_id' => 'required|exists:attributes,id',
            'skus.*.attributes.*.value' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên sản phẩm là bắt buộc.',
            'name.unique' => 'Tên sản phẩm đã tồn tại.',
            'description.required' => 'Mô tả là bắt buộc.',
            'description.string' => 'Mô tả phải là chuỗi ký tự.',
            'brand_id.exists' => 'Thương hiệu không tồn tại.',
            'category_id.exists' => 'Danh mục không tồn tại.',
            'image_url.image' => 'Ảnh sản phẩm phải là file ảnh.',
            'image_url.mimes' => 'Ảnh sản phẩm phải có định dạng jpeg, png, jpg hoặc gif.',
            'image_url.max' => 'Ảnh sản phẩm không được vượt quá 2MB.',
            'skus.required' => 'SKU là bắt buộc.',
            'skus.array' => 'SKU phải là mảng.',
            'skus.*.price.required' => 'Giá là bắt buộc.',
            'skus.*.price.numeric' => 'Giá phải là số.',
            'skus.*.stock.required' => 'Số lượng là bắt buộc.',
            'skus.*.stock.numeric' => 'Số lượng phải là số.',
            'skus.*.image_url.image' => 'Ảnh SKU phải là file ảnh.',
            'skus.*.image_url.mimes' => 'Ảnh SKU phải có định dạng jpeg, png, jpg hoặc gif.',
            'skus.*.image_url.max' => 'Ảnh SKU không được vượt quá 2MB.',
            'skus.*.attributes.required' => 'Thuộc tính là bắt buộc.',
            'skus.*.attributes.array' => 'Thuộc tính phải là mảng.',
            'skus.*.attributes.*.attribute_id.required' => 'Thuộc tính là bắt buộc.',
            'skus.*.attributes.*.attribute_id.exists' => 'Thuộc tính không tồn tại.',
            'skus.*.attributes.*.value.required' => 'Giá trị thuộc tính là bắt buộc.',
            'skus.*.attributes.*.value.string' => 'Giá trị thuộc tính phải là chuỗi ký tự.',
        ];
    }
}
