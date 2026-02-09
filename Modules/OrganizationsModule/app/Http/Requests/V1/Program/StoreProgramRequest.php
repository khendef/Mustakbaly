<?php

namespace Modules\OrganizationsModule\Http\Requests\V1\Program;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\OrganizationsModule\Models\Organization;

class StoreProgramRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation (merge route param so we can validate it).
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'organization_id' => $this->route('orgId'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'organization_id' => ['required', 'integer', Rule::exists(Organization::class, 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'objectives' => ['nullable', 'string'],
            'status' => ['required', 'in:archived,completed,in_progress'],
            'required_budget' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'organization_id.required' => 'The organization ID is required.',
            'organization_id.exists' => 'The specified organization does not exist.',
            'title.required' => 'The program title is required.',
            'title.string' => 'The program title must be a string.',
            'title.max' => 'The program title may not be greater than 255 characters.',
            'description.string' => 'The program description must be a string.',
            'objectives.string' => 'The program objectives must be a string.',
            'status.required' => 'The program status is required.',
            'status.in' => 'The program status must be one of the following: archived, completed, in_progress.',
            'required_budget.required' => 'The required budget is required.',
            'required_budget.numeric' => 'The required budget must be a number.',
            'required_budget.min' => 'The required budget must be at least 0.',
        ];
    }

    public function attributes(): array
    {
        return [
            'organization_id' => 'organization',
            'title' => 'Program Title',
            'description' => 'Program Description',
            'objectives' => 'Program Objectives',
            'status' => 'Program Status',
            'required_budget' => 'Required Budget',
        ];
    }
}
