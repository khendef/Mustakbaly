<?php

namespace Modules\ReportingModule\Services;

use Modules\LearningModule\Models\Enrollment;
use Modules\LearningModule\Enums\EnrollmentStatus;
use Illuminate\Support\Facades\Cache;

/**
 * Service for Student Dashboard
 * Handles business logic for student dashboard data
 */
class StudentDashboardService
{
    protected LearnerAnalyticsService $learnerAnalyticsService;
    /**
     * Create a new service instance
     *
     * @param LearnerAnalyticsService $learnerAnalyticsService
     */
    public function __construct(LearnerAnalyticsService $learnerAnalyticsService)
    {
        $this->learnerAnalyticsService = $learnerAnalyticsService;
    }
    /**
     * Get student dashboard data
     *
     * @param int $learnerId
     * @return array
     */
    public function getStudentDashboard(int $learnerId): array
    {
        $cacheKey = "student_dashboard_{$learnerId}";

        return Cache::remember($cacheKey, 300, function () use ($learnerId) {
            $enrollments = Enrollment::where('learner_id', $learnerId)
                ->with(['course', 'course.courseType'])
                ->get();

            $activeEnrollments = $enrollments->where('enrollment_status', EnrollmentStatus::ACTIVE);
            $completedEnrollments = $enrollments->where('enrollment_status', EnrollmentStatus::COMPLETED);

            $avgProgress = $enrollments->avg('progress_percentage') ?? 0;

            $recentCourses = $enrollments
                ->sortByDesc('enrolled_at')
                ->take(5)
                ->map(function ($enrollment) {
                    return [
                        'course_id' => $enrollment->course_id,
                        'title' => $enrollment->course->title ?? 'Unknown',
                        'progress' => (float)$enrollment->progress_percentage,
                        'status' => $enrollment->enrollment_status->value,
                        'enrolled_at' => $enrollment->enrolled_at?->toDateTimeString(),
                    ];
                })
                ->values();

            return [
                'summary' => [
                    'total_courses' => $enrollments->count(),
                    'active_courses' => $activeEnrollments->count(),
                    'completed_courses' => $completedEnrollments->count(),
                    'average_progress' => round($avgProgress, 2),
                ],
                'recent_courses' => $recentCourses,
                'progress_by_course' => $this->learnerAnalyticsService->getLearnerProgressByCourse($enrollments),
            ];
        });
    }

    // /**
    //  * Get learner progress by course
    //  *
    //  * @param \Illuminate\Support\Collection $enrollments
    //  * @return array
    //  */
    // private function getLearnerProgressByCourse($enrollments): array
    // {
    //     return $enrollments->map(function ($enrollment) {
    //         return [
    //             'course_id' => $enrollment->course_id,
    //             'course_title' => $enrollment->course->title ?? 'Unknown',
    //             'progress' => (float)$enrollment->progress_percentage,
    //             'status' => $enrollment->enrollment_status->value,
    //         ];
    //     })->toArray();
    // }
}
