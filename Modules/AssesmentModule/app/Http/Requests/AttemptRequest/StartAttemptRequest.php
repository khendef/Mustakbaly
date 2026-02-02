<?php

namespace Modules\AssesmentModule\Http\Requests\AttemptRequest;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StartAttemptRequest
 *
 * This class handles the validation of the request data when starting an attempt. 
 * It includes optional validation for the `score` and `is_passed` fields. 
 * These fields are validated if provided, ensuring the `score` is a non-negative integer 
 * and the `is_passed` field is a boolean value.
 * 
 * @package Modules\AssesmentModule\Http\Requests\AttemptRequest
 */
class StartAttemptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * This method checks if the user is authorized to start the attempt. 
     * By default, it returns `true`, meaning the request is always authorized.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * This method defines the validation rules for the `score` and `is_passed` fields. 
     * Both fields are optional, but if provided, the following rules apply:
     * - `score`: Must be an integer and non-negative.
     * - `is_passed`: Must be a boolean.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'score' => ['sometimes', 'integer', 'min:0'], // Ensures score is a non-negative integer, if provided
            'is_passed' => ['sometimes', 'boolean'], // Ensures is_passed is a boolean, if provided
        ];
    }

    /**
     * Get the custom validation error messages.
     *
     * This method returns an array of custom error messages. In this case, it returns 
     * an empty array as no custom messages are defined.
     *
     * @return array
     */
    public function messages(): array
    {
        return [];
    }
}
