<?php
namespace Modules\OrganizationsModule\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDonorRequest extends FormRequest
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
           'user_id' => ['sometimes', 'required', 'exists:users,id'],
            'description' => ['nullable', 'array'],
            'name' => ['sometimes', 'required', 'array','string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The donor name is required.',
            'email.required' => 'The donor email is required.',
            'email.email' => 'The donor email must be a valid email address.',
            'email.unique' => 'The donor email must be unique.',
        ];
    }
}
