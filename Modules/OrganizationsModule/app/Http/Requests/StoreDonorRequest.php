<?php
namespace Modules\OrganizationsModule\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreDonorRequest extends FormRequest
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
            'user_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'array'],
            'description' => ['nullable', 'array','string'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'The user ID is required.',
            'user_id.exists' => 'The specified user does not exist.',
            'name.required' => 'The donor name is required.',
        ];
    }
}
