<?php

namespace Modules\ReportingModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
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
            $userId = $request->user()->id;
            $dashboard = $this->dashboardService->getLearnerDashboard($userId);

            return self::success(
                new DashboardResource($dashboard),
                'Learner dashboard retrieved successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving learner dashboard', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Failed to retrieve learner dashboard.', 500);
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
            $instructorId = $request->user()->id;
            $dashboard = $this->dashboardService->getInstructorDashboard($instructorId);

            return self::success(
                new DashboardResource($dashboard),
                'Instructor dashboard retrieved successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving instructor dashboard', [
                'instructor_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Failed to retrieve instructor dashboard.', 500);
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

            return self::success(
                new DashboardResource($dashboard),
                'Admin dashboard retrieved successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving admin dashboard', [
                'user_id' => $request->user()->id ?? null,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Failed to retrieve admin dashboard.', 500);
        }
    }
}
