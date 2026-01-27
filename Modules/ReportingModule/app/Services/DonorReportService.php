<?php

namespace Modules\ReportingModule\Services;

use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\Enrollment;
use Modules\LearningModule\Enums\EnrollmentStatus;
use App\Models\User;

/**
 * Service for donor reports
 * Handles business logic for comprehensive donor reporting
 */
class DonorReportService
{
    protected CourseAnalyticsService $courseAnalyticsService;
    protected LearnerAnalyticsService $learnerAnalyticsService;
    /**
     * Create a new service instance
     *
     * @param LearnerAnalyticsService $learnerAnalyticsService
     */
    public function __construct(LearnerAnalyticsService $learnerAnalyticsService, CourseAnalyticsService $courseAnalyticsService)
    {
        $this->learnerAnalyticsService = $learnerAnalyticsService;
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
