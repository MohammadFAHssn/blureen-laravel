<?php

namespace App\Http\Requests\Food;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateMealReservationEligibilityRuleRequest extends FormRequest
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
            'meal_id' => 'required|integer|exists:meals,id',
            'time' => 'required|date_format:H:i',
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
            'meal_id.required' => 'وعده غذایی الزامی است.',
            'meal_id.integer' => 'شناسه وعده غذایی باید عدد باشد.',
            'meal_id.exists' => 'وعده غذایی انتخاب‌ شده معتبر نیست.',
            'time.required' => 'زمان الزامی است.',
            'time.date_format' => 'فرمت زمان معتبر نیست.',
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
