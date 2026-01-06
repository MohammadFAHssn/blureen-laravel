<?php

namespace App\Http\Requests\Food;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateMealReservationRequest extends FormRequest
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
            'date' => 'required|array',
            'date.*' => 'required|date',

            'meal_id' => 'required|integer|exists:meals,id',
            'reserve_type' => 'required|string|in:personnel,contractor,guest,repairman',
            'supervisor_id' => 'required|integer|exists:users,id',

            // personnel
            'personnel' => 'required_if:reserve_type,personnel|array',
            'personnel.*' => 'required_if:reserve_type,personnel|integer|exists:users,id',

            // contractor
            'contractor' => 'required_if:reserve_type,contractor|integer|exists:contractors,id',

            // quantity (contractor, guest and repairman)
            'quantity' => 'required_if:reserve_type,contractor,guest,repairman|integer|min:1',

            // guest
            'serve_place' => 'required_if:reserve_type,guest|string|in:serve_in_kitchen,deliver',
            'attendance_hour' => 'nullable|required_if:reserve_type,guest|required_if:serve_place,serve_in_kitchen|date_format:H:i',

            // guest and repairman
            'description' => 'required_if:reserve_type,guest,repairman|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'date.required'        => 'انتخاب حداقل یک تاریخ الزامی است.',
            'date.array'           => 'لیست تاریخ باید به صورت آرایه ارسال شود.',
            'date.*.required'      => 'انتخاب حداقل یک تاریخ الزامی است.',
            'date.*.date'          => 'تاریخ باید معتبر باشد.',

            'meal_id.required'     => 'وعده غذایی الزامی است.',
            'meal_id.integer'      => 'شناسه وعده غذایی باید عدد صحیح باشد.',
            'meal_id.exists'       => 'وعده غذایی انتخاب‌شده معتبر نیست.',

            'reserve_type.required'=> 'نوع رزرو الزامی است.',
            'reserve_type.string'  => 'نوع رزرو باید متن باشد.',
            'reserve_type.in'      => 'نوع رزرو انتخاب‌شده معتبر نیست.',

            'supervisor_id.required' => 'انتخاب مسئول مربوطه الزامی است.',
            'supervisor_id.integer'  => 'شناسه مسئول باید عدد صحیح باشد.',
            'supervisor_id.exists'   => 'مسئول انتخاب‌شده معتبر نیست.',

            // personnel
            'personnel.required_if'   => 'برای رزرو پرسنلی، انتخاب حداقل یک پرسنل الزامی است.',
            'personnel.array'         => 'لیست پرسنل باید به صورت آرایه ارسال شود.',
            'personnel.*.required_if' => 'شناسه هر پرسنل الزامی است.',
            'personnel.*.integer'     => 'شناسه هر پرسنل باید عدد صحیح باشد.',
            'personnel.*.exists'      => 'حداقل یکی از پرسنل انتخاب‌شده معتبر نیست.',

            // contractor
            'contractor.required_if'  => 'برای رزرو پیمانکار، انتخاب پیمانکار الزامی است.',
            'contractor.integer'     => 'شناسه پیمانکار باید عدد صحیح باشد.',
            'contractor.exists'      => 'پیمانکار انتخاب‌شده معتبر نیست.',

            // quantity (contractor, guest and repairman)
            'quantity.required_if'    => 'تعداد الزامی است.',
            'quantity.integer'        => 'تعداد باید عدد صحیح باشد.',
            'quantity.min'            => 'تعداد باید حداقل ۱ باشد.',

            // guest
            'serve_place.required_if' => 'برای رزرو مهمان، محل سرو الزامی است.',
            'serve_place.string'      => 'محل سرو باید متن معتبر باشد.',
            'serve_place.in'          => 'محل سرو انتخاب‌شده معتبر نیست.',

            // attendance_hour
            'attendance_hour.required_if' => 'برای رزرو مهمان در صورت سرو در آشپزخانه، ساعت حضور الزامی است.',
            'attendance_hour.date_format' => 'فرمت ساعت حضور معتبر نیست (مثال: 14:30).',

            // description
            'description.required_if' => 'برای رزرو مهمان یا تعمیرکار، وارد کردن توضیحات الزامی است.',
            'description.string'      => 'توضیحات باید متن معتبر باشد.',
            'description.max'         => 'توضیحات نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
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
