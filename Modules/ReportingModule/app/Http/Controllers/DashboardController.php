<?php

namespace Modules\ReportingModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\ReportingModule\Http\Requests\Dashboard\GetDashboardRequest;
use Modules\ReportingModule\Http\Resources\DashboardResource;
use Modules\ReportingModule\Services\DashboardService;

/**
 * Controller for dashboard data
 * Handles HTTP requests for learner, instructor, and admin dashboards
 */
class DashboardController extends Controller
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
     * Get learner dashboard data
     * GET /api/v1/dashboards/learner
     *
     * @param GetDashboardRequest $request
     * @return JsonResponse
     */
    public function learnerDashboard(GetDashboardRequest $request): JsonResponse
    {
        try {
            $userId = $request->user()->user_id;
            $dashboard = $this->dashboardService->getLearnerDashboard($userId);
            
            return $this->successResponse(
                new DashboardResource($dashboard),
                'Learner dashboard retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve learner dashboard.', null, null, $e);
        }
    }

    /**
     * Get instructor dashboard data
     * GET /api/v1/dashboards/instructor
     *
     * @param GetDashboardRequest $request
     * @return JsonResponse
     */
    public function instructorDashboard(GetDashboardRequest $request): JsonResponse
    {
        try {
            $instructorId = $request->user()->user_id;
            $dashboard = $this->dashboardService->getInstructorDashboard($instructorId);
            
            return $this->successResponse(
                new DashboardResource($dashboard),
                'Instructor dashboard retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve instructor dashboard.', null, null, $e);
        }
    }

    /**
     * Get admin dashboard data
     * GET /api/v1/dashboards/admin
     *
     * @param GetDashboardRequest $request
     * @return JsonResponse
     */
    public function adminDashboard(GetDashboardRequest $request): JsonResponse
    {
        try {
            $dashboard = $this->dashboardService->getAdminDashboard();
            
            return $this->successResponse(
                new DashboardResource($dashboard),
                'Admin dashboard retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve admin dashboard.', null, null, $e);
        }
    }
}

