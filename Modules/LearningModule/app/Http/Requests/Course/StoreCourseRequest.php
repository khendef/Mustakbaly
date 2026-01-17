<?php

namespace Modules\LearningModule\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for storing a new course.
 * Handles validation for course creation.
 */
class StoreCourseRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:courses,slug'],
            'description' => ['nullable', 'string'],
            'objectives' => ['nullable', 'string'],
            'prerequisites' => ['nullable', 'string'],
            'course_type_id' => ['required', 'integer', 'exists:course_types,course_type_id'],
            'actual_duration_hours' => ['required', 'integer', 'min:1'],
            'language' => ['nullable', 'string', 'max:10', Rule::in(['ar', 'en'])],
            'status' => ['nullable', 'string', Rule::in(['draft', 'review', 'published', 'archived'])],
            'min_score_to_pass' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_offline_available' => ['nullable', 'boolean'],
            'course_delivery_type' => ['nullable', 'string', Rule::in(['self_paced', 'interactive', 'hybrid'])],
            'difficulty_level' => ['nullable', 'string', Rule::in(['beginner', 'intermediate', 'advanced'])],
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
            'title.required' => 'The course title is required.',
            'title.max' => 'The course title may not be greater than 255 characters.',
            'slug.unique' => 'This slug is already taken. Please choose a different one.',
            'course_type_id.required' => 'Please select a course type.',
            'course_type_id.exists' => 'The selected course type does not exist.',
            'actual_duration_hours.required' => 'Actual duration is required.',
            'actual_duration_hours.min' => 'Actual duration must be at least 1 hour.',
            'language.in' => 'Please select a valid language.',
            'status.in' => 'Please select a valid status.',
            'min_score_to_pass.min' => 'Minimum score to pass must be at least 0.',
            'min_score_to_pass.max' => 'Minimum score to pass cannot exceed 100.',
            'course_delivery_type.in' => 'Please select a valid delivery type.',
            'difficulty_level.in' => 'Please select a valid difficulty level.',
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
            'course_type_id' => 'course type',
            'actual_duration_hours' => 'actual duration',
            'min_score_to_pass' => 'minimum score to pass',
            'is_offline_available' => 'offline availability',
            'course_delivery_type' => 'delivery type',
            'difficulty_level' => 'difficulty level',
        ];
    }
}
