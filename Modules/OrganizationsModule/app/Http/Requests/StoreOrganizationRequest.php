<?php
namespace Modules\OrganizationsModule\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrganizationRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:organizations,slug',
            'description' => 'nullable|array',
            'email' => 'required|email|unique:organizations,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The organization name is required.',
            'slug.required' => 'The organization slug is required.',
            'slug.unique' => 'The organization slug must be unique.',
            'email.required' => 'The organization email is required.',
            'email.email' => 'The organization email must be a valid email address.',
            'email.unique' => 'The organization email must be unique.',
        ];
    }
}
