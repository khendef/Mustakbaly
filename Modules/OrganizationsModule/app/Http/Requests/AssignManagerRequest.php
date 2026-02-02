<?php

namespace Modules\OrganizationsModule\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AssignManagerRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id'  => 'sometimes|exists:users,id',
            'email'=>'required_without:user_id|email|unique:users,email',
            'name'=> 'required_without:user_id|string|max:255',
            'password'=>['required_without:user_id','string','confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->symbols()
                    ->letters()
                    ->numbers()
                ],
            'phone'=>'required_without:user_id|string|phone',
            'date_of_birth'=>'required_without:user_id|date',
            'gender'=>['required_without:user_id',Rule::in(['male','female'])],
            'address'=>'nullable|max:500',
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
