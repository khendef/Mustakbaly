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

            'user_id'  => 'nullable|exists:users,id',
            'name'=> 'required_without:user_id|string|max:255',
            'email'=>'required_without:user_id|string',
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
