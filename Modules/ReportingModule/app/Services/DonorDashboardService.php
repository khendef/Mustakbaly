<?php

namespace Modules\ReportingModule\Services;

use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\Enrollment;
use Modules\LearningModule\Enums\EnrollmentStatus;
use Modules\ReportingModule\Services\DonorReportService;
use Illuminate\Support\Facades\Cache;

/**
 * Service for Donor Dashboard
 * Handles business logic for donor dashboard data
 */
class DonorDashboardService
{
    /**
     * Donor report service instance
     *
     * @var DonorReportService
     */
    protected DonorReportService $donorReportService;

    /**
     * Create a new service instance
     *
     * @param DonorReportService $donorReportService
     */
    public function __construct(DonorReportService $donorReportService)
    {
        $this->donorReportService = $donorReportService;
    }

    /**
     * Get donor dashboard data
     *
     * @param int $donorId
     * @param array $filters Optional filters for reports
     * @return array
     */
    public function getDonorDashboard(int $donorId): array
    {
        $cacheKey = "donor_dashboard_{$donorId}";

        return Cache::remember($cacheKey, 300, function () use ($donorId) {
            // Eager load course relationship to avoid N+1 queries
            $enrollments = Enrollment::whereHas('course', function ($q) use ($donorId) {
                $q->where('donor_id', $donorId);
            })
            ->with('course')
            ->get();

            // Filter null values and get unique program IDs
            $totalFundedPrograms = $enrollments
                ->pluck('course.program_id')
                ->filter()
                ->unique()
                ->count();

            // Get unique learner IDs
            $totalBeneficiaries = $enrollments
                ->pluck('learner_id')
                ->unique()
                ->count();

            // Filter by status first, then pluck learner IDs for better performance
            $activeBeneficiaries = $enrollments
                ->where('enrollment_status', EnrollmentStatus::ACTIVE)
                ->pluck('learner_id')
                ->unique()
                ->count();

            $completedBeneficiaries = $enrollments
                ->where('enrollment_status', EnrollmentStatus::COMPLETED)
                ->pluck('learner_id')
                ->unique()
                ->count();

            return [
                'summary' => [
                    'total_funded_programs' => $totalFundedPrograms,
                    'total_beneficiaries' => $totalBeneficiaries,
                    'active_beneficiaries' => $activeBeneficiaries,
                    'completed_beneficiaries' => $completedBeneficiaries,
                ],
            ];
        });
    }

    /**
     * Get recent donations/programs
     *
     * @param int $donorId
     * @param array $filters
     * @return array
     */
    private function getRecentDonations(int $donorId, array $filters): array
    {
        // TODO: Implement when donation/program model is available
        // For now, return empty array
        return [];
    }

    /**
     * Get program statistics
     *
     * @param int $donorId
     * @param array $filters
     * @return array
     */
    private function getProgramStatistics(int $donorId, array $filters): array
    {
        $query = Enrollment::query()->whereHas('course', function ($q) use ($filters) {
            if (isset($filters['program_id'])) {
                $q->where('program_id', $filters['program_id']);
            }
        });

        if (isset($filters['date_from'])) {
            $query->where('enrolled_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('enrolled_at', '<=', $filters['date_to']);
        }

        $enrollments = $query->get();
        $programs = $enrollments->pluck('course.program_id')->filter()->unique()->count();

        return [
            'total_programs' => $programs,
            'total_enrollments' => $enrollments->count(),
            'completed_enrollments' => $enrollments->where('enrollment_status', EnrollmentStatus::COMPLETED)->count(),
        ];
    }

    /**
     * Calculate program impact metrics
     *
     * @param array $report
     * @return array
     */
    private function calculateProgramImpact(array $report): array
    {
        $totalBeneficiaries = $report['beneficiaries']['total_beneficiaries'] ?? 0;
        $completedBeneficiaries = $report['beneficiaries']['completed_beneficiaries'] ?? 0;
        $completionRate = $report['courses']['completion_rate'] ?? 0;

        return [
            'total_beneficiaries_reached' => $totalBeneficiaries,
            'beneficiaries_completed' => $completedBeneficiaries,
            'overall_completion_rate' => $completionRate,
            'skills_categories' => count($report['skills_acquired'] ?? []),
        ];
    }
}
