<?php

namespace App\Http\Requests\HrRequest;

use App\Constants\AppConstants;
use Illuminate\Foundation\Http\FormRequest;
use phpDocumentor\Reflection\Exception\PcreException;

class UpdateHrRequest extends FormRequest
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
            AppConstants::HR_REQUEST_TYPES['DAILY_LEAVE'] => $this->rulesForDailyLeaveRequest(),
            AppConstants::HR_REQUEST_TYPES['HOURLY_LEAVE'] => $this->rulesForHourlyLeaveRequest(),
            AppConstants::HR_REQUEST_TYPES['OVERTIME'] => $this->rulesForOvertimeRequest(),
            AppConstants::HR_REQUEST_TYPES['SICK'] => $this->rulesForSickRequest(),
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
            'user_id.required'           => 'انتخاب :attribute الزامی است.',
            'user_id.exists'             => ':attribute انتخاب‌شده در سیستم یافت نشد.',

            'request_type_id.required'   => 'انتخاب :attribute الزامی است.',
            'request_type_id.integer'    => ':attribute معتبر نیست.',
            'request_type_id.exists'     => ':attribute انتخاب‌شده نامعتبر است.',

            'start_date.required'        => 'وارد کردن :attribute الزامی است.',
            'start_date.date'            => 'فرمت :attribute نامعتبر است.',
            'end_date.required'          => 'وارد کردن :attribute الزامی است.',
            'end_date.date'              => 'فرمت :attribute نامعتبر است.',
            'end_date.after_or_equal'    => ':attribute باید بعد از یا برابر با :other باشد.',

            'start_time.required'        => 'وارد کردن :attribute الزامی است.',
            'start_time.date_format'     => ':attribute باید در قالب HH:mm (مثلاً 08:30) باشد.',
            'end_time.required'          => 'وارد کردن :attribute الزامی است.',
            'end_time.date_format'       => ':attribute باید در قالب HH:mm (مثلاً 17:45) باشد.',
            'end_time.different'         => ':attribute نباید با :other یکسان باشد.',

            'details.description.required' => 'وارد کردن :attribute الزامی است.',
            'details.description.string'   => ':attribute باید متن باشد.',
            'details.description.max'      => ':attribute نباید بیش از :max کاراکتر باشد.',
        ];
    }

    public function attributes(): array
    {
        return [
            'user_id'               => 'کاربر',
            'request_type_id'       => 'نوع درخواست',
            'start_date'            => 'تاریخ شروع',
            'end_date'              => 'تاریخ پایان',
            'start_time'            => 'ساعت شروع',
            'end_time'              => 'ساعت پایان',
            'details.description'   => 'توضیح درخواست',
        ];
    }

}
