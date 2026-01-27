<?php

namespace Modules\ReportingModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\ReportingModule\Http\Requests\Dashboard\GetDashboardRequest;
use Modules\ReportingModule\Http\Resources\DashboardResource;
use Modules\ReportingModule\Services\DashboardService;

/**
 * Controller for Student Dashboard
 * Handles HTTP requests for student dashboard data
 */
class StudentDashboardController extends Controller
{
    /**
     * Dashboard service instance
     *
     * @var DashboardService
     */
    protected DashboardService $dashboardService;

    /**
     * Create a new controller instance
     *
     * @param DashboardService $dashboardService
     */
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get student dashboard data
     * GET /api/v1/dashboards/student
     *
     * @param GetDashboardRequest $request
     * @return JsonResponse
     */
    public function index(GetDashboardRequest $request): JsonResponse
    {
        try {
            $userId = $request->user()->user_id;
            $dashboard = $this->dashboardService->getLearnerDashboard($userId);

            return $this->success(
                new DashboardResource($dashboard),
                'Student dashboard retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to retrieve student dashboard.', 500, $e->getMessage());
        }
    }
}
