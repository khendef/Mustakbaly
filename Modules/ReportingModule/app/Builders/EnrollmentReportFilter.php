<?php

namespace Modules\ReportingModule\Builders;

use Modules\LearningModule\Builders\EnrollmentBuilder;

/**
 * EnrollmentReportFilter
 *
 * Helper class to apply report filters to Enrollment queries.
 * Since Enrollment model returns EnrollmentBuilder, we use this helper to apply ReportingModule-specific filters.
 */
class EnrollmentReportFilter
{
    /**
     * Apply report filters to an Enrollment query builder
     *
     * @param EnrollmentBuilder $query
     * @param array $filters
     * @return EnrollmentBuilder
     */
    public static function apply(EnrollmentBuilder $query, array $filters): EnrollmentBuilder
    {
        if (isset($filters['learner_id'])) {
            $query->byLearner($filters['learner_id']);
        }

        if (isset($filters['course_id'])) {
            $query->byCourse($filters['course_id']);
        }

        if (isset($filters['enrollment_id'])) {
            $query->where('enrollment_id', $filters['enrollment_id']);
        }

        if (isset($filters['date_from'])) {
            $query->enrolledAfter($filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->enrolledBefore($filters['date_to']);
        }

        if (isset($filters['course_type_id'])) {
            $query->whereHas('course', function ($q) use ($filters) {
                $q->where('course_type_id', $filters['course_type_id']);
            });
        }

        return $query;
    }
}
