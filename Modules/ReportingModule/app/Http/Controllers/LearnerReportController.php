<?php

namespace Modules\ReportingModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\ReportingModule\Http\Requests\Report\GenerateLearnerReportRequest;
use Modules\ReportingModule\Http\Resources\LearnerPerformanceReportResource;
use Modules\ReportingModule\Services\LearnerAnalyticsService;

/**
 * Controller for learner reports
 * Handles HTTP requests for learner performance reports
 */
class LearnerReportController extends Controller
{
    /**
     * Learner analytics service instance
     *
     * @var LearnerAnalyticsService
     */
    protected LearnerAnalyticsService $analyticsService;

    /**
     * Create a new controller instance
     *
     * @param LearnerAnalyticsService $analyticsService
     */
    public function __construct(LearnerAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Get learner performance report
     * GET /api/v1/reports/learners/performance
     *
     * @param GenerateLearnerReportRequest $request
     * @return JsonResponse
     */
    public function performanceReport(GenerateLearnerReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $report = $this->analyticsService->generatePerformanceReport($filters);
            
            return $this->successResponse(
                new LearnerPerformanceReportResource($report),
                'Performance report generated successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to generate performance report.', null, null, $e);
        }
    }

    /**
     * Get learner completion rates
     * GET /api/v1/reports/learners/completion-rates
     *
     * @param GenerateLearnerReportRequest $request
     * @return JsonResponse
     */
    public function completionRates(GenerateLearnerReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $report = $this->analyticsService->getCompletionRates($filters);
            
            return $this->successResponse($report, 'Completion rates retrieved successfully.');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve completion rates.', null, null, $e);
        }
    }

    /**
     * Get learning time analysis
     * GET /api/v1/reports/learners/learning-time
     *
     * @param GenerateLearnerReportRequest $request
     * @return JsonResponse
     */
    public function learningTime(GenerateLearnerReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $report = $this->analyticsService->getLearningTimeAnalysis($filters);
            
            return $this->successResponse($report, 'Learning time analysis retrieved successfully.');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve learning time analysis.', null, null, $e);
        }
    }
}

