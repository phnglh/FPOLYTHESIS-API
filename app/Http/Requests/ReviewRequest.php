<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'rating.required' => 'Vui lòng chọn số sao.',
            'rating.integer' => 'Số sao phải là một số nguyên.',
            'rating.min' => 'Số sao phải từ 1 đến 5.',
            'rating.max' => 'Số sao phải từ 1 đến 5.',
            'review.string' => 'Nội dung đánh giá phải là một chuỗi ký tự.',
        ];
    }
}
