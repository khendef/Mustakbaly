<?php

namespace Modules\ReportingModule\Services;

use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\Enrollment;
use Modules\LearningModule\Enums\EnrollmentStatus;

/**
 * Service for course analytics and reporting
 * Handles business logic for course popularity and content performance reports
 * it gives you the total courses, total enrollments, popular courses, and popularity by course type and content performance for a specific course
 */
class CourseAnalyticsService
{
    /**
     * Generate course popularity report
     *
     * @param array $filters
     * @return array
     */
    public function generatePopularityReport(array $filters): array
    {
        $query = Course::query();

        if (isset($filters['course_type_id'])) {
            $query->where('course_type_id', $filters['course_type_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('published_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('published_at', '<=', $filters['date_to']);
        }

        $courses = $query->withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->get();

        return [
            'total_courses' => $courses->count(),
            'total_enrollments' => $courses->sum('enrollments_count'),
            'popular_courses' => $courses->take(10)->map(function ($course) {
                return [
                    'course_id' => $course->course_id,
                    'title' => $course->title,
                    'enrollments_count' => $course->enrollments_count,
                    'average_rating' => (float)$course->average_rating,
                ];
            }),
            'popularity_by_course_type' => $this->getPopularityByCourseType($courses),
        ];
    }

    /**
     * Get content performance for a specific course
     *
     * @param int|null $courseId
     * @return array
     */
    public function getContentPerformance(?int $courseId): array
    {
        if (!$courseId) {
            return [
                'error' => 'Course ID is required',
            ];
        }

        $course = Course::with(['enrollments', 'units.lessons'])->find($courseId);

        if (!$course) {
            return [
                'error' => 'Course not found',
            ];
        }

        $enrollments = $course->enrollments;
        $totalEnrollments = $enrollments->count();
        $completedEnrollments = $enrollments->where('enrollment_status', EnrollmentStatus::COMPLETED)->count();

        return [
            'course_id' => $course->course_id,
            'course_title' => $course->title,
            'total_enrollments' => $totalEnrollments,
            'completed_enrollments' => $completedEnrollments,
            'completion_rate' => $totalEnrollments > 0
                ? round(($completedEnrollments / $totalEnrollments) * 100, 2)
                : 0,
            'average_progress' => round($enrollments->avg('progress_percentage') ?? 0, 2),
            'total_units' => $course->units->count(),
            'total_lessons' => $course->units->sum(function ($unit) {
                return $unit->lessons->count();
            }),
        ];
    }

    /**
     * Get course popularity by course type
     * return array with course_type_id, course_type_name, total courses, and total enrollments
     * @param \Illuminate\Support\Collection $courses
     * @return array
     */
    private function getPopularityByCourseType($courses): array
    {
        return $courses->groupBy('course_type_id')->map(function ($typeCourses) {
            return [
                'course_type_id' => $typeCourses->first()->course_type_id,
                'course_type_name' => $typeCourses->first()->courseType->name ?? 'Unknown',
                'total_courses' => $typeCourses->count(),
                'total_enrollments' => $typeCourses->sum('enrollments_count'),
            ];
        })->values()->toArray();
    }
}
