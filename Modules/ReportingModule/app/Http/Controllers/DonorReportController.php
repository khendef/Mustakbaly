<?php

namespace Modules\ReportingModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\ReportingModule\Http\Requests\Report\GenerateDonorReportRequest;
use Modules\ReportingModule\Http\Resources\DonorReportResource;
use Modules\ReportingModule\Services\DonorReportService;

/**
 * Controller for donor reports
 * Handles HTTP requests for comprehensive donor reports
 */
class DonorReportController extends Controller
{
    /**
     * Donor report service instance
     *
     * @var DonorReportService
     */
    protected DonorReportService $reportService;

    /**
     * Create a new controller instance
     *
     * @param DonorReportService $reportService
     */
    public function __construct(DonorReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Generate comprehensive donor report
     * GET /api/v1/reports/donors/comprehensive
     *
     * @param GenerateDonorReportRequest $request
     * @return JsonResponse
     */
    public function comprehensiveReport(GenerateDonorReportRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $report = $this->reportService->generateComprehensiveReport($filters);
            
            return $this->successResponse(
                new DonorReportResource($report),
                'Donor report generated successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to generate donor report.', null, null, $e);
        }
    }
}

