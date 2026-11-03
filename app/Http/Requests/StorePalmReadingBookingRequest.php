<?php

namespace App\Http\Requests;

use App\Gender;
use App\PalmReadingPackage;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Các trường mang tiền tố `palm_` để không đụng old input/lỗi của form xem tướng cùng trang chủ.
 */
class StorePalmReadingBookingRequest extends FormRequest
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
            'palm_name' => ['required', 'string', 'max:100'],
            'palm_gender' => ['required', Rule::enum(Gender::class)],
            'palm_birth_day' => ['required', 'integer', 'between:1,31'],
            'palm_birth_month' => ['required', 'integer', 'between:1,12'],
            'palm_birth_year' => ['required', 'integer', 'between:1900,'.now()->year],
            'palm_phone' => ['required', 'string', 'regex:/^(\+?84|0)\d{9,10}$/'],
            'palm_package' => ['required', Rule::enum(PalmReadingPackage::class)],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['palm_birth_day', 'palm_birth_month', 'palm_birth_year'])) {
                    return;
                }

                if (! checkdate($this->integer('palm_birth_month'), $this->integer('palm_birth_day'), $this->integer('palm_birth_year'))) {
                    $validator->errors()->add('palm_birth_date', 'Ngày sinh không hợp lệ.');
                } elseif ($this->birthDate()->isFuture()) {
                    $validator->errors()->add('palm_birth_date', 'Ngày sinh không được ở tương lai.');
                }
            },
        ];
    }

    /**
     * Bỏ khoảng trắng, dấu chấm, gạch ngang người dùng hay gõ trong số điện thoại.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('palm_phone')) {
            $this->merge(['palm_phone' => preg_replace('/[\s.\-()]+/', '', (string) $this->input('palm_phone'))]);
        }
    }

    public function birthDate(): CarbonImmutable
    {
        return CarbonImmutable::create($this->integer('palm_birth_year'), $this->integer('palm_birth_month'), $this->integer('palm_birth_day'));
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'palm_name.required' => 'Vui lòng nhập họ và tên.',
            'palm_name.max' => 'Họ và tên tối đa 100 ký tự.',
            'palm_gender.required' => 'Vui lòng chọn giới tính.',
            'palm_birth_day.required' => 'Vui lòng chọn đầy đủ ngày sinh.',
            'palm_birth_month.required' => 'Vui lòng chọn đầy đủ ngày sinh.',
            'palm_birth_year.required' => 'Vui lòng chọn đầy đủ ngày sinh.',
            'palm_phone.required' => 'Vui lòng nhập số điện thoại.',
            'palm_phone.regex' => 'Số điện thoại không hợp lệ.',
            'palm_package.required' => 'Vui lòng chọn gói xem.',
        ];
    }
}
