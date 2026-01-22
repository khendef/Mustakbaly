<?php

namespace Modules\AssesmentModule\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Modules\AssesmentModule\Models\Attempt;
use Modules\AssesmentModule\Models\Question;
use Illuminate\Validation\Rule;

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

        $answer = $this->route('answer');
        $answerId  = is_object($answer) ? ($answer->id ?? null) : $answer;

        $attemptId = $this->input('attempt_id')
            ?? (is_object($answer) ? $answer->attempt_id : null);

        return [
            'attempt_id' => ['sometimes', 'integer', 'exists:attempts,id'],

            'question_id' => [
                'sometimes',
                'integer',
                'exists:questions,id',
                Rule::unique('answers', 'question_id')
                    ->where(fn ($q) => $q->where('attempt_id', $attemptId))
                    ->ignore($answerId),
            ],

            'selected_option' => ['sometimes', 'nullable', 'integer', 'exists:question_options,id'],
            'answer_text'     => ['sometimes', 'nullable', 'string'],
            'boolean_answer'  => ['sometimes', 'nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $answer = $this->route('answer');
            if (!$answer) return;
            $attemptId = $this->input('attempt_id') ?? $answer->attempt_id;
            $attempt = Attempt::query()->find($attemptId);

            if ($attempt && ($attempt->status ?? null) !== 'in_progress') {
                $v->errors()->add('attempt_id', 'لا يمكن تعديل الإجابة لأن حالة المحاولة ليست In Progress.');
                return;
            }
            $questionId = $this->input('question_id') ?? $answer->question_id;
            $question = Question::query()->find($questionId);
            if (!$question) return;

            $type = $question->type;

            $selected = $this->input('selected_option');
            $text     = $this->input('answer_text');
            $bool     = $this->input('boolean_answer');

            if ($type === 'mcq' && $selected === null && !$this->has('answer_text') && !$this->has('boolean_answer')) {
                $v->errors()->add('selected_option', 'selected_option is required for MCQ questions.');
            }

            if ($type === 'true_false' && $bool === null && !$this->has('selected_option') && !$this->has('answer_text')) {
                $v->errors()->add('boolean_answer', 'boolean_answer is required for true_false questions.');
            }

            if ($type === 'text' && ($text === null || trim((string) $text) === '') && !$this->has('selected_option') && !$this->has('boolean_answer')) {
                $v->errors()->add('answer_text', 'answer_text is required for text questions.');
            }
        });
    }
}
