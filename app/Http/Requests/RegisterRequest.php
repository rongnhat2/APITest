<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' =>'required',
            'email' =>'required|email',
            'password' => 'required|min:8',
        ];
    }
    /**
    * Get the error messages for the defined validation rules.
    *
    * @return array
    */
    public function messages()
    {
        return [
            'name.required'     => __('Tên là trường bắt buộc'),
            'email.required'    => __('Bạn chưa nhập Email.'),
            'email.email'       => __('Email không đúng định dạng'),
            'password.required' => __('Mật khẩu là trường bắt buộc'),
        ];
    }
}
