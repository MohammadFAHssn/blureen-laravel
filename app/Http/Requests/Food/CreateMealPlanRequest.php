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
            'year' => 'required|integer|min:1,max:1000000',
            'month' => 'required|integer|min:1,max:12',
            'day' => 'required|integer|min:1,max:31',
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
            'year.required' => 'سال الزامی است.',
            'year.integer' => 'سال باید به صورت عدد باشد.',
            'year.min' => 'سال معتبر نیست.',
            'year.max' => 'سال نباید بیشتر از 1000000 باشد.',
            'month.required' => 'ماه الزامی است.',
            'month.integer' => 'ماه باید به صورت عدد باشد.',
            'month.min' => 'ماه نمی‌تواند کمتر از 1 باشد.',
            'month.max' => 'ماه نمی‌تواند بیشتر از 12 باشد.',
            'day.required' => 'روز الزامی است.',
            'day.integer' => 'روز باید به صورت عدد باشد.',
            'day.min' => 'روز نمی‌تواند کمتر از 1 باشد.',
            'day.max' => 'روز نمی‌تواند بیشتر از 31 باشد.',
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
