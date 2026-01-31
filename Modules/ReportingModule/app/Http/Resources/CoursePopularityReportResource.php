<?php

namespace Modules\ReportingModule\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Course Popularity Report API Resource
 * Transforms course popularity report data into a consistent JSON structure
 */
class CoursePopularityReportResource extends JsonResource
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
            'total_courses' => $this->resource['total_courses'] ?? 0,
            'total_enrollments' => $this->resource['total_enrollments'] ?? 0,
            'popular_courses' => $this->resource['popular_courses'] ?? [],
            'popularity_by_course_type' => $this->resource['popularity_by_course_type'] ?? [],
        ];
    }
}

