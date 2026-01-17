<?php

namespace Modules\LearningModule\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lesson API Resource
 *
 * Transforms Lesson model into a consistent JSON structure for API responses.
 * Provides a standardized format for lesson data across all endpoints.
 */
class LessonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->lesson_id,
            'unit_id' => $this->unit_id,
            'title' => $this->title,
            'description' => $this->description,
            'lesson_order' => $this->lesson_order,
            'lesson_type' => $this->lesson_type,
            'is_required' => $this->is_required,
            'actual_duration_minutes' => $this->actual_duration_minutes,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),

            // Relationships (only included if loaded)
            'unit' => $this->whenLoaded('unit', function () {
                return [
                    'id' => $this->unit->unit_id,
                    'title' => $this->unit->title,
                ];
            }),
        ];
    }
}
