<?php

namespace App\Http\Requests\Evaluation;

use App\Constants\AppConstants;
use Illuminate\Foundation\Http\FormRequest;

class SelfEvaluateRequest extends FormRequest
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
            'answers' => 'required|array|max:100',
            'answers.*.question_id' => 'required|integer|exists:evaluation_questions,id',
            'answers.*.question_type_id' => 'required|integer|exists:question_types,id',

            // 'answers.*.score' => 'numeric|min:1|max:10',
            'answers.*.score' => [
                'nullable',
                'numeric',
                'min:1',
                'max:10',
                'required_if:answers.*.question_type_id,' . AppConstants::QUESTION_TYPES['RATING'],
                'prohibited_unless:answers.*.question_type_id,' . AppConstants::QUESTION_TYPES['RATING'],
            ],

            // 'answers.*.option_id' => 'integer|exists:evaluation_question_options,id',
            'answers.*.option_id' => [
                'nullable',
                'integer',
                'exists:evaluation_question_options,id',
                'required_if:answers.*.question_type_id,' . AppConstants::QUESTION_TYPES['SINGLE_CHOICE'],
                'prohibited_unless:answers.*.question_type_id,' . AppConstants::QUESTION_TYPES['SINGLE_CHOICE'],
            ],

            // 'answers.*.option_ids' => 'array|max:10',
            'answers.*.option_ids' => [
                'nullable',
                'array',
                'max:10',
                'required_if:answers.*.question_type_id,' . AppConstants::QUESTION_TYPES['MULTIPLE_CHOICE'],
                'prohibited_unless:answers.*.question_type_id,' . AppConstants::QUESTION_TYPES['MULTIPLE_CHOICE'],
            ],
            'answers.*.option_ids.*' => 'required|integer|exists:evaluation_question_options,id',

            'answers.*.text' => [
                'nullable',
                'string',
                'max:512',
                'required_if:answers.*.question_type_id,' . AppConstants::QUESTION_TYPES['OPEN_ENDED'],
                'prohibited_unless:answers.*.question_type_id,' . AppConstants::QUESTION_TYPES['OPEN_ENDED'],
            ],
        ];
    }
}
