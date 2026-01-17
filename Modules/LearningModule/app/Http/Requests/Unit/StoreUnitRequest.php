<?php

namespace Modules\LearningModule\Http\Requests\Unit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for storing a new unit.
 * Handles validation for unit creation.
 */
class StoreUnitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,course_id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit_order' => ['nullable', 'integer', 'min:1'],
            'actual_duration_minutes' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'course_id.required' => 'The course is required.',
            'course_id.exists' => 'The selected course does not exist.',
            'title.required' => 'The unit title is required.',
            'title.max' => 'The unit title may not be greater than 255 characters.',
            'unit_order.min' => 'Unit order must be at least 1.',
            'actual_duration_minutes.required' => 'Actual duration is required.',
            'actual_duration_minutes.min' => 'Actual duration must be at least 1 minute.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'course_id' => 'course',
            'unit_order' => 'unit order',
            'actual_duration_minutes' => 'actual duration',
        ];
    }
}
