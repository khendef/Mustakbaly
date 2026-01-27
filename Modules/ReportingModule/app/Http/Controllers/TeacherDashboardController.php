<?php

namespace Modules\ReportingModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\ReportingModule\Http\Requests\Dashboard\GetDashboardRequest;
use Modules\ReportingModule\Http\Resources\DashboardResource;
use Modules\ReportingModule\Services\DashboardService;

/**
 * Controller for Teacher Dashboard
 * Handles HTTP requests for teacher dashboard data
 */
class TeacherDashboardController extends Controller
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
     * Get teacher dashboard data
     * GET /api/v1/dashboards/teacher
     *
     * @param GetDashboardRequest $request
     * @return JsonResponse
     */
    public function index(GetDashboardRequest $request): JsonResponse
    {
        try {
            $instructorId = $request->user()->user_id;
            $dashboard = $this->dashboardService->getInstructorDashboard($instructorId);

            return $this->success(
                new DashboardResource($dashboard),
                'Teacher dashboard retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to retrieve teacher dashboard.', 500, $e->getMessage());
        }
    }
}
