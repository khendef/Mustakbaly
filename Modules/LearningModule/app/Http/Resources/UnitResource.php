<?php

namespace Modules\LearningModule\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Unit API Resource
 *
 * Transforms Unit model into a consistent JSON structure for API responses.
 * Provides a standardized format for unit data across all endpoints.
 */
class UnitResource extends JsonResource
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
            'id' => $this->unit_id,
            'course_id' => $this->course_id,
            'title' => $this->title,
            'description' => $this->description,
            'unit_order' => $this->unit_order,
            'actual_duration_minutes' => $this->actual_duration_minutes,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),

            // Relationships (only included if loaded)
            'course' => $this->whenLoaded('course', function () {
                return [
                    'id' => $this->course->course_id,
                    'title' => $this->course->title,
                    'slug' => $this->course->slug,
                ];
            }),

            'lessons' => $this->whenLoaded('lessons', function () {
                return $this->lessons->map(function ($lesson) {
                    return [
                        'id' => $lesson->lesson_id,
                        'title' => $lesson->title,
                        'lesson_order' => $lesson->lesson_order,
                    ];
                });
            }),

            'lessons_count' => $this->when(
                $this->relationLoaded('lessons'),
                fn() => $this->lessons->count()
            ),
        ];
    }
}
