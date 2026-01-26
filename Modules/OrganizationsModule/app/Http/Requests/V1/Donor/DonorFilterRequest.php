<?php
namespace Modules\OrganizationsModule\Http\Requests\V1\Donor;

use Illuminate\Foundation\Http\FormRequest;

class DonorFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'       => ['sometimes', 'integer', 'exists:users,id'],
            'name'          => ['sometimes', 'string'],
            'created_from'  => ['sometimes', 'date'],
            'created_to'    => ['sometimes', 'date'],
        ];
    }

    public function filters(): array
    {
        $filters = $this->validated();
        ksort($filters);

        return $filters;
    }
}
