<?php

namespace Modules\ReportingModule\Builders;

use Modules\LearningModule\Builders\CourseBuilder;

/**
 * CourseReportBuilder
 *
 * Purpose: Custom query builder for Course model in reporting context.
 * Extends CourseBuilder from LearningModule and adds reporting-specific filter methods.
 *
 * This builder provides scopes for filtering courses in reports:
 * - Filter by course type
 * - Filter by program
 * - Filter by published date range
 *
 * Usage:
 * Course::query()->applyReportFilters($filters)->get();
 */
class CourseReportBuilder extends CourseBuilder
{
    /**
     * Apply filters from an array for reporting purposes
     *
     * @param array $filters
     * @return self
     */
    public function applyReportFilters(array $filters): self
    {
        if (isset($filters['course_type_id'])) {
            $this->byCourseType($filters['course_type_id']);
        }

        if (isset($filters['program_id'])) {
            $this->byProgram($filters['program_id']);
        }

        if (isset($filters['date_from'])) {
            $this->publishedAfter($filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $this->publishedBefore($filters['date_to']);
        }

        return $this;
    }

    /**
     * Filter courses published after a specific date
     *
     * @param \DateTime|string $date
     * @return self
     */
    public function publishedAfter(\DateTime|string $date): self
    {
        return $this->where('published_at', '>=', $date);
    }

    /**
     * Filter courses published before a specific date
     *
     * @param \DateTime|string $date
     * @return self
     */
    public function publishedBefore(\DateTime|string $date): self
    {
        return $this->where('published_at', '<=', $date);
    }

    /**
     * Filter courses published within a date range
     *
     * @param \DateTime|string $from
     * @param \DateTime|string $to
     * @return self
     */
    public function publishedBetween(\DateTime|string $from, \DateTime|string $to): self
    {
        return $this->whereBetween('published_at', [$from, $to]);
    }
}
