<?php

namespace Modules\UserManagementModule\Http\Requests\Api\V1\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserFilterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'roles'=>'sometimes|array|min:1',
            'roles.*'=>'string|exists:roles,name',
            'term'=>'sometimes|string|max:100',
            'gender'=>['sometimes','string',Rule::in(['male','feemale'])],
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
