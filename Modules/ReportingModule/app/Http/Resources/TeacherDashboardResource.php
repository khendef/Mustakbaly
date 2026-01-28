<?php

namespace Modules\ReportingModule\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Teacher Dashboard API Resource
 * Transforms teacher dashboard data into a consistent JSON structure
 */
class TeacherDashboardResource extends JsonResource
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
            'course_statistics' => $this->resource['course_statistics'] ?? [],
            'top_performing_courses' => $this->resource['top_performing_courses'] ?? [],
        ];
    }
}
