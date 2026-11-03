<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Khách tự sửa thông tin cá nhân; email là định danh đăng nhập nên không đổi ở đây.
 */
class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'regex:/^(\+84|0)\d{9,10}$/'],
            'address' => ['nullable', 'string', 'max:500'],
            'newsletter' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Bỏ khoảng trắng, dấu chấm, gạch ngang người dùng hay gõ trong số điện thoại.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('phone')) {
            $this->merge(['phone' => preg_replace('/[\s.\-()]+/', '', (string) $this->input('phone'))]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ tên.',
            'name.max' => 'Họ tên tối đa 100 ký tự.',
            'phone.regex' => 'Số điện thoại chưa đúng.',
            'address.max' => 'Địa chỉ tối đa 500 ký tự.',
        ];
    }
}
