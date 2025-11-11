<?php

namespace App\Http\Requests\HSE;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHealthCertificateRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file_name' => 'required|string|max:255',
            'month' => 'string|max:255',
            'year' => 'string|max:255',
            'status' => 'required|integer',
        ];
    }

    /**
     * Custom validation messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            'file_name.required' => 'نام الزامی است.',
            'file_name.string' => 'نام باید به صورت متن باشد.',
            'file_name.max' => 'نام نباید بیشتر از 255 کاراکتر باشد.',
            'month.string' => 'ماه باید به صورت متن باشد.',
            'month.max' => 'ماه نباید بیشتر از 255 کاراکتر باشد.',
            'year.string' => 'سال باید به صورت متن باشد.',
            'year.max' => 'سال نباید بیشتر از 255 کاراکتر باشد.',
            'file.file' => 'فایل باید معتبر باشد.',
            'file.mimes' => 'فقط فایل‌های اکسل (xls یا xlsx) مورد قبول می‌باشد.',
            'status.required' => 'وضعیت الزامی است.',
            'status.integer' => 'وضعیت باید فعال یا غیرفعال باشد.',
        ];
    }
}
