<?php

namespace Modules\LearningModule\Http\Requests\CourseType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\LearningModule\Models\CourseType;

/**
 * Form request for updating an existing course type.
 * Handles validation for course type updates.
 */
class UpdateCourseTypeRequest extends FormRequest
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
        // Get course type ID from route parameter (could be ID or model instance)
        $courseTypeId = $this->route('courseType');
        $courseTypeId = $courseTypeId instanceof CourseType ? $courseTypeId->course_type_id : $courseTypeId;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('course_types', 'name')->ignore($courseTypeId, 'course_type_id')],
            'slug' => ['sometimes', 'nullable', 'string', 'max:100', Rule::unique('course_types', 'slug')->ignore($courseTypeId, 'course_type_id')],
            'description' => ['sometimes', 'nullable', 'string'],
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
