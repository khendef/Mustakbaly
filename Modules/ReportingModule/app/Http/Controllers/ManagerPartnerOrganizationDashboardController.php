<?php

namespace Modules\ReportingModule\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Modules\ReportingModule\Services\CourseAnalyticsService;
use Modules\ReportingModule\Services\PartnerOrganizationDashboardService;
use Modules\ReportingModule\Http\Requests\Report\GenerateCourseReportRequest;
use Modules\ReportingModule\Http\Resources\CoursePopularityReportResource;
use Modules\ReportingModule\Http\Resources\PartnerOrganizationDashboardResource;

/**
 * Controller for Manager/Partner Organization Dashboard
 * Handles HTTP requests for manager and partner organization dashboard data
 */
class ManagerPartnerOrganizationDashboardController extends Controller
{
    /**
     * Dashboard service instance
     *
     * @var PartnerOrganizationDashboardService
     */
    protected PartnerOrganizationDashboardService $partnerOrganizationDashboardService;
    protected CourseAnalyticsService $courseAnalyticsService;
    /**
     * Create a new controller instance
     *
     * @param PartnerOrganizationDashboardService $partnerOrganizationDashboardService
     * @param CourseAnalyticsService $courseAnalyticsService
     */
    public function __construct(PartnerOrganizationDashboardService $partnerOrganizationDashboardService, CourseAnalyticsService $courseAnalyticsService)
    {
        $this->partnerOrganizationDashboardService = $partnerOrganizationDashboardService;
        $this->courseAnalyticsService = $courseAnalyticsService;
    }

    /**
     * Get manager/partner organization dashboard data
     * GET /api/v1/dashboards/manager-partner-organization-dashboard/{organizationId}
     *
     * @param int $organizationId
     * @return JsonResponse
     */
    public function dashboard(int $organizationId): JsonResponse
    {
        try {
            $dashboard = $this->partnerOrganizationDashboardService->getPartnerOrganizationDashboard($organizationId);
            return $this->success(
                new PartnerOrganizationDashboardResource($dashboard),
                'Partner organization dashboard retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to retrieve partner organization dashboard.', 500, $e->getMessage());
        }
    }

    /**
     * Generate course popularity report
     * GET /api/v1/dashboards/donor-dashboard/{donorId}/generate-course-popularity-report
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
}
