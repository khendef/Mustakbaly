<?php

namespace Modules\OrganizationsModule\Http\Requests\V1\Program;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'               => ['sometimes', 'string', 'max:255'],
            'description'         => ['nullable', 'string'],
            'objectives'          => ['nullable', 'string'],
            'status'              => ['sometimes', 'in:archived,completed,in_progress'],
            'required_budget'     => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
