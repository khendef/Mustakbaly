<?php

namespace Modules\LearningModule\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CourseType API Resource
 *
 * Transforms CourseType model into a consistent JSON structure for API responses.
 * Provides a standardized format for course type data across all endpoints.
 */
class CourseTypeResource extends JsonResource
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
            'id' => $this->course_type_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'target_audience' => $this->target_audience,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),

            // Relationships (only included if loaded)
            'courses' => $this->whenLoaded('courses', function () {
                return $this->courses->map(function ($course) {
                    return [
                        'id' => $course->course_id,
                        'title' => $course->title,
                        'slug' => $course->slug,
                        'status' => $course->status,
                    ];
                });
            }),

            'courses_count' => $this->when(
                $this->relationLoaded('courses'),
                fn() => $this->courses->count()
            ),
        ];
    }
}
