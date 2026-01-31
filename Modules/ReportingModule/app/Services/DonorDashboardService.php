<?php

namespace Modules\ReportingModule\Services;

use Illuminate\Support\Facades\Cache;
use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\Enrollment;
use Modules\OrganizationsModule\Models\Program;
use Modules\LearningModule\Enums\EnrollmentStatus;
use Modules\OrganizationsModule\Models\DonorProgram;
use Modules\ReportingModule\Services\DonorReportService;

/**
 * Service for Donor Dashboard
 * Handles business logic for donor dashboard data
 */
class DonorDashboardService
{
    /**
     * Get donor dashboard data
     *
     * @param int $donorId
     * @return array
     */
    public function getDonorDashboard(int $donorId): array
    {
        $cacheKey = "donor_dashboard_{$donorId}";

        return Cache::remember($cacheKey, 300, function () use ($donorId) {

            $programs = Program::where('donor_id', $donorId)->get();

            $totalFundedPrograms = $programs->count();
            $totalFundedAmount = $programs->sum('total_funded_amount');

            $activePrograms = $programs->where('status', 'active')->count();
            $completedPrograms = $programs->where('status', 'completed')->count();

            return [
                'summary' => [
                    'total_funded_programs' => $totalFundedPrograms,
                    'total_funded_amount' => $totalFundedAmount,
                    'active_programs' => $activePrograms,
                    'completed_programs' => $completedPrograms,
                ],
            ];
        });
    }

    /**
     * Get recent donations/programs
     *
     * @param int $donorId
     * @return array
     */
    private function getRecentDonations(int $donorId): array
    {
        $donations = DonorProgram::where('donor_id', $donorId)->orderBy('created_at', 'desc')->get();
        return [
            'total_donations' => $donations->count(),
            'total_donated_amount' => $donations->sum('contribution_amount'),
            'recent_donations' => $donations->take(5),
        ];
    }
}
