<?php

namespace Modules\ReportingModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Modules\ReportingModule\Http\Requests\Report\GenerateCourseReportRequest;
use Modules\ReportingModule\Http\Resources\CoursePopularityReportResource;
use Modules\ReportingModule\Services\CourseAnalyticsService;

/**
 * Controller for course reports
 * Handles HTTP requests for course popularity and content performance reports
 */
class CourseReportController extends Controller
{
    /**
     * Course analytics service instance
     *
     * @var CourseAnalyticsService
     */
    protected CourseAnalyticsService $analyticsService;

    /**
     * Create a new controller instance
     *
     * @param CourseAnalyticsService $analyticsService
     */
    public function __construct(CourseAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Get course popularity report
     * GET /api/v1/reports/courses/popularity
     *
     * @param GenerateCourseReportRequest $request
     * @return JsonResponse
     */
    public function popularityReport(GenerateCourseReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $report = $this->analyticsService->generatePopularityReport($filters);

            return self::success(
                new CoursePopularityReportResource($report),
                'Popularity report generated successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error generating popularity report', [
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Failed to generate popularity report.', 500);
        }
    }

    /**
     * Get course content performance
     * GET /api/v1/reports/courses/content-performance
     *
     * @param GenerateCourseReportRequest $request
     * @return JsonResponse
     */
    public function contentPerformance(GenerateCourseReportRequest $request): JsonResponse
    {
        try {
            $courseId = $request->input('course_id');
            $report = $this->analyticsService->getContentPerformance($courseId);

            if (isset($report['error'])) {
                throw new Exception($report['error'], 404);
            }

            return self::success($report, 'Content performance retrieved successfully.');
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving content performance', [
                'course_id' => $courseId ?? null,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Failed to retrieve content performance.', 500);
        }
    }
}
