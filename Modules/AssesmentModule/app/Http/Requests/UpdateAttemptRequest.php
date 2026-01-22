<?php

namespace Modules\AssesmentModule\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttemptRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
             'quiz_id' => ['sometimes', 'integer', 'exists:quizzes,id'],
            'student_id' => ['sometimes', 'integer', 'exists:users,id'],
            'attempt_number' => ['sometimes', 'integer', 'min:1'],

            'status' => ['sometimes', 'string', 'in:in_progress,submitted,graded'],
            'score' => ['sometimes', 'integer', 'min:0'],
            'is_passed' => ['sometimes', 'boolean'],

            'start_at' => ['sometimes', 'nullable', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date'],

            'submitted_at' =>['sometimes','nullable','date'],
            'graded_at' => ['sometimes', 'nullable', 'date'],
            'graded_by' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],

        ];
    }
}
