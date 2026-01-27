<?php
namespace Modules\CertificationModule\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCertificateRequest extends FormRequest
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
            'organization_id' => 'required|exists:organizations,id',
            'recipient_name' => 'required|string|max:255',
            'course_name' => 'required|string|max:255',
            'completion_date' => 'required|date',
            'issue_date' => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'organization_id.required' => 'The organization ID is required.',
            'organization_id.exists' => 'The specified organization does not exist.',
            'recipient_name.required' => 'The recipient name is required.',
            'course_name.required' => 'The course name is required.',
            'completion_date.required' => 'The completion date is required.',
            'completion_date.date' => 'The completion date must be a valid date.',
            'issue_date.required' => 'The issue date is required.',
            'issue_date.date' => 'The issue date must be a valid date.',
        ];
    }
}
