<?php

namespace App\Http\Requests\Contractor;

use Illuminate\Foundation\Http\FormRequest;

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
            'national_code' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:255',
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

            'national_code.required' => 'کد ملی الزامی است.',
            'national_code.string' => 'کد ملی باید به صورت متن باشد.',
            'national_code.max' => 'کد ملی نباید بیشتر از 255 کاراکتر باشد.',

            'mobile_number.required' => 'موبایل الزامی است.',
            'mobile_number.string' => 'موبایل باید به صورت متن باشد.',
            'mobile_number.max' => 'موبایل نباید بیشتر از 255 کاراکتر باشد.',
        ];
    }
}
