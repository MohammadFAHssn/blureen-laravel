<?php

namespace App\Http\Requests\Food;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FindMealReservationReportRequest extends FormRequest
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
            'date' => 'required|array|size:2',
            'date.*' => 'required|date',
            'meal_id' => 'required|integer|exists:meals,id',
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
            'date.required' => 'انتخاب بازه زمانی الزامی است.',
            'date.array' => 'بازه زمانی باید به صورت آرایه ارسال شود.',
            'date.size' => 'بازه زمانی باید شامل دو تاریخ (شروع و پایان) باشد.',
            'date.*.required' => 'هر دو تاریخ شروع و پایان الزامی است.',
            'date.*.date' => 'تاریخ باید معتبر باشد.',
            'meal_id.required' => 'وعده غذایی الزامی است.',
            'meal_id.integer' => 'شناسه وعده غذایی باید عدد باشد.',
            'meal_id.exists' => 'وعده غذایی انتخاب‌شده معتبر نیست.',
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
