<?php

namespace App\Http\Requests\HrRequest;

use App\Constants\AppConstants;
use Illuminate\Foundation\Http\FormRequest;
use phpDocumentor\Reflection\Exception\PcreException;

class GetEmployeeAttendanceRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'شناسه کاربر الزامی است.',
            'user_id.integer'  => 'شناسه کاربر باید یک عدد صحیح باشد.',
            'user_id.exists'   => 'شناسه کاربر وارد شده در سیستم یافت نشد.',

            'start_date.required' => 'تاریخ شروع الزامی است.',
            'start_date.date'     => 'فرمت تاریخ شروع نامعتبر است.',

            'end_date.required'        => 'تاریخ پایان الزامی است.',
            'end_date.date'            => 'فرمت تاریخ پایان نامعتبر است.',
            'end_date.after_or_equal'  => 'تاریخ پایان باید تاریخی برابر یا پس از تاریخ شروع باشد.',
        ];
    }



}
