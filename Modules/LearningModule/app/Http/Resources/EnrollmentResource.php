<?php

namespace Modules\LearningModule\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Enrollment API Resource
 *
 * Transforms Enrollment model into a consistent JSON structure for API responses.
 * Provides a standardized format for enrollment data across all endpoints.
 *
 * Includes:
 * - Core enrollment information (id, status, type, progress)
 * - Timestamps (enrolled_at, completed_at)
 * - Relationships (learner, course, enrolled_by) - loaded conditionally
 * - Computed fields (duration_in_days, is_overdue)
 */
class EnrollmentResource extends JsonResource
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
            // Core enrollment information
            'id' => $this->enrollment_id,
            'learner_id' => $this->learner_id,
            'course_id' => $this->course_id,
            'enrollment_type' => $this->enrollment_type,
            'enrollment_status' => $this->enrollment_status?->value,

            // Progress and timing
            'progress_percentage' => (float)$this->progress_percentage,
            'enrolled_at' => $this->enrolled_at?->toDateTimeString(),
            'completed_at' => $this->completed_at?->toDateTimeString(),
            'enrolled_by' => $this->enrolled_by,

            // Timestamps
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),

            // Computed fields
            'enrollment_duration_days' => $this->getEnrollmentDurationDays(),
            'is_completed' => $this->enrollment_status?->value === 'completed',

            // Relationships (only included if loaded)
            'learner' => $this->whenLoaded('learner', function () {
                return [
                    'id' => $this->learner->user_id,
                    'name' => $this->learner->name,
                    'email' => $this->learner->email,
                ];
            }),

            'course' => $this->whenLoaded('course', function () {
                return [
                    'id' => $this->course->course_id,
                    'title' => $this->course->title,
                    'slug' => $this->course->slug,
                    'description' => $this->course->description,
                    'status' => $this->course->status?->value,
                    'actual_duration_hours' => $this->course->actual_duration_hours,
                    'min_score_to_pass' => $this->course->min_score_to_pass,
                ];
            }),

            'enrolled_by_user' => $this->whenLoaded('enrolledBy', function () {
                return [
                    'id' => $this->enrolledBy->user_id,
                    'name' => $this->enrolledBy->name,
                    'email' => $this->enrolledBy->email,
                ];
            }),
        ];
    }

    /**
     * Get additional metadata that should always be included.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0',
            ],
        ];
    }

    /**
     * Calculate enrollment duration in days
     *
     * @return int|null
     */
    private function getEnrollmentDurationDays(): ?int
    {
        if (!$this->enrolled_at) {
            return null;
        }

        $endDate = $this->completed_at ?? now();
        return $this->enrolled_at->diffInDays($endDate);
    }
}
