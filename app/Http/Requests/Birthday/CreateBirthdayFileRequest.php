<?php

namespace App\Http\Requests\Birthday;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateBirthdayFileRequest extends FormRequest
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
            'month' => 'required|string|max:255',
            'year' => 'required|string|max:255',
            'file' => 'required|file|mimes:xls,xlsx',
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
            'file_name.required' => 'نام فایل الزامی است.',
            'file_name.string' => 'نام فایل باید به صورت متن باشد.',
            'file_name.max' => 'نام فایل نباید بیشتر از 255 کاراکتر باشد.',
            'month.required' => 'ماه الزامی باشد.',
            'month.string' => 'ماه باید به صورت متن باشد.',
            'month.max' => 'ماه نباید بیشتر از 255 کاراکتر باشد.',
            'year.required' => 'سال الزامی باشد.',
            'year.string' => 'سال باید به صورت متن باشد.',
            'year.max' => 'سال نباید بیشتر از 255 کاراکتر باشد.',
            'file.required' => 'آپلود فایل اکسل الزامی است.',
            'file.file' => 'فایل باید معتبر باشد.',
            'file.mimes' => 'فقط فایل‌های اکسل (xls یا xlsx) مورد قبول می‌باشد.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'خطا در اعتبارسنجی اطلاعات ورودی!',
            'errors' => $validator->errors(),
        ], 422));
    }
}
