<?php

namespace Modules\ReportingModule\Services;

use App\Models\User;
use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\Enrollment;
use Modules\LearningModule\Enums\EnrollmentStatus;
use Illuminate\Support\Facades\Cache;

/**
 * Service for Partner Organization Dashboard
 * Handles business logic for partner organization dashboard data
 */
class PartnerOrganizationDashboardService
{
    /**
     * Get partner organization dashboard data
     *
     * @param int $organizationId
     * @param array $filters Optional filters for reports
     * @return array
     */
    public function getPartnerOrganizationDashboard(int $organizationId): array
    {
        $cacheKey = "partner_org_dashboard_{$organizationId}_";

        return Cache::remember($cacheKey, 300, function () use ($organizationId) {

            $programs = Program::where('organization_id', $organizationId)->get();
            $enrollments = Enrollment::whereHas('course', function ($q) use ($programs) {
                $q->whereIn('program_id', $programs->pluck('id'));
            })->get();
            $totalLearners = $enrollments->pluck('learner_id')->unique()->count();
            $activeLearners = $enrollments->where('enrollment_status', EnrollmentStatus::ACTIVE)
                ->pluck('learner_id')
                ->unique()
                ->count();

            $totalCourses = $enrollments->pluck('course_id')->unique()->count();
            $completedEnrollments = $enrollments->where('enrollment_status', EnrollmentStatus::COMPLETED);

            return [
                'summary' => [
                    'total_programs' => $programs->count(),
                    'total_learners' => $totalLearners,
                    'active_learners' => $activeLearners,
                    'total_courses' => $totalCourses,
                    'active_programs' => $programs->where('status', 'active')->count(),
                ],
                'program_statistics' => $this->getProgramStatistics($programs, $enrollments),
                'learner_statistics' => $this->getLearnerStatistics($enrollments),
                'course_statistics' => $this->getCourseStatistics($enrollments),
                'program_performance' => $this->getProgramPerformance($programs, $enrollments),
                'impact_metrics' => $this->getImpactMetrics($enrollments, $completedEnrollments),
            ];
        });
    }

    /**
     * Get organization programs
     *
     * @param int $organizationId
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    private function getOrganizationPrograms(int $organizationId, array $filters)
    {
        // TODO: Implement when Program model is available
        // For now, return empty collection
        return collect([]);
    }

    /**
     * Get organization enrollments
     *
     * @param int $organizationId
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    private function getOrganizationEnrollments(int $organizationId, array $filters)
    {
        $query = Enrollment::query()->whereHas('course', function ($q) use ($organizationId) {
            // TODO: Update when organization relationship is available
            // $q->where('organization_id', $organizationId);
        });

        if (isset($filters['date_from'])) {
            $query->where('enrolled_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('enrolled_at', '<=', $filters['date_to']);
        }

        return $query->with(['course', 'learner'])->get();
    }

    /**
     * Get program statistics
     *
     * @param \Illuminate\Support\Collection $programs
     * @param \Illuminate\Support\Collection $enrollments
     * @return array
     */
    private function getProgramStatistics($programs, $enrollments): array
    {
        // TODO: Implement when Program model is available
        return [];
    }

    /**
     * Get learner statistics
     *
     * @param \Illuminate\Support\Collection $enrollments
     * @return array
     */
    private function getLearnerStatistics($enrollments): array
    {
        $total = $enrollments->pluck('learner_id')->unique()->count();
        $active = $enrollments->where('enrollment_status', EnrollmentStatus::ACTIVE)
            ->pluck('learner_id')
            ->unique()
            ->count();
        $completed = $enrollments->where('enrollment_status', EnrollmentStatus::COMPLETED)
            ->pluck('learner_id')
            ->unique()
            ->count();

        return [
            'total_learners' => $total,
            'active_learners' => $active,
            'completed_learners' => $completed,
            'average_progress' => round($enrollments->avg('progress_percentage') ?? 0, 2),
        ];
    }

    /**
     * Get course statistics
     *
     * @param \Illuminate\Support\Collection $enrollments
     * @return array
     */
    private function getCourseStatistics($enrollments): array
    {
        return $enrollments->groupBy('course_id')->map(function ($courseEnrollments) {
            $course = $courseEnrollments->first()->course;
            return [
                'course_id' => $course->course_id ?? null,
                'course_title' => $course->title ?? 'Unknown',
                'total_enrollments' => $courseEnrollments->count(),
                'completed_enrollments' => $courseEnrollments->where('enrollment_status', EnrollmentStatus::COMPLETED)->count(),
                'average_progress' => round($courseEnrollments->avg('progress_percentage') ?? 0, 2),
            ];
        })->values()->toArray();
    }

    /**
     * Get program performance metrics
     *
     * @param \Illuminate\Support\Collection $programs
     * @param \Illuminate\Support\Collection $enrollments
     * @return array
     */
    private function getProgramPerformance($programs, $enrollments): array
    {
        // TODO: Implement when Program model is available
        return [];
    }

    /**
     * Get impact metrics
     *
     * @param \Illuminate\Support\Collection $enrollments
     * @param \Illuminate\Support\Collection $completedEnrollments
     * @return array
     */
    private function getImpactMetrics($enrollments, $completedEnrollments): array
    {
        $total = $enrollments->count();
        $completed = $completedEnrollments->count();
        $completionRate = $total > 0 ? round(($completed / $total) * 100, 2) : 0;

        return [
            'total_enrollments' => $total,
            'completed_enrollments' => $completed,
            'completion_rate' => $completionRate,
            'total_learners_reached' => $enrollments->pluck('learner_id')->unique()->count(),
            'learners_completed' => $completedEnrollments->pluck('learner_id')->unique()->count(),
        ];
    }
}
