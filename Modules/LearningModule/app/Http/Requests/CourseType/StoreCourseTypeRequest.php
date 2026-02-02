<?php

namespace Modules\LearningModule\Http\Requests\CourseType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for storing a new course type.
 * Translatable fields (name, description) accept string or array with en/ar keys.
 */
class StoreCourseTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        foreach (['name', 'description'] as $key) {
            if ($this->has($key) && is_string($this->input($key))) {
                $this->merge([$key => ['en' => $this->input($key)]]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'array'],
            'name.en' => ['required_without:name.ar', 'nullable', 'string', 'max:100'],
            'name.ar' => ['nullable', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:course_types,slug'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
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
