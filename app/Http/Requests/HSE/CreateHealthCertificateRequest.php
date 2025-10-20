<?php

namespace App\Http\Requests\HSE;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateHealthCertificateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // adjust authorization as needed
        return true;
    }

    /**
     * Normalize input before validation.
     * Ensures 'images' is an array of UploadedFile objects when possible.
     */
    protected function prepareForValidation(): void
    {
        // If files were uploaded under 'images' and it's a single UploadedFile, wrap in array
        if ($this->hasFile('images') && !is_array($this->file('images'))) {
            $this->merge(['images' => [$this->file('images')]]);
        }

        // If the client sent images[] entries, Laravel already maps them to an array in file('images')
        // If somehow images came as a non-file array (e.g. JS sent an array of files incorrectly),
        // we prefer the files() value when available:
        if ($this->files->has('images')) {
            $this->merge(['images' => $this->file('images')]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Accepts:
     * - file_name: required string
     * - month: required integer between 1 and 12
     * - year: required integer (4 digits like 1404)
     * - images: required array with at least one file
     * - images.*: each item must be a valid image file, allowed mimes and size limit
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file_name' => 'required|string|max:255',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|digits_between:3,4',  // accepts 3-4 digit years like 1404
            'images' => 'required|array|min:1',
            'images.*' => 'file|image|mimes:jpeg,png,jpg,gif,webp|max:5120',  // max 5 MB per image
        ];
    }

    /**
     * Custom validation messages (Persian).
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'file_name.required' => 'نام فایل الزامی است.',
            'file_name.string' => 'نام فایل باید متن باشد.',
            'file_name.max' => 'نام فایل نباید بیشتر از 255 کاراکتر باشد.',
            'month.required' => 'ماه الزامی است.',
            'month.integer' => 'ماه باید عدد باشد.',
            'month.between' => 'ماه باید بین 1 تا 12 باشد.',
            'year.required' => 'سال الزامی است.',
            'year.integer' => 'سال باید عدد باشد.',
            'year.digits_between' => 'سال باید یک مقدار معتبر (مثلاً 1404) باشد.',
            'images.required' => 'حداقل یک تصویر باید ارسال شود.',
            'images.array' => 'فرمت تصاویر نامعتبر است.',
            'images.min' => 'حداقل یک تصویر لازم است.',
            'images.*.file' => 'هر ورودی تصویر باید یک فایل معتبر باشد.',
            'images.*.image' => 'فایل ارسالی باید یک تصویر باشد.',
            'images.*.mimes' => 'فرمت تصویر باید jpeg, png, jpg, gif یا webp باشد.',
            'images.*.max' => 'هر تصویر نمی‌تواند بیشتر از 5 مگابایت باشد.',
        ];
    }

    /**
     * Failed validation JSON response.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'خطا در اعتبارسنجی اطلاعات ورودی!',
            'errors' => $validator->errors(),
        ], 422));
    }
}
