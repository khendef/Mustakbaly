<?php

namespace Modules\AssesmentModule\Http\Requests\QuestionOptionRequest;

use Modules\AssesmentModule\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateQuestionOptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    /* protected function prepareForValidation()
    {
        $question = $this->route('question');
        if($question instanceof Question){
            $this->merge(['question_id' => $question->id]);
        }
    }*/
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $option = $this->route('question_option');
        $qid = $this->input('question_id') ?? optional($option)->question_id;
        return [
            'option_text.*' => [
                'sometimes',
                 'string',
            Rule::unique('question_options','option_text')->
            where(fn($q) => $q->where('question_id',$qid))
            ->ignore(optional($option)->id)
            ],
            'option_text' => [
                'sometimes',
                 'array',
            Rule::unique('question_options','option_text')->
            where(fn($q) => $q->where('question_id',$qid))
            ->ignore(optional($option)->id)
            ],

            'is_correct' => ['sometimes','boolean'],
        ];
    }

        public function withValidator(Validator $validator):void
    {
        $validator->after(function (Validator $validator1){
            $question = $this->route('question');
            if($question instanceof Question && $question->type !== 'mcq'){
                $validator1->errors()->add('question_id','Option are allowed only MCQ questions');
            }
        });
    }
}
