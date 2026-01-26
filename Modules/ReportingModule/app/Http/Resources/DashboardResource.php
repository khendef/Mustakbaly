<?php

namespace Modules\ReportingModule\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Dashboard API Resource
 * Transforms dashboard data into a consistent JSON structure
 */
class DashboardResource extends JsonResource
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
            'summary' => $this->resource['summary'] ?? [],
            'recent_courses' => $this->resource['recent_courses'] ?? [],
            'progress_by_course' => $this->resource['progress_by_course'] ?? [],
            'course_statistics' => $this->resource['course_statistics'] ?? [],
            'top_performing_courses' => $this->resource['top_performing_courses'] ?? [],
            'popular_courses' => $this->resource['popular_courses'] ?? [],
            'learning_gaps' => $this->resource['learning_gaps'] ?? [],
            'enrollment_trends' => $this->resource['enrollment_trends'] ?? [],
            'completion_trends' => $this->resource['completion_trends'] ?? [],
        ];
    }
}

