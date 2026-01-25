<?php

namespace Modules\UserManagementModule\Http\Requests\Api\V1\Instructor;

use Illuminate\Foundation\Http\FormRequest;

class InstructorFilterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'term'=>'soetimes|string|max:100',
            'years'=>'sometimes|int',
            'organiztionId'=>'sometimes|int|exists:organizations,id'
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
