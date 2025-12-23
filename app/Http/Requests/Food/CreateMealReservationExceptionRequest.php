<?php

namespace App\Http\Requests\Food;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateMealReservationExceptionRequest extends FormRequest
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
            'users' => 'required|array',
            'users.*' => 'integer|exists:users,id',
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
            'users.required'       => 'انتخاب حداقل یک کاربر الزامی است.',
            'users.array'          => 'لیست کاربران باید به صورت آرایه ارسال شود.',

            'users.*.integer'      => 'شناسه هر کاربر باید عدد باشد.',
            'users.*.exists'       => 'کاربر انتخاب‌شده معتبر نیست.',

            'meal_id.required'     => 'وعده غذایی الزامی است.',
            'meal_id.integer'      => 'شناسه وعده غذایی باید عدد صحیح باشد.',
            'meal_id.exists'       => 'وعده غذایی انتخاب‌شده معتبر نیست.',
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
