<?php

namespace Modules\AssesmentModule\Http\Requests\AnswerRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnswerRequest extends FormRequest
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
             'selected_option_id' => ['nullable', 'integer', 'exists:question_options,id'],
            'answer_text' => ['nullable', 'array'],
            'answer_text.*' => ['nullable', 'string'],
            'boolean_answer' => ['nullable', 'boolean'],

            'is_correct' => ['sometimes', 'boolean'],
            'question_score' => ['sometimes', 'integer', 'min:0'],
            'graded_at' => ['sometimes', 'date'],
            'graded_by' => ['sometimes', 'integer', 'exists:users,id'],
        ];
    }
}
