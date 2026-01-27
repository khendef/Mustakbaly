<?php

namespace Modules\AssesmentModule\Http\Requests\QuizRequest;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuizRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
             'course_id' => [
                'required',
                'exists:courses,id',
            ],

            'instructor_id' => [
              'required',
              'exists:users,id'
            ],

            'quizable_id' => [
            'nullable',
            'integer'
            ],

            'quizable_type' => [
                 'nullable',
                 'string',
                 'max:255'
                 ],
            'type' => [
            'required',
            'in:quiz,assignment,practice'
                 ],
            'title' => [
            'required',
            'array'],

            'title.*' => [
            'required',
            'string',
            'max:255'
                ],
            'description.*' => [
              'nullable',
               'string'
            ],
             'description' => [
              'nullable',
               'array'
            ],

            'max_score' => [
              'required',
              'integer',
              'min:1',
            ],

            'passing_score' => [
             'required',
             'integer',
             'min:0'],

        'status' => [
            'required',
            'in:published,draft'
            ],
        'auto_grade_enabled' => [
            'required',
            'boolean'
            ],
        'available_from' => [
            'nullable',
            'date'
            ],
        'due_date' => [
            'nullable',
            'date',
            'after_or_equal:available_from'
            ],
        'duration_minutes' => ['nullable',
           'integer',
            'min:1'],
        ];
    }
    public function withValidator(Validator $validator): void
{
    $validator->after(function (Validator $v) {
        $maxScore = (int) $this->input('max_score' , 0);
        $passing  = (int) $this->input('passing_score',0);

        if ($passing > $maxScore){
            $v->errors()->add('passing_score','Passing score cannot be greater than max score');
        }

        $limit = (int) floor($maxScore * 0.60);

        if ($passing > $limit) {
            $v->errors()->add('passing_score', "Passing score must be <= {$limit} (60% of max_score)");
        }
    });
}


    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
