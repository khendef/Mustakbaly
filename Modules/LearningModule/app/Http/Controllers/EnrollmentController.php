<?php

namespace Modules\LearningModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\LearningModule\Enums\EnrollmentStatus;
use Modules\LearningModule\Http\Requests\Enrollment\ChangeStatusEnrollmentRequest;
use Modules\LearningModule\Http\Requests\Enrollment\StoreEnrollmentRequest;
use Modules\LearningModule\Http\Requests\Enrollment\UpdateEnrollmentRequest;
use Modules\LearningModule\Http\Resources\EnrollmentCollection;
use Modules\LearningModule\Http\Resources\EnrollmentResource;
use Modules\LearningModule\Models\Enrollment;
use Modules\LearningModule\Services\EnrollmentService;

/**
 * Controller for managing enrollments.
 * Handles HTTP requests and delegates business logic to EnrollmentService.
 * Follows SOLID principles: Single Responsibility, Dependency Inversion.
 *
 * Endpoints:
 * - GET /enrollments - List all enrollments with filtering
 * - POST /enrollments - Create new enrollment
 * - GET /enrollments/{id} - Get specific enrollment
 * - PUT /enrollments/{id} - Update enrollment
 * - DELETE /enrollments/{id} - Delete enrollment
 * - PUT /enrollments/{id}/status - Change enrollment status
 */
class EnrollmentController extends Controller
{
    /**
     * Enrollment service instance.
     *
     * @var EnrollmentService
     */
    protected EnrollmentService $enrollmentService;

    /**
     * Create a new controller instance.
     *
     * @param EnrollmentService $enrollmentService
     */
    public function __construct(EnrollmentService $enrollmentService)
    {
        $this->enrollmentService = $enrollmentService;
    }

    /**
     * Display a listing of enrollments with filtering and pagination.
     *
     * Query Parameters:
     * - learner_id: Filter by learner ID
     * - course_id: Filter by course ID
     * - status: Filter by enrollment status
     * - type: Filter by enrollment type
     * - search: Search by learner or course name
     * - sort: Sort field (default: enrollment_id)
     * - direction: Sort direction (asc|desc)
     * - per_page: Items per page (default: 15)
     * - page: Page number (default: 1)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Enrollment::query();

            $enrollments = $query
                ->filterByRequest($request)
                ->withRelations()
                ->ordered($request)
                ->paginateFromRequest($request)
                ->through(fn($enrollment) => new EnrollmentResource($enrollment));

            return $this->successResponse(
                new EnrollmentCollection($enrollments),
                'Enrollments retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve enrollments.');
        }
    }

    /**
     * Store a newly created enrollment.
     *
     * Creates an enrollment relationship between a learner and course.
     * Validates that both learner and course exist, and course is available.
     *
     * @param StoreEnrollmentRequest $request
     * @return JsonResponse
     */
    public function store(StoreEnrollmentRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $enrollment = $this->enrollmentService->enroll(
                $validated['course_id'],
                $validated['learner_id'],
                $validated['enrollment_type'] ?? 'self',
                $validated['enrolled_by'] ?? null
            );

            $enrollment->load(['learner', 'course', 'enrolledBy']);

            return $this->createdResponse(
                new EnrollmentResource($enrollment),
                'Enrollment created successfully.'
            );
        } catch (Exception $e) {
            if ($e->getCode() === 404) {
                return $this->notFoundResponse($e->getMessage());
            } elseif ($e->getCode() === 422) {
                return $this->errorResponse($e->getMessage(), 422);
            }

            return $this->serverErrorResponse('Failed to create enrollment.');
        }
    }

    /**
     * Display the specified enrollment.
     *
     * @param Enrollment $enrollment
     * @return JsonResponse
     */
    public function show(Enrollment $enrollment): JsonResponse
    {
        try {
            $enrollment->load(['learner', 'course', 'enrolledBy']);

            return $this->successResponse(
                new EnrollmentResource($enrollment),
                'Enrollment retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve enrollment.');
        }
    }

    /**
     * Change the enrollment status.
     *
     * Updates enrollment status and related timestamps.
     * Handles completion date and progress percentage automatically.
     *
     * @param ChangeStatusEnrollmentRequest $request
     * @param Enrollment $enrollment
     * @return JsonResponse
     */
    public function updateStatus(ChangeStatusEnrollmentRequest $request, Enrollment $enrollment): JsonResponse
    {
        try {
            $status = EnrollmentStatus::from($request->validated()['status']);
            $enrollment = $this->enrollmentService->updateStatus($enrollment, $status);

            $enrollment->load(['learner', 'course', 'enrolledBy']);

            return $this->successResponse(
                new EnrollmentResource($enrollment),
                'Enrollment status updated successfully.'
            );
        } catch (Exception $e) {
            if ($e->getCode() === 422) {
                return $this->errorResponse($e->getMessage(), 422);
            }

            return $this->serverErrorResponse('Failed to update enrollment status.');
        }
    }


    /**
     * Get enrollment progress details.
     *
     * Returns detailed progress information including:
     * - Current progress percentage
     * - Completed units/lessons count
     * - Total units/lessons count
     * - Estimated completion time
     *
     * @param Enrollment $enrollment
     * @return JsonResponse
     */
    public function getProgress(Enrollment $enrollment): JsonResponse
    {
        try {
            $progress = $this->enrollmentService->getProgressDetails($enrollment);

            return $this->successResponse($progress, 'Progress details retrieved successfully.');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve progress details.');
        }
    }

    /**
     * Reactivate a suspended or dropped enrollment.
     *
     * Changes enrollment status back to active.
     *
     * @param Enrollment $enrollment
     * @return JsonResponse
     */
    public function reactivate(Enrollment $enrollment): JsonResponse
    {
        try {
            $enrollment = $this->enrollmentService->reactivate($enrollment);
            $enrollment->load(['learner', 'course', 'enrolledBy']);

            return $this->successResponse(
                new EnrollmentResource($enrollment),
                'Enrollment reactivated successfully.'
            );
        } catch (Exception $e) {
            if ($e->getCode() === 422) {
                return $this->errorResponse($e->getMessage(), 422);
            }

            return $this->serverErrorResponse('Failed to reactivate enrollment.');
        }
    }
}
