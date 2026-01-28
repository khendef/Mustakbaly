<?php

namespace Modules\ReportingModule\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\Enrollment;
use Modules\OrganizationsModule\Models\Program;
use Modules\LearningModule\Enums\EnrollmentStatus;

/**
 * Service for Partner Organization Dashboard
 * Handles business logic for partner organization dashboard data
 */
class PartnerOrganizationDashboardService
{
    protected CourseAnalyticsService $courseAnalyticsService;
    protected LearnerAnalyticsService $learnerAnalyticsService;
    /**
     * Create a new service instance
     *
     * @param LearnerAnalyticsService $learnerAnalyticsService
     * @param CourseAnalyticsService $courseAnalyticsService
     */
    public function __construct(LearnerAnalyticsService $learnerAnalyticsService, CourseAnalyticsService $courseAnalyticsService)
    {
        $this->learnerAnalyticsService = $learnerAnalyticsService;
        $this->courseAnalyticsService = $courseAnalyticsService;
    }

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
     * Get program statistics
     *
     * @param \Illuminate\Support\Collection $programs
     * @param \Illuminate\Support\Collection $enrollments
     * @return array
     */
    private function getProgramStatistics($programs, $enrollments): array
    {
        return $programs->map(function ($program) use ($enrollments) {
            return [
                'program_id' => $program->id,
                'program_title' => $program->title,
                'total_enrollments' => $enrollments->where('program_id', $program->id)->count(),
                'completed_enrollments' => $enrollments->where('program_id', $program->id)->where('enrollment_status', EnrollmentStatus::COMPLETED)->count(),
            ];
        })->toArray();
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
     * Generate comprehensive donor report for a program
     *
     * @param array $filters
     * @return array
     */
    public function generateComprehensiveReport(array $filters): array
    {
        // query to get the enrollments with the learner, course, and course type
        $query = Enrollment::query()->whereHas('course', function ($q) use ($filters) {
            $q->where('program_id', $filters["program_id"]);
        });

        if (isset($filters['date_from'])) {
            $query->where('enrolled_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('enrolled_at', '<=', $filters['date_to']);
        }

        if (isset($filters['course_type_id'])) {
            $query->whereHas('course', function ($q) use ($filters) {
                $q->where('course_type_id', $filters['course_type_id']);
            });
        }

        $enrollments = $query->with(['learner', 'course', 'course.courseType'])->get();

        $totalBeneficiaries = $enrollments->pluck('learner_id')->unique()->count();
        $completedEnrollments = $enrollments->where('enrollment_status', EnrollmentStatus::COMPLETED);

        return [
            'report_period' => [
                'from' => $filters['date_from'] ?? null,
                'to' => $filters['date_to'] ?? null,
            ],
            'beneficiaries' => [
                'total_beneficiaries' => $totalBeneficiaries,
                'active_beneficiaries' => $enrollments->where('enrollment_status', EnrollmentStatus::ACTIVE)
                    ->pluck('learner_id')
                    ->unique()
                    ->count(),
                'completed_beneficiaries' => $completedEnrollments->pluck('learner_id')->unique()->count(),
            ],
            'courses' => [
                'total_courses' => $enrollments->pluck('course_id')->unique()->count(),
                'total_enrollments' => $enrollments->count(),
                'completed_enrollments' => $completedEnrollments->count(),
                'completion_rate' => $enrollments->count() > 0
                    ? round(($completedEnrollments->count() / $enrollments->count()) * 100, 2)
                    : 0,
                'courses_by_type' => $this->courseAnalyticsService->getPopularityByCourseType($enrollments),
            ],
            'skills_acquired' => $this->learnerAnalyticsService->getSkillsAcquired($completedEnrollments),
        ];
    }
}
