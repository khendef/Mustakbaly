<?php

namespace Modules\ReportingModule\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Modules\ReportingModule\Services\TeacherDashboardService;
use Modules\ReportingModule\Http\Resources\TeacherDashboardResource;

/**
 * Controller for Teacher Dashboard
 * Handles HTTP requests for teacher dashboard data
 */
class TeacherDashboardController extends Controller
{
    /**
     * Dashboard service instance
     *
     * @var TeacherDashboardService
     */
    protected TeacherDashboardService $dashboardService;

    /**
     * Create a new controller instance
     *
     * @param TeacherDashboardService $dashboardService
     */
    public function __construct(TeacherDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get teacher dashboard data
     * GET /api/v1/dashboards/teacher-dashboard/{instructorId}
     *
     * @param int $instructorId
     * @return JsonResponse
     */
    public function dashboard(int $instructorId): JsonResponse
    {
        try {
            $dashboard = $this->dashboardService->getTeacherDashboard($instructorId);

            return $this->success(
                new TeacherDashboardResource($dashboard),
                'Teacher dashboard retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to retrieve teacher dashboard.', 500, $e->getMessage());
        }
    }
}
