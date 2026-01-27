<?php

namespace Modules\ReportingModule\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Modules\ReportingModule\Services\DashboardService;
use Modules\ReportingModule\Services\DonorReportService;
use Modules\ReportingModule\Http\Resources\DashboardResource;
use Modules\ReportingModule\Http\Requests\Dashboard\GetDashboardRequest;

/**
 * Controller for Donor Dashboard
 * Handles HTTP requests for donor dashboard data
 */
class DonorDashboardController extends Controller
{
    /**
     * Dashboard service instance
     *
     * @var DonorReportService
     */
    protected DonorReportService $reportService;

    /**
     * Create a new controller instance
     *
     * @param DonorReportService $dashboardService
     */
    public function __construct(DonorReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Get donor dashboard data
     * GET /api/v1/dashboards/donor
     *
     * @param GetDashboardRequest $request
     * @return JsonResponse
     */
    public function index(GetDashboardRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $report = $this->reportService->generateComprehensiveReport($filters);

            return $this->success(
                new DonorDashboardResource($report),
                'Donor dashboard retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to retrieve donor dashboard.', 500, $e->getMessage());
        }
    }
}
