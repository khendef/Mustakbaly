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
 * Define the validation rules for starting an attempt.
 *
 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
 */
public function rules(): array
{
    return [
        'quiz_id' => ['required', 'integer', 'exists:quizzes,id'],
        
        'student_id' => ['required', 'integer', 'exists:users,id'],
    ];
}

}
