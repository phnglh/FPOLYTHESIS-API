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
        $rules = [
            'name' => "required|unique:products,name,{$id}",
            'description' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'is_published' => 'nullable|boolean',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Hỗ trợ file upload
        ];

        // Chỉ bắt buộc skus khi tạo mới (POST), không bắt buộc khi cập nhật (PUT)
        if ($this->isMethod('POST')) {
            $rules['skus'] = 'required|array|min:1';
            $rules['skus.*.price'] = 'required|numeric|min:0';
            $rules['skus.*.stock'] = 'required|integer|min:0';
            $rules['skus.*.image_url'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'; // Hỗ trợ file upload
            $rules['skus.*.image_url.*'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'; // Hỗ trợ nhiều ảnh
            $rules['skus.*.attributes'] = 'required|array|min:1';
            $rules['skus.*.attributes.*.attribute_id'] = 'required|integer|exists:attributes,id';
            $rules['skus.*.attributes.*.value'] = 'required|string|max:255';
        }

        return $rules;
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
            'skus.required' => 'SKU là bắt buộc khi tạo mới sản phẩm.',
            'skus.array' => 'SKU phải là mảng.',
            'skus.min' => 'Phải có ít nhất 1 SKU khi tạo mới sản phẩm.',
            'skus.*.price.required' => 'Giá là bắt buộc.',
            'skus.*.price.numeric' => 'Giá phải là số.',
            'skus.*.price.min' => 'Giá không được nhỏ hơn 0.',
            'skus.*.stock.required' => 'Số lượng là bắt buộc.',
            'skus.*.stock.integer' => 'Số lượng phải là số nguyên.',
            'skus.*.stock.min' => 'Số lượng không được nhỏ hơn 0.',
            'skus.*.image_url.image' => 'Ảnh SKU phải là file ảnh.',
            'skus.*.image_url.mimes' => 'Ảnh SKU phải có định dạng jpeg, png, jpg hoặc gif.',
            'skus.*.image_url.max' => 'Ảnh SKU không được vượt quá 2MB.',
            'skus.*.image_url.*.image' => 'Ảnh SKU phải là file ảnh.',
            'skus.*.image_url.*.mimes' => 'Ảnh SKU phải có định dạng jpeg, png, jpg hoặc gif.',
            'skus.*.image_url.*.max' => 'Ảnh SKU không được vượt quá 2MB.',
            'skus.*.attributes.required' => 'Thuộc tính là bắt buộc.',
            'skus.*.attributes.array' => 'Thuộc tính phải là mảng.',
            'skus.*.attributes.min' => 'Phải có ít nhất 1 thuộc tính.',
            'skus.*.attributes.*.attribute_id.required' => 'ID thuộc tính là bắt buộc.',
            'skus.*.attributes.*.attribute_id.integer' => 'ID thuộc tính phải là số nguyên.',
            'skus.*.attributes.*.attribute_id.exists' => 'ID thuộc tính không tồn tại.',
            'skus.*.attributes.*.value.required' => 'Giá trị thuộc tính là bắt buộc.',
            'skus.*.attributes.*.value.string' => 'Giá trị thuộc tính phải là chuỗi.',
            'skus.*.attributes.*.value.max' => 'Giá trị thuộc tính không được vượt quá 255 ký tự.',
        ];
    }
}
