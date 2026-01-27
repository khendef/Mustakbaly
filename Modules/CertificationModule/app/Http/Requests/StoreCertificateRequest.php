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
            'enrollment_id' => ['required', 'exists:enrollments,id'],
            'organization_id' => ['required', 'exists:organizations,id'],
            'certificate_number' => ['required', 'string', 'max:255', 'unique:certificates,certificate_number'],
            'completion_date' => ['required', 'date'],
            'issue_date' => ['required', 'date', 'after_or_equal:completion_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'enrollment_id.required' => 'the enrollment ID I=is required',
             'enrollment_id.exists' => 'The specified enrollment_id does not exist.',
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
