<?php

namespace Modules\AssesmentModule\Http\Requests\AnswerRequest;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateAnswerRequest
 *
 * This class handles the validation logic for updating an existing answer. It ensures that the input data
 * meets the required criteria for fields such as `selected_option_id`, `answer_text`, `boolean_answer`,
 * and other fields that are part of the answer. It also allows for optional updates of fields like `is_correct`,
 * `question_score`, and `graded_at`.
 *
 * @package Modules\AssesmentModule\Http\Requests\AnswerRequest
 */
class UpdateAnswerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * This method returns `true`, allowing all users to update answers.
     *
     * @return bool Always returns `true` to allow the request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * The validation rules ensure that:
     * - `selected_option_id` must exist in the `question_options` table if provided.
     * - `answer_text` must be an array of strings if provided.
     * - `boolean_answer` must be a boolean if provided.
     * - Fields like `is_correct`, `question_score`, `graded_at`, and `graded_by` are optional but must meet
     *   certain criteria if provided.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string> The validation rules.
     */
    public function rules(): array
    {
        return [
            // The selected_option_id is optional, but if provided, it must be a valid integer and exist in the question_options table.
            'selected_option_id' => ['nullable', 'integer', 'exists:question_options,id'],

            // The answer_text is optional but must be an array, and each item should be a string.
            'answer_text' => ['nullable', 'array'],
            'answer_text.*' => ['nullable', 'string'],

            // The boolean_answer is optional, but if provided, it must be a boolean.
            'boolean_answer' => ['nullable', 'boolean'],

            // The is_correct field is optional but must be a boolean if provided.
            'is_correct' => ['sometimes', 'boolean'],

            // The question_score field is optional but must be an integer greater than or equal to 0 if provided.
            'question_score' => ['sometimes', 'integer', 'min:0'],

            // The graded_at field is optional but must be a valid date if provided.
            'graded_at' => ['sometimes', 'date'],

            // The graded_by field is optional but must be an integer and an existing user ID if provided.
            'graded_by' => ['sometimes', 'integer', 'exists:users,id'],
        ];
    }
}
