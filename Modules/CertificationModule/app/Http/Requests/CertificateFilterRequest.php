<?php
namespace Modules\CertificationModule\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class CertificateFilterRequest extends FormRequest
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
            'organization_id' => 'nullable|integer|exists:organizations,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'certificate_number' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'organization_id.integer' => 'The organization ID must be an integer.',
            'organization_id.exists' => 'The specified organization does not exist.',
            'from_date.date' => 'The from date must be a valid date.',
            'to_date.date' => 'The to date must be a valid date.',
            'certificate_number.string' => 'The certificate number must be a string.',
            'certificate_number.max' => 'The certificate number may not be greater than 100 characters.',
        ];
    }
}
