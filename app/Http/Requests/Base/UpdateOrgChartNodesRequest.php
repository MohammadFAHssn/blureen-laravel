<?php

namespace App\Http\Requests\Base;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrgChartNodesRequest extends FormRequest
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
            'orgChartNodes' => ['required', 'array', 'min:1', 'max:1000'],
            'orgChartNodes.*.id' => ['required'],
            'orgChartNodes.*.parentId' => ['nullable', 'in_array:orgChartNodes.*.id'],
            'orgChartNodes.*.orgPosition' => ['required', 'array', 'min:1', 'max:10'],
            'orgChartNodes.*.orgPosition.id' => ['required', 'integer', 'exists:org_positions,id'],
            'orgChartNodes.*.orgUnit' => ['required', 'array', 'min:1', 'max:10'],
            'orgChartNodes.*.orgUnit.id' => ['required'],
            'orgChartNodes.*.orgUnit.name' => ['required', 'string'],
            'orgChartNodes.*.users' => ['required', 'array', 'min:1', 'max:500'],
            'orgChartNodes.*.users.*.id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
