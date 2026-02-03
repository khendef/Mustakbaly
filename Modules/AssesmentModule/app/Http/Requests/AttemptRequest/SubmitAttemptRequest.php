<?php

namespace Modules\AssesmentModule\Http\Requests\AttemptRequest;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAttemptRequest extends FormRequest
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
            'status' => 'required|in:in_progress,submitted,graded',
            'submitted_at' => 'sometimes|date',
        ];
    }
}
