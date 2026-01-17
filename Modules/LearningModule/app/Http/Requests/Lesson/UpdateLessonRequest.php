<?php

namespace Modules\LearningModule\Http\Requests\Lesson;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\LearningModule\Models\Lesson;

/**
 * Form request for updating an existing lesson.
 * Handles validation for lesson updates.
 */
class UpdateLessonRequest extends FormRequest
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
        $lessonId = $this->route('lesson');

        // Get lesson ID from route parameter (could be ID or model instance)
        $lessonId = $lessonId instanceof Lesson ? $lessonId->lesson_id : $lessonId;

        return [
            'unit_id' => ['sometimes', 'required', 'integer', 'exists:units,unit_id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'lesson_order' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'lesson_type' => ['sometimes', 'required', 'string', Rule::in(['lecture', 'video', 'interactive', 'reading'])],
            'is_required' => ['sometimes', 'nullable', 'boolean'],
            'actual_duration_minutes' => ['sometimes', 'required', 'integer', 'min:1'],
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
            'unit_id.required' => 'The unit is required.',
            'unit_id.exists' => 'The selected unit does not exist.',
            'title.required' => 'The lesson title is required.',
            'title.max' => 'The lesson title may not be greater than 255 characters.',
            'lesson_order.min' => 'Lesson order must be at least 1.',
            'lesson_type.required' => 'The lesson type is required.',
            'lesson_type.in' => 'Please select a valid lesson type (lecture, video, interactive, reading).',
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
            'unit_id' => 'unit',
            'lesson_order' => 'lesson order',
            'lesson_type' => 'lesson type',
            'is_required' => 'required',
            'actual_duration_minutes' => 'actual duration',
        ];
    }
}
