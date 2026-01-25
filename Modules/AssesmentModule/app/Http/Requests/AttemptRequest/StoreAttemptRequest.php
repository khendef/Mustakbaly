<?php

namespace Modules\AssesmentModule\Http\Requests\AttemptRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttemptRequest extends FormRequest
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
