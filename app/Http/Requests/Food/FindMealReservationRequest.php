<?php

namespace App\Http\Requests\Food;

use Illuminate\Foundation\Http\FormRequest;

class FindMealReservationRequest extends FormRequest
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
            'delivery_code' => 'required|integer|exists:meal_reservations,delivery_code',
        ];
    }
}
