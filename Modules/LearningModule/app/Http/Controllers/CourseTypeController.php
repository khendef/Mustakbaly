<?php

namespace Modules\LearningModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\LearningModule\Http\Requests\CourseType\StoreCourseTypeRequest;
use Modules\LearningModule\Http\Requests\CourseType\UpdateCourseTypeRequest;
use Modules\LearningModule\Http\Resources\CourseTypeResource;
use Modules\LearningModule\Models\CourseType;
use Modules\LearningModule\Services\CourseTypeService;

/**
 * Controller for managing course types.
 * Handles HTTP requests and delegates business logic to CourseTypeService.
 * Follows SOLID principles: Single Responsibility, Dependency Inversion.
 */
class CourseTypeController extends Controller
{
    /**
     * Course type service instance.
     *
     * @var CourseTypeService
     */
    protected CourseTypeService $courseTypeService;

    /**
     * Create a new controller instance.
     *
     * @param CourseTypeService $courseTypeService
     */
    public function __construct(CourseTypeService $courseTypeService)
    {
        $this->courseTypeService = $courseTypeService;
    }

    /**
     * Display a listing of course types.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = CourseType::query();

            $courseTypes = $query
                ->filterByRequest($request)
                ->ordered()
                ->paginateFromRequest($request)
                ->through(fn($courseType) => new CourseTypeResource($courseType));

            return $this->successResponse($courseTypes, 'Course types retrieved successfully.');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve course types.');
        }
    }

    /**
     * Store a newly created course type.
     *
     * @param StoreCourseTypeRequest $request
     * @return JsonResponse
     */
    public function store(StoreCourseTypeRequest $request): JsonResponse
    {
        try {
            $courseType = $this->courseTypeService->create($request->validated());

            return $this->createdResponse(
                new CourseTypeResource($courseType),
                'Course type created successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to create course type.');
        }
    }

    /**
     * Display the specified course type.
     *
     * @param CourseType $courseType
     * @return JsonResponse
     */
    public function show(CourseType $courseType): JsonResponse
    {
        try {
            $courseType->load('courses');

            return $this->successResponse(
                new CourseTypeResource($courseType),
                'Course type retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Course type not found.');
        }
    }

    /**
     * Update the specified course type.
     *
     * @param UpdateCourseTypeRequest $request
     * @param CourseType $courseType
     * @return JsonResponse
     */
    public function update(UpdateCourseTypeRequest $request, CourseType $courseType): JsonResponse
    {
        try {
            $updatedCourseType = $this->courseTypeService->update($courseType, $request->validated());

            return $this->successResponse(
                new CourseTypeResource($updatedCourseType),
                'Course type updated successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to update course type.');
        }
    }

    /**
     * Remove the specified course type.
     *
     * @param CourseType $courseType
     * @return JsonResponse
     */
    public function destroy(CourseType $courseType): JsonResponse
    {
        try {
            $this->courseTypeService->delete($courseType);

            return $this->successResponse(null, 'Course type deleted successfully.');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to delete course type.');
        }
    }

    /**
     * Activate the specified course type.
     *
     * @param CourseType $courseType
     * @return JsonResponse
     */
    public function activate(CourseType $courseType): JsonResponse
    {
        try {
            $activatedCourseType = $this->courseTypeService->activate($courseType);

            return $this->successResponse(
                new CourseTypeResource($activatedCourseType),
                'Course type activated successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to activate course type.');
        }
    }

    /**
     * Deactivate the specified course type.
     *
     * @param CourseType $courseType
     * @return JsonResponse
     */
    public function deactivate(CourseType $courseType): JsonResponse
    {
        try {
            $deactivatedCourseType = $this->courseTypeService->deactivate($courseType);

            return $this->successResponse(
                new CourseTypeResource($deactivatedCourseType),
                'Course type deactivated successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to deactivate course type.');
        }
    }
}
