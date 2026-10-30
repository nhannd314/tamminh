<?php

namespace App\Http\Requests;

use App\Gender;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreFaceAnalysisRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'birth_day' => ['required', 'integer', 'between:1,31'],
            'birth_month' => ['required', 'integer', 'between:1,12'],
            'birth_year' => ['required', 'integer', 'between:1900,'.now()->year],
            'birth_hour' => ['nullable', 'integer', 'between:0,23'],
            'birth_minute' => ['nullable', 'integer', 'between:0,59'],
            'portrait' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192', 'dimensions:min_width=200,min_height=200'],
            'agree_terms' => ['accepted'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['birth_day', 'birth_month', 'birth_year'])) {
                    return;
                }

                if (! checkdate($this->integer('birth_month'), $this->integer('birth_day'), $this->integer('birth_year'))) {
                    $validator->errors()->add('birth_date', 'Ngày sinh không hợp lệ.');
                } elseif ($this->birthDate()->isFuture()) {
                    $validator->errors()->add('birth_date', 'Ngày sinh không được ở tương lai.');
                }
            },
        ];
    }

    /**
     * Form nằm giữa trang chủ nên quay lại đúng vị trí form khi có lỗi.
     */
    protected function getRedirectUrl(): string
    {
        return route('home').'#xem-tuong';
    }

    public function birthDate(): CarbonImmutable
    {
        return CarbonImmutable::create($this->integer('birth_year'), $this->integer('birth_month'), $this->integer('birth_day'));
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'gender.required' => 'Vui lòng chọn giới tính.',
            'birth_day.required' => 'Vui lòng chọn đầy đủ ngày sinh.',
            'birth_month.required' => 'Vui lòng chọn đầy đủ ngày sinh.',
            'birth_year.required' => 'Vui lòng chọn đầy đủ ngày sinh.',
            'portrait.required' => 'Vui lòng tải lên ảnh chân dung.',
            'portrait.image' => 'Tệp tải lên phải là ảnh.',
            'portrait.mimes' => 'Ảnh phải có định dạng JPG, PNG hoặc WEBP.',
            'portrait.max' => 'Ảnh không được lớn hơn 8MB.',
            'portrait.dimensions' => 'Ảnh quá nhỏ, vui lòng dùng ảnh tối thiểu 200×200 px.',
            'agree_terms.accepted' => 'Bạn cần đồng ý với điều khoản để tiếp tục.',
        ];
    }
}
