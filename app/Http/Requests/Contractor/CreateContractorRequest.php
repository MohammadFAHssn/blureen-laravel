<?php

namespace App\Http\Requests\Contractor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateContractorRequest extends FormRequest
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
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
            'first_name.required' => 'نام الزامی است.',
            'first_name.string' => 'نام باید به صورت متن باشد.',
            'first_name.max' => 'نام نباید بیشتر از 255 کاراکتر باشد.',

            'last_name.required' => 'نام خانوادگی الزامی است.',
            'last_name.string' => 'نام خانوادگی باید به صورت متن باشد.',
            'last_name.max' => 'نام خانوادگی نباید بیشتر از 255 کاراکتر باشد.',

            'description.string' => 'توضیحات باید به صورت متن باشد.',
            'description.max' => 'توضیحات نباید بیشتر از 255 کاراکتر باشد.',
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
