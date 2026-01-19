<?php

namespace Modules\ReportingModule\Services;

use Modules\LearningModule\Models\Enrollment;
use Modules\LearningModule\Enums\EnrollmentStatus;

/**
 * Service for learner analytics and reporting
 * Handles business logic for learner performance
 * it gives you the total enrollments, completed enrollments, average progress, average completion time, and performance by course
 */
class LearnerAnalyticsService
{
    /**
     * Generate learner performance report
     *
     * @param array $filters
     * @return array
     */
    public function generatePerformanceReport(array $filters): array
    {
        $query = Enrollment::query();

        if (isset($filters['learner_id'])) {
            $query->where('learner_id', $filters['learner_id']);
        }

        if (isset($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('enrolled_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('enrolled_at', '<=', $filters['date_to']);
        }

        $enrollments = $query->with(['learner', 'course'])->get();

        return [
            'total_enrollments' => $enrollments->count(),
            'completed_enrollments' => $enrollments->where('enrollment_status', EnrollmentStatus::COMPLETED)->count(),
            'average_progress' => round($enrollments->avg('progress_percentage') ?? 0, 2),
            'average_completion_time_days' => $this->calculateAverageCompletionTime($enrollments),
            'performance_by_course' => $this->getPerformanceByCourse($enrollments),
        ];
    }

    /**
     * Get completion rates by filters date_from, date_to
     *
     * @param array $filters
     * @return array
     */
    public function getCompletionRates(array $filters): array
    {
        $query = Enrollment::query();

        if (isset($filters['date_from'])) {
            $query->where('enrolled_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('enrolled_at', '<=', $filters['date_to']);
        }

        $total = $query->count();
        $completed = (clone $query)->where('enrollment_status', EnrollmentStatus::COMPLETED)->count();

        return [
            'total_enrollments' => $total,
            'completed_enrollments' => $completed,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Get learning time analysis by filters learner_id, course_id, enrollment_id, date_from, date_to
     * return array with total enrollments, completed enrollments, average completion days, and total learning days
     * @param array $filters
     * @return array
     */
    public function getLearningTimeAnalysis(array $filters): array
    {
        $query = Enrollment::query();

        if (isset($filters['learner_id'])) {
            $query->where('learner_id', $filters['learner_id']);
        }

        if (isset($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }

        if (isset($filters['enrollment_id'])) {
            $query->where('enrollment_id', $filters['enrollment_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('enrolled_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('enrolled_at', '<=', $filters['date_to']);
        }

        $enrollments = $query->get();

        $completedEnrollments = $enrollments->where('enrollment_status', EnrollmentStatus::COMPLETED)
            ->whereNotNull('completed_at');

        $totalDays = $completedEnrollments->sum(function ($enrollment) {
            return $enrollment->enrolled_at->diffInDays($enrollment->completed_at);
        });

        $averageDays = $completedEnrollments->count() > 0
            ? round($totalDays / $completedEnrollments->count(), 2)
            : 0;

        return [
            'total_enrollments' => $enrollments->count(),
            'completed_enrollments' => $completedEnrollments->count(),
            'average_completion_days' => $averageDays,
            'total_learning_days' => $totalDays,
        ];
    }

    /**
     * Calculate average completion time
     * return float with average completion time in days
     * @param \Illuminate\Support\Collection $enrollments
     * @return float
     */
    private function calculateAverageCompletionTime($enrollments): float
    {
        $completed = $enrollments->where('enrollment_status', EnrollmentStatus::COMPLETED)
            ->whereNotNull('completed_at');

        if ($completed->isEmpty()) {
            return 0;
        }

        $totalDays = $completed->sum(function ($enrollment) {
            return $enrollment->enrolled_at->diffInDays($enrollment->completed_at);
        });

        return round($totalDays / $completed->count(), 2);
    }

    /**
     * Get performance by course
     * return array with course_id, course_title, total enrollments, average progress, and completion rate
     * @param \Illuminate\Support\Collection $enrollments
     * @return array
     */
    private function getPerformanceByCourse($enrollments): array
    {
        return $enrollments->groupBy('course_id')->map(function ($courseEnrollments) {
            $total = $courseEnrollments->count();
            $completed = $courseEnrollments->where('enrollment_status', EnrollmentStatus::COMPLETED)->count();

            return [
                'course_id' => $courseEnrollments->first()->course_id,
                'course_title' => $courseEnrollments->first()->course->title ?? 'Unknown',
                'total_enrollments' => $total,
                'average_progress' => round($courseEnrollments->avg('progress_percentage') ?? 0, 2),
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            ];
        })->values()->toArray();
    }
}
