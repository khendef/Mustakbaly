<?php

namespace Modules\LearningModule\Http\Requests\CourseType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\LearningModule\Models\CourseType;

/**
 * Form request for updating an existing course type.
 * Translatable fields accept string or array with en/ar keys.
 */
class UpdateCourseTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
        $courseTypeId = $this->route('courseType');
        $courseTypeId = $courseTypeId instanceof CourseType ? $courseTypeId->course_type_id : $courseTypeId;

        return [
            'name' => ['sometimes', 'required', 'array'],
            'name.en' => ['nullable', 'string', 'max:100'],
            'name.ar' => ['nullable', 'string', 'max:100'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:100', Rule::unique('course_types', 'slug')->ignore($courseTypeId, 'course_type_id')],
            'description' => ['sometimes', 'nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'nullable', 'boolean'],
            'target_audience' => ['sometimes', 'nullable', 'string'],
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
