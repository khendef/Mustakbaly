<?php

namespace Modules\LearningModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Modules\LearningModule\Http\Requests\CourseType\FilterCourseTypesRequest;
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
     * @param FilterCourseTypesRequest $request
     * @return JsonResponse
     */
    public function index(FilterCourseTypesRequest $request): JsonResponse
    {
        try {
            $query = CourseType::query();

            $courseTypes = $query
                ->filterByRequest($request)
                ->ordered()
                ->paginateFromRequest($request)
                ->through(fn($courseType) => new CourseTypeResource($courseType));

            return self::paginated($courseTypes, 'Course types retrieved successfully.');
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving course types', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to retrieve course types at this time.', 500);
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

            if (!$courseType) {
                throw new Exception('Failed to create course type. Please check your input and try again.', 422);
            }

            return self::success(
                new CourseTypeResource($courseType),
                'Course type created successfully.',
                201
            );
        } catch (Exception $e) {
            Log::error('Unexpected error creating course type', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while creating the course type.', 500);
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

            return self::success(
                new CourseTypeResource($courseType),
                'Course type retrieved successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error retrieving course type', [
                'course_type_id' => $courseType->course_type_id ?? null,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Unable to retrieve course type details.', 500);
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

            if (!$updatedCourseType) {
                throw new Exception('Failed to update course type. Please check your input and try again.', 422);
            }

            return self::success(
                new CourseTypeResource($updatedCourseType),
                'Course type updated successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error updating course type', [
                'course_type_id' => $courseType->course_type_id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while updating the course type.', 500);
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
            $deleted = $this->courseTypeService->delete($courseType);

            if (!$deleted) {
                throw new Exception('Cannot delete course type. It may have courses associated with it.', 422);
            }

            return self::success(null, 'Course type deleted successfully.');
        } catch (Exception $e) {
            Log::error('Unexpected error deleting course type', [
                'course_type_id' => $courseType->course_type_id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while deleting the course type.', 500);
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

            if (!$activatedCourseType) {
                throw new Exception('Failed to activate course type.', 422);
            }

            return self::success(
                new CourseTypeResource($activatedCourseType),
                'Course type activated successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error activating course type', [
                'course_type_id' => $courseType->course_type_id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while activating the course type.', 500);
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

            if (!$deactivatedCourseType) {
                throw new Exception('Cannot deactivate course type. It may have active published courses.', 422);
            }

            return self::success(
                new CourseTypeResource($deactivatedCourseType),
                'Course type deactivated successfully.'
            );
        } catch (Exception $e) {
            Log::error('Unexpected error deactivating course type', [
                'course_type_id' => $courseType->course_type_id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('An error occurred while deactivating the course type.', 500);
        }
    }
}
