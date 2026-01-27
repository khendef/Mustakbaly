<?php

namespace Modules\ReportingModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\ReportingModule\Http\Requests\Dashboard\GetDashboardRequest;
use Modules\ReportingModule\Http\Resources\DashboardResource;
use Modules\ReportingModule\Services\DashboardService;

/**
 * Controller for Manager/Partner Organization Dashboard
 * Handles HTTP requests for manager and partner organization dashboard data
 */
class ManagerPartnerOrganizationDashboardController extends Controller
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
     * Get manager/partner organization dashboard data
     * GET /api/v1/dashboards/manager-partner-organization
     *
     * @param GetDashboardRequest $request
     * @return JsonResponse
     */
    public function index(GetDashboardRequest $request): JsonResponse
    {
        try {
            $managerId = $request->user()->user_id;
            // TODO: Implement getManagerPartnerOrganizationDashboard method in DashboardService
            // For now, returning a placeholder response
            $dashboard = [
                'summary' => [
                    'total_programs' => 0,
                    'total_learners' => 0,
                    'total_courses' => 0,
                    'active_programs' => 0,
                ],
                'program_statistics' => [],
                'learner_statistics' => [],
                'course_statistics' => [],
            ];

            return $this->success(
                new DashboardResource($dashboard),
                'Manager/Partner Organization dashboard retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->error('Failed to retrieve manager/partner organization dashboard.', 500, $e->getMessage());
        }
    }
}
