<?php

namespace Modules\LearningModule\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for filtering courses.
 * Handles validation for course filtering parameters.
 */
class FilterCoursesRequest extends FormRequest
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
            'status' => ['nullable', 'string', Rule::in(['draft', 'review', 'published', 'archived'])],
            'course_type_id' => ['nullable', 'integer', 'exists:course_types,course_type_id'],
            'program_id' => ['nullable', 'integer'],
            'language' => ['nullable', 'string', 'max:10', Rule::in(['ar', 'en'])],
            'difficulty_level' => ['nullable', 'string', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'min_rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'created_by' => ['nullable', 'integer'],
            'instructor_id' => ['nullable', 'integer'],
            'is_offline_available' => ['nullable', 'boolean'],
            'course_delivery_type' => ['nullable', 'string', Rule::in(['self_paced', 'interactive', 'hybrid'])],
            'order_by' => ['nullable', 'string', 'max:255'],
            'order_direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
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
            'status.in' => 'Please select a valid status.',
            'course_type_id.integer' => 'The course type ID must be an integer.',
            'course_type_id.exists' => 'The selected course type does not exist.',
            'program_id.integer' => 'The program ID must be an integer.',
            'language.in' => 'Please select a valid language.',
            'difficulty_level.in' => 'Please select a valid difficulty level.',
            'min_rating.numeric' => 'The minimum rating must be a number.',
            'min_rating.min' => 'The minimum rating must be at least 0.',
            'min_rating.max' => 'The minimum rating cannot exceed 5.',
            'created_by.integer' => 'The creator ID must be an integer.',
            'instructor_id.integer' => 'The instructor ID must be an integer.',
            'is_offline_available.boolean' => 'The offline availability must be true or false.',
            'course_delivery_type.in' => 'Please select a valid delivery type.',
            'order_direction.in' => 'The order direction must be either asc or desc.',
            'per_page.integer' => 'The per page value must be an integer.',
            'per_page.min' => 'The per page value must be at least 1.',
            'per_page.max' => 'The per page value cannot exceed 100.',
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
            'program_id' => 'program',
            'min_rating' => 'minimum rating',
            'created_by' => 'creator',
            'instructor_id' => 'instructor',
            'is_offline_available' => 'offline availability',
            'course_delivery_type' => 'delivery type',
            'difficulty_level' => 'difficulty level',
            'order_by' => 'order by',
            'order_direction' => 'order direction',
            'per_page' => 'per page',
        ];
    }
}
