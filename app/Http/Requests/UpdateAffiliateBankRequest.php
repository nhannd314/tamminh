<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Tài khoản ngân hàng affiliate dùng để nhận hoa hồng.
 */
class UpdateAffiliateBankRequest extends FormRequest
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
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_number' => ['required', 'string', 'regex:/^\d{6,20}$/'],
            'bank_account_name' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * Bỏ khoảng trắng, dấu chấm trong số tài khoản; tên chủ tài khoản viết hoa như trên ngân hàng.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(array_filter([
            'bank_account_number' => $this->filled('bank_account_number') ? preg_replace('/[\s.\-]+/', '', (string) $this->input('bank_account_number')) : null,
            'bank_account_name' => $this->filled('bank_account_name') ? mb_strtoupper(preg_replace('/\s+/', ' ', trim((string) $this->input('bank_account_name')))) : null,
            'bank_name' => $this->filled('bank_name') ? preg_replace('/\s+/', ' ', trim((string) $this->input('bank_name'))) : null,
        ], fn (?string $value): bool => $value !== null));
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bank_name.required' => 'Vui lòng nhập tên ngân hàng.',
            'bank_name.max' => 'Tên ngân hàng tối đa 100 ký tự.',
            'bank_account_number.required' => 'Vui lòng nhập số tài khoản.',
            'bank_account_number.regex' => 'Số tài khoản gồm 6-20 chữ số.',
            'bank_account_name.required' => 'Vui lòng nhập tên chủ tài khoản.',
            'bank_account_name.max' => 'Tên chủ tài khoản tối đa 100 ký tự.',
        ];
    }
}
