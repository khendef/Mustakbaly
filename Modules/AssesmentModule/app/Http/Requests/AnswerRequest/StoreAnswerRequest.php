<?php

namespace Modules\AssesmentModule\Http\Requests\AnswerRequest;

use Illuminate\Database\Query\Builder;
use Modules\AssesmentModule\Models\Attempt;
use Modules\AssesmentModule\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\AssesmentModule\Models\Answer;
use Modules\AssesmentModule\Models\QuestionOption;

class StoreAnswerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
     public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [
            'attempt_id' => ['required', 'integer', 'exists:attempts,id'],

            'question_id' => ['required', 'integer', 'exists:questions,id'],

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

    public function messages(): array
    {
        return [
            'attempt_id.required' => 'المحاولة مطلوبة',
            'attempt_id.exists'   => 'المحاولة غير موجودة',

            'question_id.required' => 'السؤال مطلوب',
            'question_id.exists'   => 'السؤال غير موجود',

            'selected_option_id.exists' => 'الخيار المحدد غير صحيح',
            'boolean_answer.boolean'    => 'اجابة النعم/لا غير صحيحة',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $attemptId  = $this->input('attempt_id');
            $questionId = $this->input('question_id');

            if (!$attemptId || !$questionId) {
                return;
            }

            $attempt = Attempt::query()->find($attemptId);
            if (!$attempt) return;

            if (($attempt->status ?? null) !== 'in_progress') {
                $v->errors()->add('attempt_id', 'لا يمكن إرسال إجابة لأن حالة المحاولة ليست In Progress.');
                return;
            }

            $question = Question::query()->find($questionId);
            if (!$question) return;

            // quiz match
            if (!empty($attempt->quiz_id) && !empty($question->quiz_id) && (int)$attempt->quiz_id !== (int)$question->quiz_id) {
                $v->errors()->add('question_id', 'هذا السؤال لا يتبع لنفس الاختبار (Quiz).');
                return;
            }
            $already = Answer::query()
                ->where('attempt_id', (int)$attemptId)
                ->where('question_id', (int)$questionId)
                ->exists();

            if ($already) {
                $v->errors()->add('question_id', 'لا يمكنك إرسال إجابتين لنفس السؤال ضمن نفس المحاولة');
                return;
            }

            $type     = $question->type;
            $selected = $this->input('selected_option_id');
            $text     = $this->input('answer_text');
            $bool     = $this->input('boolean_answer');

            if ($type === 'mcq') {
                if ($selected === null) {
                    $v->errors()->add('selected_option_id', 'selected_option_id is required for MCQ questions.');
                    return;
                }

                $belongs = QuestionOption::query()
                    ->where('id', (int)$selected)
                    ->where('question_id', (int)$questionId)
                    ->exists();

                if (!$belongs) {
                    $v->errors()->add('selected_option_id', 'هذا الخيار لا يتبع لهذا السؤال.');
                    return;
                }
            }

            if ($type === 'true_false') {
                if ($bool === null) {
                    $v->errors()->add('boolean_answer', 'boolean_answer is required for true_false questions.');
                    return;
                }
            }

            if ($type === 'text') {
                $hasText = is_array($text) && collect($text)->filter(fn($x) => is_string($x) && trim($x) !== '')->isNotEmpty();
                if (!$hasText) {
                    $v->errors()->add('answer_text', 'answer_text is required for text questions.');
                    return;
                }
            }
        });
    }
}


