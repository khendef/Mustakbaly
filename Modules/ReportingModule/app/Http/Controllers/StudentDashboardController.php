<?php

namespace Modules\ReportingModule\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Modules\ReportingModule\Http\Resources\DashboardResource;
use Modules\ReportingModule\Services\StudentDashboardService;
use Modules\ReportingModule\Http\Resources\StudentDashboardResource;
use Modules\ReportingModule\Http\Requests\Dashboard\GetDashboardRequest;

/**
 * Controller for Student Dashboard
 * Handles HTTP requests for student dashboard data
 */
class StudentDashboardController extends Controller
{
    /**
     * Dashboard service instance
     *
     * @var StudentDashboardService
     */
    protected StudentDashboardService $dashboardService;

    /**
     * Create a new controller instance
     *
     * @param StudentDashboardService $dashboardService
     */
    public function __construct(StudentDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get student dashboard data
     * GET /api/v1/dashboards/student-dashboard/{learnerId}
     *
     * @param int $learnerId
     * @return JsonResponse
     */
    public function dashboard(int $learnerId): JsonResponse
    {
        try {
            $dashboard = $this->dashboardService->getStudentDashboard($learnerId);

            return $this->success(
                new StudentDashboardResource($dashboard),
                'Student dashboard retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to retrieve student dashboard.', 500, $e->getMessage());
        }
    }
}
