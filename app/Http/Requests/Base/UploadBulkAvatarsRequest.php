<?php

namespace App\Http\Requests\Base;

use Illuminate\Foundation\Http\FormRequest;

class UploadBulkAvatarsRequest extends FormRequest
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

    //  if changed, sync this with FileService avatar collection
    public function rules(): array
    {
        return [
            'files' => 'required|array|min:1|max:500',
            'files.*' => 'file|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'files.required' => 'حداقل یک فایل باید ارسال شود.',
            'files.array' => 'فایل‌ها باید به صورت آرایه ارسال شوند.',
            'files.min' => 'حداقل یک فایل باید ارسال شود.',
            'files.max' => 'حداکثر 500 فایل می‌توانید آپلود کنید.',
            'files.*.file' => 'هر آیتم باید یک فایل معتبر باشد.',
            'files.*.mimes' => 'فرمت فایل باید jpg، jpeg یا png باشد.',
            'files.*.max' => 'حداکثر حجم هر فایل 2 مگابایت است.',
        ];
    }
}
