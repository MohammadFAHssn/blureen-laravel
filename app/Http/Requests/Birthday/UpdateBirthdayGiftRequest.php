<?php

namespace App\Http\Requests\Birthday;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateBirthdayGiftRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'amount' => 'required|integer|min:1',
            'image' => 'sometimes|nullable|image|max:2048',
            'status' => 'nullable|integer|min:0|max:1'
        ];
    }

    /**
     * Custom validation messages
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'نام هدیه الزامی است.',
            'name.string' => 'نام هدیه باید به صورت متن باشد.',
            'name.max' => 'نام هدیه نباید بیشتر از 255 کاراکتر باشد.',
            'code.required' => 'کد هدیه الزامی باشد.',
            'code.string' => 'کد هدیه باید به صورت متن باشد.',
            'code.max' => 'کد هدیه نباید بیشتر از 255 کاراکتر باشد.',
            'amount.required' => 'مقدار هدیه الزامی باشد.',
            'amount.integer' => 'مقدار هدیه باید به صورت عدد صحیح باشد.',
            'amount.min' => 'مقدار هدیه نباید کمتر از 1 باشد.',
            'image.image' => 'فایل باید یک تصویر معتبر باشد.',
            'image.max' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد.',
            'status.integer' => 'وضعیت هدیه باید به صورت عدد صحیح باشد.',
            'status.min' => 'وضعیت هدیه نباید کمتر از 0 و بیشتر از 1 باشد.',
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
