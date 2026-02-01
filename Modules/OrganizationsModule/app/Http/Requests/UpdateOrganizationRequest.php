<?php

namespace Modules\OrganizationsModule\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrganizationRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:organizations,slug,' . $this->route('organization')->id,
            'description' => 'nullable|array',
            'email' => 'sometimes|required|email|unique:organizations,email,' . $this->route('organization')->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
<<<<<<< HEAD
            'logo' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:2048',
            ],
=======

>>>>>>> 8f82310be1ed3956233161a9a739ff5b62ca6e3c
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
            'logo.image' => 'The file must be an image.',
            'logo.mimes' => 'Supported formats are: jpeg, png, jpg, webp.',
            'logo.max' => 'The logo size must not exceed 2MB.',
        ];
    }
}
