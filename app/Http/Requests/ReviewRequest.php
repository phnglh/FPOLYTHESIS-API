<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:500',
        ];
    }


    public function messages(): array
    {
        return [
            'product_id.required' => 'Sản phẩm không được để trống.',
            'product_id.exists' => 'Sản phẩm không hợp lệ.',
            'rating.required' => 'Vui lòng nhập đánh giá.',
            'rating.integer' => 'Đánh giá phải là số nguyên.',
            'rating.min' => 'Đánh giá tối thiểu là 1.',
            'rating.max' => 'Đánh giá tối đa là 5.',
            'review.max' => 'Nội dung đánh giá không được vượt quá 500 ký tự.',
        ];
    }
}
