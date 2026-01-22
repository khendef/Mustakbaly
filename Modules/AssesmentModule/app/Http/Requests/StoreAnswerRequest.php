<?php

namespace Modules\AssesmentModule\Http\Requests;

use Modules\AssesmentModule\Models\Attempt;
use Modules\AssesmentModule\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'attempt_id' => [
                'required',
                'integer',
                'exists:attempts,id',
            ],

            'question_id' => [
                'required',
                'integer',
                'exists:questions,id',
            ],

            'selected_option' => [
                'nullable',
                'integer',
                'exists:question_options,id',
            ],

            'answer_text' => [
                'nullable',
                'string',
            ],

            'boolean_answer' => [
                'nullable',
                'boolean',
            ],

            'is_correct' => [
                'sometimes',
                'boolean',
            ],

            'question_score' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'graded_at' => [
                'sometimes',
                'date',
            ],

            'graded_by' => [
                'sometimes',
                'integer',
                'exists:users,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'attempt_id.required'      => 'المحاولة مطلوبة',
            'attempt_id.exists'        => 'المحاولة غير موجودة',
            'question_id.required'     => 'السؤال مطلوب',
            'question_id.exists'       => 'السؤال غير موجود',
            'question_id.unique'       => 'لا يمكنك إرسال إجابتين لنفس السؤال ضمن نفس المحاولة',
            'selected_option.exists'   => 'الخيار المحدد غير صحيح',
            'boolean_answer.boolean'   => 'اجابة النعم/لا غير صحيحة',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $attemptId = $this->input('attempt_id');
            $questionId = $this->input('question_id');

            if (!$attemptId || !$questionId) {
                return;
            }

            $attempt = Attempt::query()->find($attemptId);
            if (!$attempt) {
                return;
            }

            if (($attempt->status ?? null) !== 'in_progress') {
                $v->errors()->add('attempt_id', 'لا يمكن إرسال إجابة لأن حالة المحاولة ليست In Progress.');
                return;
            }

            $question = Question::query()->find($questionId);
            if (!$question) {
                return;
            }

            if (!empty($attempt->quiz_id) && !empty($question->quiz_id) && (int)$attempt->quiz_id !== (int)$question->quiz_id) {
                $v->errors()->add('question_id', 'هذا السؤال لا يتبع لنفس الاختبار (Quiz).');
                return;
            }

            $type = $question->type;

            $selected = $this->input('selected_option');
            $text     = $this->input('answer_text');
            $bool     = $this->input('boolean_answer');

            if ($type === 'mcq') {
                if ($selected === null) {
                    $v->errors()->add('selected_option', 'selected_option is required for MCQ questions.');
                }
            }

            if ($type === 'true_false') {
                if ($bool === null) {
                    $v->errors()->add('boolean_answer', 'boolean_answer is required for true_false questions.');
                }
            }

            if ($type === 'text') {
                if ($text === null || trim($text) === '') {
                    $v->errors()->add('answer_text', 'answer_text is required for text questions.');
                }
            }
        });
    }
}
