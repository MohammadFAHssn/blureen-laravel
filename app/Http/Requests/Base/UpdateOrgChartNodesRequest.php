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
            'orgChartNode' => ['required', 'array'],
            'orgChartNode.id' => ['required'],
            'orgChartNode.parentId' => ['nullable'],
            'orgChartNode.orgPosition' => ['required', 'array'],
            'orgChartNode.orgPosition.id' => ['required', 'integer', 'exists:org_positions,id'],
            'orgChartNode.orgUnit' => ['required', 'array'],
            'orgChartNode.orgUnit.id' => ['required'],
            'orgChartNode.orgUnit.name' => ['required', 'string'],
            'orgChartNode.users' => ['required', 'array', 'min:1', 'max:500'],
            'orgChartNode.users.*.id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
