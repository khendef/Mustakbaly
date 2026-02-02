<?php

namespace Modules\AssesmentModule\Http\Requests\AttemptRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreAttemptRequest
 *
 * This class handles the validation for creating a new attempt for a quiz. 
 * It ensures that the `quiz_id`, `student_id`, `attempt_number`, `score`, `is_passed`, `start_at`, and `ends_at` fields 
 * are valid according to the specified rules. 
 * The `attempt_number` must be unique for the specific student and quiz combination, 
 * while the `score` and `is_passed` fields are optional but validated if provided.
 * 
 * @package Modules\AssesmentModule\Http\Requests\AttemptRequest
 */
class StoreAttemptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * This method checks if the user is authorized to create a new attempt for the specified quiz and student.
     * By default, it returns `true`, meaning the request is always authorized.
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
     * This method defines the validation rules for the `quiz_id`, `student_id`, `attempt_number`, 
     * `score`, `is_passed`, `start_at`, and `ends_at` fields. 
     * - `quiz_id` and `student_id` must exist in their respective tables.
     * - `attempt_number` is optional, but if provided, it must be unique for the specific quiz and student.
     * - `score` is optional and must be a non-negative integer if provided.
     * - `is_passed` is optional and must be a boolean value.
     * - `start_at` and `ends_at` are optional, but if provided, `ends_at` must be after or equal to `start_at`.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quiz_id' => ['required', 'integer', 'exists:quizzes,id'],
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'attempt_number' => [
                'sometimes', 
                'integer', 
                'min:1',
                Rule::unique('attempts', 'attempt_number')
                    ->where(fn ($q) => $q
                        ->where('quiz_id', $this->input('quiz_id'))
                        ->where('student_id', $this->input('student_id'))
                    ),
            ],

            'score' => ['sometimes', 'integer', 'min:0'],
            'is_passed' => ['sometimes', 'boolean'],

            'start_at' => ['sometimes', 'date'],
            'ends_at' => ['sometimes', 'date', 'after_or_equal:start_at'],
        ];
    }

    /**
     * Get the custom validation error messages.
     *
     * This method returns an array of custom error messages, making it easier for users to understand 
     * why a validation rule failed. The error messages are customized for certain fields like `quiz_id`, 
     * `student_id`, `attempt_number`, and `ends_at`.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'quiz_id.required' => 'quiz_id is required',
            'student_id.required' => 'student_id is required',
            'attempt_number.required' => 'attempt_number is required',
            'attempt_number.unique' => 'attempt_number already used for this quiz and student',
            'ends_at.after_or_equal' => 'ends_at must be after or equal start_at',
        ];
    }
}
