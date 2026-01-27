<?php

namespace Modules\ReportingModule\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Modules\ReportingModule\Services\AdminDashboardService;
use Modules\ReportingModule\Http\Resources\AdminDashboardResource;
use Modules\ReportingModule\Http\Requests\Dashboard\GetDashboardRequest;

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

    /**
     * Create a new controller instance
     *
     * @param AdminDashboardService $dashboardService
     */
    public function __construct(AdminDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get admin dashboard data
     * GET /api/v1/dashboards/admin-dashboard
     * Returns comprehensive dashboard with all system reports
     *
     * @param GetDashboardRequest $request
     * @return JsonResponse
     */
    public function index(GetDashboardRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $dashboard = $this->dashboardService->getAdminDashboard($filters);

            return $this->success(
                new AdminDashboardResource($dashboard),
                'Admin dashboard retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to retrieve admin dashboard.', 500, $e->getMessage());
        }
    }
}
