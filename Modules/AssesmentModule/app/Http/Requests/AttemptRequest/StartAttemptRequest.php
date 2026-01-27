<?php

namespace Modules\AssesmentModule\Http\Requests\AttemptRequest;

use Illuminate\Foundation\Http\FormRequest;

class StartAttemptRequest extends FormRequest
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
        'score' => ['sometimes','integer','min:0'],
        'is_passed' => ['sometimes','boolean'],

        ];
    }

    public function messages():array
    {
      return [
        ];
    }
}
