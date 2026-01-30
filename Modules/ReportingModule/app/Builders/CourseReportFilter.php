<?php

namespace Modules\ReportingModule\Builders;

use Modules\LearningModule\Builders\CourseBuilder;

/**
 * CourseReportFilter
 *
 * Helper class to apply report filters to Course queries.
 * Since Course model returns CourseBuilder, we use this helper to apply ReportingModule-specific filters.
 */
class CourseReportFilter
{
    /**
     * Apply report filters to a Course query builder
     *
     * @param CourseBuilder $query
     * @param array $filters
     * @return CourseBuilder
     */
    public static function apply(CourseBuilder $query, array $filters): CourseBuilder
    {
        if (isset($filters['course_type_id'])) {
            $query->byCourseType($filters['course_type_id']);
        }

        if (isset($filters['program_id'])) {
            $query->byProgram($filters['program_id']);
        }

        if (isset($filters['date_from'])) {
            $query->where('published_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('published_at', '<=', $filters['date_to']);
        }

        return $query;
    }
}
