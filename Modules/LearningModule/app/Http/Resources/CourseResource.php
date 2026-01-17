<?php

namespace Modules\LearningModule\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Course API Resource
 *
 * Transforms Course model into a consistent JSON structure for API responses.
 * Provides a standardized format for course data across all endpoints.
 */
class CourseResource extends JsonResource
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
            'id' => $this->course_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'objectives' => $this->objectives,
            'prerequisites' => $this->prerequisites,
            'actual_duration_hours' => $this->actual_duration_hours,
            'language' => $this->language,
            'status' => $this->status,
            'min_score_to_pass' => $this->min_score_to_pass,
            'is_offline_available' => $this->is_offline_available,
            'course_delivery_type' => $this->course_delivery_type,
            'difficulty_level' => $this->difficulty_level,
            'average_rating' => $this->average_rating,
            'total_ratings' => $this->total_ratings,
            'published_at' => $this->published_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),

            // Relationships (only included if loaded)
            'course_type' => $this->whenLoaded('courseType', function () {
                return [
                    'id' => $this->courseType->course_type_id,
                    'name' => $this->courseType->name,
                    'slug' => $this->courseType->slug,
                    'description' => $this->courseType->description,
                    'is_active' => $this->courseType->is_active,
                ];
            }),

            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->user_id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),

            'instructors' => $this->whenLoaded('instructors', function () {
                return $this->instructors->map(function ($instructor) {
                    return [
                        'id' => $instructor->user_id,
                        'name' => $instructor->name,
                        'email' => $instructor->email,
                        'is_primary' => $instructor->pivot->is_primary ?? false,
                    ];
                });
            }),

            'units' => $this->whenLoaded('units', function () {
                return $this->units->map(function ($unit) {
                    return [
                        'id' => $unit->unit_id,
                        'title' => $unit->title,
                        'slug' => $unit->slug,
                        'unit_order' => $unit->unit_order,
                    ];
                });
            }),

            'enrollments_count' => $this->when(
                $this->relationLoaded('enrollments'),
                fn() => $this->enrollments->count()
            ),
        ];
    }
}
