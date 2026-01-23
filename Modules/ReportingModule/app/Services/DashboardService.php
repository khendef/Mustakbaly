<?php

namespace Modules\ReportingModule\Services;

use App\Models\User;
use App\Traits\CachesQueries;
use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\Enrollment;
use Modules\LearningModule\Enums\EnrollmentStatus;
use Illuminate\Support\Facades\DB;

/**
 * Service for dashboard data aggregation
 * Handles business logic for learner, instructor, and admin dashboards
 */
class DashboardService
{
    use CachesQueries;
    /**
     * Get learner dashboard data
     *
     * @param int $learnerId
     * @return array
     */
    public function getLearnerDashboard(int $learnerId): array
    {
        $cacheKey = "learner_dashboard_{$learnerId}";

        return $this->remember($cacheKey, 300, function () use ($learnerId) {
            $enrollments = Enrollment::where('learner_id', $learnerId)
                ->with(['course', 'course.courseType'])
                ->get();

            $activeEnrollments = $enrollments->where('enrollment_status', EnrollmentStatus::ACTIVE);
            $completedEnrollments = $enrollments->where('enrollment_status', EnrollmentStatus::COMPLETED);

            $totalProgress = $enrollments->avg('progress_percentage') ?? 0;

            $recentCourses = $enrollments
                ->sortByDesc('enrolled_at')
                ->take(5)
                ->map(function ($enrollment) {
                    return [
                        'course_id' => $enrollment->course_id,
                        'title' => $enrollment->course->title,
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
                    'average_progress' => round($totalProgress, 2),
                ],
                'recent_courses' => $recentCourses,
                'progress_by_course' => $this->getLearnerProgressByCourse($learnerId),
            ];
        }, ['dashboards', "learner.{$learnerId}"]);
    }

    /**
     * Get instructor dashboard data
     *
     * @param int $instructorId
     * @return array
     */
    public function getInstructorDashboard(int $instructorId): array
    {
        $cacheKey = "instructor_dashboard_{$instructorId}";

        return $this->remember($cacheKey, 300, function () use ($instructorId) {
            $courses = Course::whereHas('instructors', function ($query) use ($instructorId) {
                $query->where('instructor_id', $instructorId);
            })
                ->with(['enrollments', 'instructors'])
                ->get();

            $totalStudents = 0;
            $courseStats = [];

            foreach ($courses as $course) {
                $enrollments = $course->enrollments;
                $totalStudents += $enrollments->count();

                $courseStats[] = [
                    'course_id' => $course->course_id,
                    'title' => $course->title,
                    'total_students' => $enrollments->count(),
                    'active_students' => $enrollments->where('enrollment_status', EnrollmentStatus::ACTIVE)->count(),
                    'completed_students' => $enrollments->where('enrollment_status', EnrollmentStatus::COMPLETED)->count(),
                    'average_progress' => round($enrollments->avg('progress_percentage') ?? 0, 2),
                ];
            }

            return [
                'summary' => [
                    'total_courses' => $courses->count(),
                    'total_students' => $totalStudents,
                    'pending_assignments' => 0, // Will be integrated with AssessmentModule later
                ],
                'course_statistics' => $courseStats,
                'top_performing_courses' => $this->getTopPerformingCourses($instructorId),
            ];
        }, ['dashboards', "instructor.{$instructorId}"]);
    }

    /**
     * Get admin dashboard data
     *
     * @return array
     */
    public function getAdminDashboard(): array
    {
        $cacheKey = "admin_dashboard";

        return $this->remember($cacheKey, 300, function () {
            $totalLearners = User::whereHas('enrollments')->count();
            $activeLearners = Enrollment::where('enrollment_status', EnrollmentStatus::ACTIVE)
                ->distinct('learner_id')
                ->count('learner_id');

            $totalCourses = Course::count();
            // $totalPrograms = program::count();

            // $coursesByProgram = Program::with('courses:course_id,program_id,title') // Load specific columns for efficiency
            //     ->withCount('courses')
            //     ->get()
            //     ->map(function ($program) {
            //         return [
            //             'program_id' => $program->program_id,
            //             'program_name' => $program->name,
            //             'courses_count' => $program->courses_count,
            //             'courses' => $program->courses->map(function ($course) {
            //                 return [
            //                     'id' => $course->course_id,
            //                     'title' => $course->title,
            //                 ];
            //             }),
            //         ];
            //     });

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

            $learningGaps = $this->identifyLearningGaps();

            return [
                'summary' => [
                    'total_learners' => $totalLearners,
                    'active_learners' => $activeLearners,
                    'total_courses' => $totalCourses,
                    // 'total_programs' => $totalprograms,
                    // 'coursesByProgram' => $coursesByProgram,
                    'published_courses' => $publishedCourses,
                    'total_enrollments' => $totalEnrollments,
                    'completion_rate' => $completionRate,
                ],
                'popular_courses' => $popularCourses,
                'learning_gaps' => $learningGaps
            ];
        }, ['dashboards', 'admin']);
    }

    /**
     * Get learner progress by course
     *
     * @param int $learnerId
     * @return array
     */
    private function getLearnerProgressByCourse(int $learnerId): array
    {
        return Enrollment::where('learner_id', $learnerId)
            ->with('course')
            ->get()
            // map the enrollments from the collection of model to an array of course_id, course_title, progress, and status
            ->map(function ($enrollment) {
                return [
                    'course_id' => $enrollment->course_id,
                    'course_title' => $enrollment->course->title,
                    'progress' => (float)$enrollment->progress_percentage,
                    'status' => $enrollment->enrollment_status->value,
                ];
            })
            ->toArray();
    }

    /**
     * Get top performing courses for instructor
     *
     * @param int $instructorId
     * @return array
     */
    private function getTopPerformingCourses(int $instructorId): array
    {
        return Course::whereHas('instructors', function ($query) use ($instructorId) {
            $query->where('instructor_id', $instructorId);
        })
            ->with(['enrollments' => function ($query) {
                $query->where('enrollment_status', EnrollmentStatus::COMPLETED);
            }])
            ->get()
            ->map(function ($course) {
                $avgProgress = $course->enrollments->avg('progress_percentage') ?? 0;
                return [
                    'course_id' => $course->course_id,
                    'title' => $course->title,
                    'average_progress' => round($avgProgress, 2),
                    'completion_count' => $course->enrollments->count(),
                ];
            })
            ->sortByDesc('average_progress')
            ->take(5)
            ->values()
            ->toArray();
    }

    /**
     * Identify learning gaps
     *
     * @return array
     */
    private function identifyLearningGaps(): array
    {
        // Courses with low completion rates
        $lowCompletionCourses = Course::withCount(['enrollments as completed_count' => function ($query) {
            $query->where('enrollment_status', EnrollmentStatus::COMPLETED);
        }])
            ->withCount('enrollments')
            ->havingRaw('enrollments_count > 0')
            ->get()
            ->filter(function ($course) {
                if ($course->enrollments_count == 0) {
                    return false;
                }
                $completionRate = ($course->completed_count / $course->enrollments_count) * 100;
                return $completionRate < 30;
            })
            ->take(5)
            ->map(function ($course) {
                $completionRate = $course->enrollments_count > 0
                    ? round(($course->completed_count / $course->enrollments_count) * 100, 2)
                    : 0;
                return [
                    'course_id' => $course->course_id,
                    'title' => $course->title,
                    'completion_rate' => $completionRate,
                    'total_enrollments' => $course->enrollments_count,
                ];
            })
            ->values();

        // Courses with low average progress
        $lowProgressCourses = Course::with('enrollments')
            ->get()
            ->map(function ($course) {
                $avgProgress = $course->enrollments->avg('progress_percentage') ?? 0;
                return [
                    'course_id' => $course->course_id,
                    'title' => $course->title,
                    'average_progress' => round($avgProgress, 2),
                ];
            })
            ->filter(function ($course) {
                return $course['average_progress'] < 30;
            })
            ->take(5)
            ->values();

        return [
            'low_completion_courses' => $lowCompletionCourses,
            'low_progress_courses' => $lowProgressCourses,
        ];
    }
}
