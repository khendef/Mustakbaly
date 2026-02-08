<?php

namespace Modules\LearningModule\Http\Requests\Enrollment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for updating an existing enrollment.
 * Handles validation for enrollment updates.
 *
 * Validates:
 * - Enrollment type is valid (if updating)
 * - Final grade can be updated by users with 'override-enrollment-final-grade' permission
 * 
 * Note: progress_percentage is calculated automatically and cannot be manually updated.
 * Final grade is calculated automatically but can be manually overridden by authorized users.
 */
class UpdateEnrollmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Authorization logic can be added here
        // For example: check if user is admin or is the learner themselves
        return true;
    }

    /**
     * Check if the authenticated user can override the final grade.
     *
     * @return bool
     */
    protected function canOverrideFinalGrade(): bool
    {
        return $this->user() && $this->user()->can('override-enrollment-final-grade');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            // Enrollment type can be updated
            'enrollment_type' => [
                'nullable',
                'string',
                Rule::in(['self', 'assigned']),
            ],
        ];

        // Users with override permission can manually update final_grade
        if ($this->canOverrideFinalGrade()) {
            $rules['final_grade'] = [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
                'decimal:0,2',
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [
            'enrollment_type.string' => 'The enrollment type must be a string.',
            'enrollment_type.in' => 'The enrollment type must be either "self" or "assigned".',
        ];

        // Add final_grade validation messages for users with override permission
        if ($this->canOverrideFinalGrade()) {
            $messages['final_grade.numeric'] = 'The final grade must be a number.';
            $messages['final_grade.min'] = 'The final grade cannot be less than 0.';
            $messages['final_grade.max'] = 'The final grade cannot be greater than 100.';
            $messages['final_grade.decimal'] = 'The final grade can have at most 2 decimal places.';
        }

        return $messages;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [
            'enrollment_type' => 'enrollment type',
        ];

        // Add final_grade attribute for users with override permission
        if ($this->canOverrideFinalGrade()) {
            $attributes['final_grade'] = 'final grade';
        }

        return $attributes;
    }


    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Convert final_grade to float if provided (users with override permission only)
        if ($this->canOverrideFinalGrade() && $this->has('final_grade')) {
            $this->merge(['final_grade' => (float)$this->final_grade]);
        }
    }

    /**
     * Get only the validated data from the request.
     * Filters out null values and empty fields.
     *
     * @return array
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Filter out null and empty values to only update provided fields
        return array_filter($validated, fn($value) => $value !== null);
    }
}
