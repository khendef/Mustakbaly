<?php

namespace Modules\ReportingModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
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

            return self::success(
                new LearnerPerformanceReportResource($report),
                'Performance report generated successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error generating performance report', [
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Failed to generate performance report.', 500);
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

            return self::success($report, 'Completion rates retrieved successfully.');
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving completion rates', [
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Failed to retrieve completion rates.', 500);
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

            return self::success($report, 'Learning time analysis retrieved successfully.');
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving learning time analysis', [
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Failed to retrieve learning time analysis.', 500);
        }
    }
}
