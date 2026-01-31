<?php

namespace Modules\ReportingModule\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Modules\ReportingModule\Services\AdminDashboardService;
use Modules\ReportingModule\Services\CourseAnalyticsService;
use Modules\ReportingModule\Services\LearnerAnalyticsService;
use Modules\ReportingModule\Http\Resources\AdminDashboardResource;
use Modules\ReportingModule\Http\Resources\CoursePopularityReportResource;
use Modules\ReportingModule\Http\Resources\LearnerPerformanceReportResource;
use Modules\ReportingModule\Http\Requests\Report\GenerateCourseReportRequest;
use Modules\ReportingModule\Http\Requests\Report\GenerateLearnerReportRequest;
use Modules\ReportingModule\Http\Requests\Report\GetCompletionRatesRequest;
use Modules\ReportingModule\Http\Requests\Report\GetLearningTimeAnalysisRequest;

/**
 * Controller for Admin Dashboard
 * Handles HTTP requests for admin dashboard data
 */
class AdminDashboardController extends Controller
{
    /**
     * Dashboard service instance
     *
     * @var AdminDashboardService
     */
    protected AdminDashboardService $dashboardService;
    protected CourseAnalyticsService $courseAnalyticsService;
    protected LearnerAnalyticsService $learnerAnalyticsService;
    /**
     * Create a new controller instance
     *
     * @param AdminDashboardService $dashboardService
     * @param CourseAnalyticsService $courseAnalyticsService
     * @param LearnerAnalyticsService $learnerAnalyticsService
     */
    public function __construct(AdminDashboardService $dashboardService, CourseAnalyticsService $courseAnalyticsService, LearnerAnalyticsService $learnerAnalyticsService)
    {
        $this->dashboardService = $dashboardService;
        $this->courseAnalyticsService = $courseAnalyticsService;
        $this->learnerAnalyticsService = $learnerAnalyticsService;
    }

    /**
     * Get admin dashboard data
     * GET /api/v1/dashboards/admin-dashboard
     * Returns comprehensive dashboard with all system reports
     *
     * @return JsonResponse
     */
    public function dashboard(): JsonResponse
    {
        try {
            $dashboard = $this->dashboardService->getAdminDashboard();

            return $this->success(
                new AdminDashboardResource($dashboard),
                'Admin dashboard retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to retrieve admin dashboard.', 500, $e->getMessage());
        }
    }

    // course analytics/reports//

    /**
     * Generate course popularity report
     * GET /api/v1/dashboards/admin-dashboard/generate-course-popularity-report
     *
     * @param GenerateCourseReportRequest $request
     * @return JsonResponse
     */
    public function generateCoursePopularityReport(GenerateCourseReportRequest $request): JsonResponse
    {
        try {
            $report = $this->courseAnalyticsService->generatePopularityReport($request->validated());
            return $this->success(
                new CoursePopularityReportResource($report),
                'Course popularity report generated successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to generate course popularity report.', 500, $e->getMessage());
        }
    }

    /**
     * Identify learning gaps
     * GET /api/v1/dashboards/admin-dashboard/identify-learning-gaps
     *
     * @return JsonResponse
     */
    public function identifyLearningGaps(): JsonResponse
    {
        try {
            $learningGapsReport = $this->courseAnalyticsService->identifyLearningGaps();
            return $this->success(
                $learningGapsReport,
                'Learning gaps identified successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to identify learning gaps.', 500, $e->getMessage());
        }
    }

    /**
     * Get content performance
     * GET /api/v1/dashboards/admin-dashboard/get-content-performance/{courseId}
     *
     * @param int $courseId
     * @return JsonResponse
     */
    public function getContentPerformance(int $courseId): JsonResponse
    {
        try {
            $contentPerformance = $this->courseAnalyticsService->getContentPerformance($courseId);
            return $this->success(
                $contentPerformance,
                'Content performance retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to get content performance.', 500, $e->getMessage());
        }
    }

    // learner analytics/reports

    /**
     * Generate learner performance report
     * GET /api/v1/dashboards/admin-dashboard/generate-learner-performance-report
     *
     * @param GenerateLearnerReportRequest $request
     * @return JsonResponse
     */
    public function generatePerformanceReport(GenerateLearnerReportRequest $request): JsonResponse
    {
        try {
            $performanceReport =    $this->learnerAnalyticsService->generatePerformanceReport($request->validated());
            return $this->success(
                new LearnerPerformanceReportResource($performanceReport),
                'Learner performance report generated successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to generate performance report.', 500, $e->getMessage());
        }
    }

    /**
     * Get completion rates
     * GET /api/v1/dashboards/admin-dashboard/get-completion-rates
     *
     * @param GetCompletionRatesRequest $request
     * @return JsonResponse
     */
    public function getCompletionRates(GetCompletionRatesRequest $request): JsonResponse
    {
        try {
            $completionRates = $this->learnerAnalyticsService->getCompletionRates($request->validated());
            return $this->success(
                $completionRates,
                'Completion rates retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to retrieve completion rates.', 500, $e->getMessage());
        }
    }

    /**
     * Get learning time analysis
     * GET /api/v1/dashboards/admin-dashboard/get-learning-time-analysis
     *
     * @param GetLearningTimeAnalysisRequest $request
     * @return JsonResponse
     */
    public function getLearningTimeAnalysis(GetLearningTimeAnalysisRequest $request): JsonResponse
    {
        try {
            $learningTimeAnalysis = $this->learnerAnalyticsService->getLearningTimeAnalysis($request->validated());
            return $this->success(
                $learningTimeAnalysis,
                'Learning time analysis retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to retrieve learning time analysis.', 500, $e->getMessage());
        }
    }
}
