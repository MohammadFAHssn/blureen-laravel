<?php

namespace App\Http\Requests\Evaluation;

use Illuminate\Foundation\Http\FormRequest;

class EvaluateRequest extends FormRequest
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
            'results' => 'required|array|max:100',
            'results.*.evaluatee_id' => 'required|integer|exists:evaluatees,id',
            'results.*.question_id' => 'required|integer|exists:evaluation_questions,id',
            'results.*.score' => 'required|integer|between:1,10',
        ];
    }
}
