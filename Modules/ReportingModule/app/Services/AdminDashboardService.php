<?php

namespace Modules\ReportingModule\Services;

use App\Models\User;
use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\Enrollment;
use Modules\LearningModule\Enums\EnrollmentStatus;
use Modules\ReportingModule\Services\CourseAnalyticsService;
use Modules\ReportingModule\Services\LearnerAnalyticsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Service for dashboard data aggregation
 * Handles business logic for learner, instructor, and admin dashboards
 */
class AdminDashboardService
{
    /**
     * Course analytics service instance
     *
     * @var CourseAnalyticsService
     */
    protected CourseAnalyticsService $courseAnalyticsService;

    /**
     * Learner analytics service instance
     *
     * @var LearnerAnalyticsService
     */
    protected LearnerAnalyticsService $learnerAnalyticsService;

    /**
     * Create a new service instance
     *
     * @param CourseAnalyticsService $courseAnalyticsService
     * @param LearnerAnalyticsService $learnerAnalyticsService
     */
    public function __construct(
        CourseAnalyticsService $courseAnalyticsService,
        LearnerAnalyticsService $learnerAnalyticsService
    ) {
        $this->courseAnalyticsService = $courseAnalyticsService;
        $this->learnerAnalyticsService = $learnerAnalyticsService;
    }

    /**
     * Get admin dashboard data
     * Returns comprehensive dashboard with all system reports
     *
     * @return array
     */
    public function getAdminDashboard(): array
    {
        $cacheKey = "admin_dashboard";

        return Cache::remember($cacheKey, 300, function () {
            // Base dashboard summary
            $totalLearners = User::whereHas('enrollments')->count();
            $activeLearners = Enrollment::where('enrollment_status', EnrollmentStatus::ACTIVE)
                ->distinct('learner_id')
                ->count('learner_id');

            $totalCourses = Course::count();
            $publishedCourses = Course::whereNotNull('published_at')->count();

            $totalEnrollments = Enrollment::count();
            $completedEnrollments = Enrollment::where('enrollment_status', EnrollmentStatus::COMPLETED)->count();
            $completionRate = $totalEnrollments > 0
                ? round(($completedEnrollments / $totalEnrollments) * 100, 2)
                : 0;

            $popularCourses = Course::withCount('enrollments')
                ->orderBy('enrollments_count', 'desc')
                ->take(10)
                ->get()
                ->map(function ($course) {
                    return [
                        'course_id' => $course->course_id,
                        'title' => $course->title,
                        'enrollments_count' => $course->enrollments_count,
                    ];
                });

            return [
                'summary' => [
                    'total_learners' => $totalLearners,
                    'active_learners' => $activeLearners,
                    'total_courses' => $totalCourses,
                    'published_courses' => $publishedCourses,
                    'total_enrollments' => $totalEnrollments,
                    'completion_rate' => $completionRate,
                    'popular_courses' => $popularCourses,
                ],
            ];
        });
    }
}
