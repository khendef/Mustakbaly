<?php

namespace Modules\ReportingModule\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Admin Dashboard API Resource
 * Transforms admin dashboard data into a consistent JSON structure
 */
class AdminDashboardResource extends JsonResource
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
            'popular_courses' => $this->resource['popular_courses'] ?? [],
            'learning_gaps' => $this->resource['learning_gaps'] ?? [],
            'course_analytics' => [
                'popularity_report' => $this->resource['course_analytics']['popularity_report'] ?? [],
            ],
            'learner_analytics' => [
                'performance_report' => $this->resource['learner_analytics']['performance_report'] ?? [],
                'completion_rates' => $this->resource['learner_analytics']['completion_rates'] ?? [],
                'learning_time_analysis' => $this->resource['learner_analytics']['learning_time_analysis'] ?? [],
            ],
        ];
    }
}
