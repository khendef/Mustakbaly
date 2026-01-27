<?php

namespace Modules\AssesmentModule\Http\Requests\AttemptRequest;

use Illuminate\Foundation\Http\FormRequest;

class GradeAttemptRequest extends FormRequest
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

            'score' => ['required', 'integer', 'min:0'],
            'is_passed' => ['required', 'boolean'],
            'graded_at' => ['sometimes', 'date'],
            'graded_by' => ['sometimes', 'integer', 'exists:users,id'],
        ];
    }
}

