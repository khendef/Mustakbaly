<?php

namespace Modules\AssesmentModule\Http\Requests\QuestionOptionRequest;
use Modules\AssesmentModule\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreQuestionOptionRequest extends FormRequest
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
        $question = $this->route('question');
        if($question instanceof Question){
            $this->merge(['question_id' => $question->id]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $qid = $this->input('question_id');
        return [
            'question_id' => ['required','exists:questions,id'],
            'option_text' => ['required','array'],
            'option_text.*' => ['required','string',
             Rule::unique('question_options','option_text')
            ->where(fn ($q) => $q->where('question_id',$this->input('question_id'))),
            ],
            'is_correct' => ['required','boolean'],

        ];

    }
    public function message():array{
        return [
            'question_id.required'=>'السؤال مطلوب',
            'question.exists' => 'السؤال غير موجود',
            'question.required' => 'نص الخيار مطلوب',
            'option_text.unique' => 'هذا الخيار موجود مسبقا لنفس السؤال',
            'is_correct.required' => 'يرجى تحديد ان كان الخيار صحيحا'
        ];
    }
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
       $question = $this->route('question_id');
            if (!($question instanceof Question)){
                $qid = $this->input('question_id');
                $question = $qid ? Question::query()->find($qid) : null;
            }
            if(!$question) return;

            if ($question->type !== 'mcq') {
                $v->errors()->add('question_id', 'Options are allowed only for MCQ questions.');
            }
        });
    }
}





