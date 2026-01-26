<?php

namespace Modules\ReportingModule\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Donor Report API Resource
 * Transforms donor report data into a consistent JSON structure
 */
class DonorReportResource extends JsonResource
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
            'report_period' => $this->resource['report_period'] ?? [],
            'beneficiaries' => $this->resource['beneficiaries'] ?? [],
            'courses' => $this->resource['courses'] ?? [],
            'skills_acquired' => $this->resource['skills_acquired'] ?? [],
            'program_impact' => $this->resource['program_impact'] ?? [],
        ];
    }
}

