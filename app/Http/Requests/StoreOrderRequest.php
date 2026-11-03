<?php

namespace App\Http\Requests;

use App\OrderPaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^(\+?84|0)\d{9,10}$/'],
            'address' => ['required', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', Rule::enum(OrderPaymentMethod::class)],
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
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_name.required' => 'Vui lòng nhập họ và tên người nhận.',
            'customer_name.max' => 'Họ và tên tối đa 100 ký tự.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại không hợp lệ.',
            'address.required' => 'Vui lòng nhập địa chỉ nhận hàng.',
            'address.max' => 'Địa chỉ tối đa 500 ký tự.',
            'note.max' => 'Ghi chú tối đa 1000 ký tự.',
            'payment_method.required' => 'Vui lòng chọn hình thức thanh toán.',
            'payment_method.enum' => 'Hình thức thanh toán không hợp lệ.',
        ];
    }
}
