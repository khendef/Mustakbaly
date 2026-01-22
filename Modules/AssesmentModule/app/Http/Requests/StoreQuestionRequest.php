<?php

namespace Modules\AssesmentModule\Http\Requests;
use Modules\AssesmentModule\Models\Quiz;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestionRequest extends FormRequest
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
        $quizId = $this->input('quiz_id');

        return [
            'quiz_id' => ['required','exists:quizzes,id'],

            'type' => [
                'required',
                'in:mcq,true_false,text',
            ],
            'question_text' => [
                'required',
                'string',

            ],
            'point' => [
                'required',
                'integer',
                'min:1',
            ],

            'order_index' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('questions','order_index')
                ->where(fn ($q) => $q->where('quiz_id',$quizId)),
            ],

            'is_required' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'نوع السؤال مطلوب',
            'type.in' => 'نوع السؤال غير صالح',

            'question_text.required' => 'نص السؤال مطلوب',
            'question_text.min' => 'نص السؤال قصير جدًا',

            'point.required' => 'علامة السؤال مطلوبة',
            'point.min' => 'علامة السؤال لا يمكن أن تكون سالبة',

            'order_index.required' => 'ترتيب السؤال مطلوب',
            'order_index.unique' => 'يوجد سؤال بنفس الترتيب داخل هذا الاختبار',

            'is_required.boolean' => 'قيمة is_required غير صحيحة',
        ];

    }

}
