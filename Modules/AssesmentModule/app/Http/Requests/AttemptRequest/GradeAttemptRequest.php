<?php

namespace Modules\AssesmentModule\Http\Requests\AttemptRequest;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class GradeAttemptRequest
 *
 * This class handles the validation for grading an attempt. It ensures that the provided `score`, `is_passed` status, 
 * `graded_at` date, and `graded_by` user ID are valid according to the specified rules. 
 * The `score` and `is_passed` fields are required, while `graded_at` and `graded_by` are optional, 
 * but if provided, they must meet the specific validation criteria.
 *
 * @package Modules\AssesmentModule\Http\Requests\AttemptRequest
 */
class GradeAttemptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * This method checks whether the user is authorized to grade an attempt. 
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
     * This method defines the validation rules for the `score`, `is_passed`, `graded_at`, 
     * and `graded_by` fields. It ensures that the `score` is a non-negative integer, 
     * the `is_passed` field is a boolean, and the `graded_at` date is valid if provided.
     * If the `graded_by` field is provided, it must be a valid user ID from the `users` table.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'score' => ['required', 'integer', 'min:0'], // Ensures score is a non-negative integer
            'is_passed' => ['required', 'boolean'], // Ensures is_passed is a boolean value
            'graded_at' => ['sometimes', 'date'], // Optional, but must be a valid date if provided
            'graded_by' => ['sometimes', 'integer', 'exists:users,id'], // Optional, but must be a valid user ID if provided
        ];
    }
}
