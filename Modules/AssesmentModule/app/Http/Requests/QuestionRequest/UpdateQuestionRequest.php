<?php

namespace Modules\AssesmentModule\Http\Requests\QuestionRequest;

use Modules\AssesmentModule\Models\Quiz;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $quiz = $this->route('quiz');
        if($quiz instanceof Quiz) {
            $this->merge(['quiz_id' => $quiz->id]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $question = $this->route('question');
        $quizId = (int) ($this->input('quiz_id') ?? $question->quiz_id);
        return [
           'type' => [
                'sometimes',
                'in:mcq,true_false,text',
            ],

            'question_text' => [
                'sometimes',
                'array',

            ],
            'question_text.*' => [
                'sometimes',
                'string',

            ],

            'point' => [
                'sometimes',
                'integer',
                'min:1',
            ],

            'order_index' => [
                'sometimes',
                'integer',
                'min:1',
                Rule::unique('questions','order_index')
                ->where(fn($q) => $q->where('quiz_id', $quizId))
                ->ignore($question->id)
            ],

            'is_required' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
