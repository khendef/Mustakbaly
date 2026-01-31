<?php

namespace Modules\ReportingModule\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Partner Organization Dashboard API Resource
 * Transforms partner organization dashboard data into a consistent JSON structure
 */
class PartnerOrganizationDashboardResource extends JsonResource
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
            'program_statistics' => $this->resource['program_statistics'] ?? [],
            'learner_statistics' => $this->resource['learner_statistics'] ?? [],
            'course_statistics' => $this->resource['course_statistics'] ?? [],
            'program_performance' => $this->resource['program_performance'] ?? [],
            'impact_metrics' => $this->resource['impact_metrics'] ?? [],
        ];
    }
}
