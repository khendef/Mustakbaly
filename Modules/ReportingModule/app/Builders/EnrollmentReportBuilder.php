<?php

namespace Modules\ReportingModule\Builders;

use Modules\LearningModule\Builders\EnrollmentBuilder;

/**
 * EnrollmentReportBuilder
 *
 * Purpose: Custom query builder for Enrollment model in reporting context.
 * Extends EnrollmentBuilder from LearningModule and adds reporting-specific filter methods.
 *
 * This builder provides scopes for filtering enrollments in reports:
 * - Filter by learner
 * - Filter by course
 * - Filter by enrollment
 * - Filter by enrollment date range
 * - Filter by course type (via relationship)
 *
 * Usage:
 * Enrollment::query()->applyReportFilters($filters)->get();
 */
class EnrollmentReportBuilder extends EnrollmentBuilder
{
    /**
     * Apply filters from an array for reporting purposes
     *
     * @param array $filters
     * @return self
     */
    public function applyReportFilters(array $filters): self
    {
        if (isset($filters['learner_id'])) {
            $this->byLearner($filters['learner_id']);
        }

        if (isset($filters['course_id'])) {
            $this->byCourse($filters['course_id']);
        }

        if (isset($filters['enrollment_id'])) {
            $this->where('enrollment_id', $filters['enrollment_id']);
        }

        if (isset($filters['date_from'])) {
            $this->enrolledAfter($filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $this->enrolledBefore($filters['date_to']);
        }

        if (isset($filters['course_type_id'])) {
            $this->byCourseType($filters['course_type_id']);
        }

        return $this;
    }

    /**
     * Filter enrollments by course type (via course relationship)
     *
     * @param int $courseTypeId
     * @return self
     */
    public function byCourseType(int $courseTypeId): self
    {
        return $this->whereHas('course', function ($query) use ($courseTypeId) {
            $query->where('course_type_id', $courseTypeId);
        });
    }

    /**
     * Filter enrollments by program (via course relationship)
     *
     * @param int $programId
     * @return self
     */
    public function byProgram(int $programId): self
    {
        return $this->whereHas('course', function ($query) use ($programId) {
            $query->where('program_id', $programId);
        });
    }
}
