<?php

namespace Modules\LearningModule\Http\Requests\CourseType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for storing a new course type.
 * Handles validation for course type creation.
 */
class StoreCourseTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:course_types,name'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:course_types,slug'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'target_audience' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The course type name is required.',
            'name.max' => 'The course type name may not be greater than 100 characters.',
            'name.unique' => 'This course type name is already taken.',
            'slug.unique' => 'This slug is already taken.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'is_active' => 'active status',
            'target_audience' => 'target audience',
        ];
    }
}
