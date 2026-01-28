<?php

namespace Modules\ReportingModule\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Modules\ReportingModule\Services\DonorDashboardService;
use Modules\ReportingModule\Http\Resources\DonorDashboardResource;

/**
 * Controller for Donor Dashboard
 * Handles HTTP requests for donor dashboard data
 */
class DonorDashboardController extends Controller
{
    /**
     * Dashboard service instance
     *
     * @var DonorDashboardService
     */
    protected DonorDashboardService $dashboardService;

    /**
     * Create a new controller instance
     *
     * @param DonorDashboardService $dashboardService
     */
    public function __construct(DonorDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get donor dashboard data
     * GET /api/v1/dashboards/donor-dashboard/{donorId}
     *
     * @param int $donorId
     * @return JsonResponse
     */
    public function dashboard(int $donorId): JsonResponse
    {
        try {
            $dashboard = $this->dashboardService->getDonorDashboard($donorId);

            return $this->success(
                new DonorDashboardResource($dashboard),
                'Donor dashboard retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to retrieve donor dashboard.', 500, $e->getMessage());
        }
    }
}
