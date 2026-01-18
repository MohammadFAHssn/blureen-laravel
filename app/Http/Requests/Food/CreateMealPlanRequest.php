<?php

namespace App\Http\Requests\Food;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateMealPlanRequest extends FormRequest
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
            'date' => 'required|date',  // e.g. Y-m-d
            'meal_id' => 'required|integer|exists:meals,id',
            'food_id' => 'required|integer|exists:foods,id',
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
            'date.required' => 'تاریخ الزامی است.',
            'date.date' => 'فرمت تاریخ معتبر نیست.',
            'meal_id.required' => 'وعده غذایی الزامی است.',
            'meal_id.integer' => 'وعده غذایی باید عدد باشد.',
            'meal_id.exists' => 'وعده غذایی انتخاب‌شده معتبر نیست.',
            'food_id.required' => 'غذا الزامی است.',
            'food_id.integer' => 'غذا باید عدد باشد.',
            'food_id.exists' => 'غذای انتخاب‌شده معتبر نیست.',
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
