<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;
use App\Constants\AppConstants;

class CreatePayrollBatchRequest extends FormRequest
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
            'file' => 'required|file|mimes:xlsx,xls|max:' . AppConstants::MAX_FILE_SIZE,
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:1390|max:1430', // :)
        ];
    }
}
