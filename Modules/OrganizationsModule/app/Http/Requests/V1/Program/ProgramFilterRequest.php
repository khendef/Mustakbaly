<?php
namespace Modules\OrganizationsModule\Http\RequestsV1\Program;
use Illuminate\Foundation\Http\FormRequest;

class ProgramFilterRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'nullable|in:archived,completed,in_progress',
            'organization_id' => 'nullable|integer|exists:organizations,id',
            'min_budget' => 'nullable|numeric|min:0',
            'max_budget' => 'nullable|numeric|min:0',
            'funded' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'The program name must be a string.',
            'name.max' => 'The program name may not be greater than 255 characters.',
            'organization_id.integer' => 'The organization ID must be an integer.',
            'organization_id.exists' => 'The specified organization does not exist.',
            'funded.boolean' => 'The funded field must be true or false.',
            'min_budget.numeric' => 'The minimum budget must be a number.',
            'min_budget.min' => 'The minimum budget must be at least 0.',
            'max_budget.numeric' => 'The maximum budget must be a number.',
            'max_budget.min' => 'The maximum budget must be at least 0.',
        ];
    }

        public function filters(): array
    {
        $filters = $this->validated();

        ksort($filters);

        return $filters;
    }

}
