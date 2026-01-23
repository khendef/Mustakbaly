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
    /**
     * Generate comprehensive donor report for a program
     *
     * @param array $filters
     * @return array
     */
    public function generateComprehensiveReport(int $program_id, array $filters): array
    {
        // $program = Program::find($program_id);
        // if (!$program) {
        //     throw new \Exception('Program not found');
        // }
        // query to get the enrollments with the learner, course, and course type
        $query = Enrollment::query()->whereHas('course', function ($q) use ($program_id) {
            $q->where('program_id', $program_id);
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
                'total_enrollments' => $enrollments->count(),
                'completed_enrollments' => $completedEnrollments->count(),
                'completion_rate' => $enrollments->count() > 0
                    ? round(($completedEnrollments->count() / $enrollments->count()) * 100, 2)
                    : 0,
                'courses_by_type' => $this->getCompletedCoursesByType($enrollments),
            ],
            'skills_acquired' => $this->getSkillsAcquired($completedEnrollments),
        ];
    }

    /**
     * Get report of courses completed by course type
     *
     * @param \Illuminate\Support\Collection $enrollments
     * @return array
     */
    private function getCompletedCoursesByType($enrollments): array
    {
        return $enrollments->groupBy(function ($enrollment) {
            return $enrollment->course->courseType->name ?? 'Unknown';
            // typeName is the name of the course type
            // typeEnrollments is the collection of enrollments for the course type
        })->map(function ($typeEnrollments, $typeName) {
            $completed = $typeEnrollments->where('enrollment_status', EnrollmentStatus::COMPLETED)->count();
            // return the course type name, total enrollments, completed enrollments, and completion rate
            return [
                'course_type' => $typeName,
                'total_enrollments' => $typeEnrollments->count(),
                'completed_enrollments' => $completed,
                'completion_rate' => $typeEnrollments->count() > 0
                    ? round(($completed / $typeEnrollments->count()) * 100, 2)
                    : 0,
            ];
        })->values()->toArray();
    }

    /**
     * Get skills acquired (based on completed courses)
     *
     * @param \Illuminate\Support\Collection $completedEnrollments
     * @return array
     */
    private function getSkillsAcquired($completedEnrollments): array
    {
        $coursesByType = $completedEnrollments->groupBy(function ($enrollment) {
            return $enrollment->course->courseType->name ?? 'Unknown';
        });

        return $coursesByType->map(function ($enrollments, $typeName) {
            return [
                'skill_category' => $typeName,
                'courses_completed' => $enrollments->pluck('course_id')->unique()->count(),
                'beneficiaries_count' => $enrollments->pluck('learner_id')->unique()->count(),
            ];
        })->values()->toArray();
    }
}
