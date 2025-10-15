<?php

namespace App\Http\Requests\HrRequest;

use App\Constants\AppConstants;
use Illuminate\Foundation\Http\FormRequest;
use phpDocumentor\Reflection\Exception\PcreException;

class CreateHrRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $formType = $this->integer('request_type_id');
        return match ($formType) {
            AppConstants::HR_REQUEST_TYPE_DAILY_LEAVE => $this->rulesForDailyLeaveRequest(),
            AppConstants::HR_REQUEST_TYPE_HOURLY_LEAVE => $this->rulesForHourlyLeaveRequest(),
            AppConstants::HR_REQUEST_TYPE_OVERTIME => $this->rulesForOvertimeRequest(),
            AppConstants::HR_REQUEST_TYPE_SICK => $this->rulesForSickRequest(),
            default => throw new PcreException('فرم ارسالی نامعتبر است',400),
        };
    }
    protected function baseRules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'request_type_id' => ['required', 'integer', 'exists:request_types,id'],
        ];
    }
    protected function rulesForDailyLeaveRequest(): array
    {
        return array_merge($this->baseRules(), [
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);
    }
    protected function rulesForHourlyLeaveRequest(): array
    {
        return array_merge($this->baseRules(), [
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i','different:start_time'],
        ]);
    }
    protected function rulesForOvertimeRequest(): array
    {
        return array_merge($this->baseRules(), [
            'start_date' => ['required','date'],
            'end_date' => ['required','date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'  => ['required', 'date_format:H:i'],
            'details.description' => ['required','string','max:500']
        ]);
    }
    protected function rulesForSickRequest(): array
    {
        return array_merge($this->baseRules(), [
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'details.description' => ['required','string','max:500']
        ]);
    }
    public function messages(): array
    {
        return [
            'request_type_id.required' => 'نوع درخواست مشخص نشده است.',
        ];
    }
}
