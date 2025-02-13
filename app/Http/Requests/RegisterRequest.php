<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;
class RegisterRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
            'phone' => 'nullable|regex:/^(\+84|0)[3-9]\d{8}$/',
            'address' => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'name.string' => 'Tên phải là chuỗi ký tự.',
            'name.max' => 'Tên không được vượt quá 255 ký tự.',

            'email.required' => 'Email là bắt buộc.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email đã được sử dụng.',

            'password.required' => 'Mật khẩu là bắt buộc.',
            'password.string' => 'Mật khẩu phải là chuỗi ký tự.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',

            'password_confirmation.required' => 'Xác nhận mật khẩu là bắt buộc.',
            'password_confirmation.string' => 'Xác nhận mật khẩu phải là chuỗi ký tự.',
            'password_confirmation.min' => 'Xác nhận mật khẩu phải có ít nhất 8 ký tự.',

            'phone.regex' => 'Số điện thoại không hợp lệ.',
            'address.max' => 'Địa chỉ không được vượt quá 500 ký tự.',
        ];
    }

}
