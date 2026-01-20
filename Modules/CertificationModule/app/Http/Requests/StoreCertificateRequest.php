<?php
namespace Modules\CertificationModule\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreCertificateRequest extends FormRequest
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
            'organization_id' => 'required|integer|exists:organizations,id',
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
            'organization_id.integer' => 'The organization ID must be an integer.',
            'organization_id.exists' => 'The specified organization does not exist.',
            'recipient_name.required' => 'The recipient name is required.',
            'recipient_name.string' => 'The recipient name must be a string.',
            'recipient_name.max' => 'The recipient name may not be greater than 255 characters.',
            'course_name.required' => 'The course name is required.',
            'course_name.string' => 'The course name must be a string.',
            'course_name.max' => 'The course name may not be greater than 255 characters.',
            'completion_date.required' => 'The completion date is required.',
            'completion_date.date' => 'The completion date must be a valid date.',
            'issue_date.required' => 'The issue date is required.',
            'issue_date.date' => 'The issue date must be a valid date.',
        ];
    }
}
