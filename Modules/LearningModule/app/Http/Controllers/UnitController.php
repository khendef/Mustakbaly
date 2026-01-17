<?php

namespace Modules\LearningModule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\CachesQueries;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\LearningModule\Http\Requests\Unit\MoveUnitRequest;
use Modules\LearningModule\Http\Requests\Unit\ReorderUnitsRequest;
use Modules\LearningModule\Http\Requests\Unit\StoreUnitRequest;
use Modules\LearningModule\Http\Requests\Unit\UpdateUnitRequest;
use Modules\LearningModule\Http\Resources\UnitResource;
use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\Unit;
use Modules\LearningModule\Services\UnitService;

/**
 * Controller for managing units.
 * Handles HTTP requests and delegates business logic to UnitService.
 * Follows SOLID principles: Single Responsibility, Dependency Inversion.
 */
class UnitController extends Controller
{
    use CachesQueries;
    /**
     * Unit service instance.
     *
     * @var UnitService
     */
    protected UnitService $unitService;

    /**
     * Create a new controller instance.
     *
     * @param UnitService $unitService
     */
    public function __construct(UnitService $unitService)
    {
        $this->unitService = $unitService;
    }

    /**
     * Display a listing of units.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $cacheKey = 'units.index.' . md5($request->getQueryString());

            $units = $this->remember($cacheKey, 1800, function () use ($request) {
                $query = Unit::query();
                return $query
                    ->filterByRequest($request)
                    ->withRelations()
                    ->ordered()
                    ->paginateFromRequest($request)
                    ->through(fn($unit) => new UnitResource($unit));
            }, ['units']);

            return $this->successResponse($units, 'Units retrieved successfully.');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve units.');
        }
    }

    /**
     * Store a newly created unit.
     *
     * @param StoreUnitRequest $request
     * @return JsonResponse
     */
    public function store(StoreUnitRequest $request): JsonResponse
    {
        try {
            $course = Course::findOrFail($request->input('course_id'));
            $data = $request->validated();
            unset($data['course_id']); // Remove course_id as service sets it from course object
            $unit = $this->unitService->create($course, $data);
            $unit->load(['course']);

            return $this->createdResponse(
                new UnitResource($unit),
                'Unit created successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to create unit.');
        }
    }

    /**
     * Display the specified unit.
     *
     * @param Unit $unit
     * @return JsonResponse
     */
    public function show(Unit $unit): JsonResponse
    {
        try {
            $cacheKey = "unit.{$unit->unit_id}";

            $unitData = $this->remember($cacheKey, 1800, function () use ($unit) {
                $unit->load(['course', 'lessons']);
                return new UnitResource($unit);
            }, ['units', "unit.{$unit->unit_id}"]);

            return $this->successResponse(
                $unitData,
                'Unit retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Unit not found.');
        }
    }

    /**
     * Update the specified unit.
     *
     * @param UpdateUnitRequest $request
     * @param Unit $unit
     * @return JsonResponse
     */
    public function update(UpdateUnitRequest $request, Unit $unit): JsonResponse
    {
        try {
            $updatedUnit = $this->unitService->update($unit, $request->validated());
            $updatedUnit->load(['course']);

            return $this->successResponse(
                new UnitResource($updatedUnit),
                'Unit updated successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to update unit.');
        }
    }

    /**
     * Remove the specified unit.
     *
     * @param Unit $unit
     * @return JsonResponse
     */
    public function destroy(Unit $unit): JsonResponse
    {
        try {
            $this->unitService->delete($unit);

            return $this->successResponse(null, 'Unit deleted successfully.');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to delete unit.');
        }
    }

    /**
     * Get units by course.
     *
     * @param Request $request
     * @param Course $course
     * @return JsonResponse
     */
    public function byCourse(Request $request, Course $course): JsonResponse
    {
        try {
            $cacheKey = "units.course.{$course->course_id}." . md5($request->getQueryString());

            $units = $this->remember($cacheKey, 1800, function () use ($request, $course) {
                return Unit::query()
                    ->byCourse($course->course_id)
                    ->filterByRequest($request)
                    ->withRelations()
                    ->ordered()
                    ->paginateFromRequest($request)
                    ->through(fn($unit) => new UnitResource($unit));
            }, ['units', "course.{$course->course_id}"]);

            return $this->successResponse($units, 'Course units retrieved successfully.');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve course units.');
        }
    }

    /**
     * Reorder units within a course.
     *
     * @param ReorderUnitsRequest $request
     * @param Course $course
     * @return JsonResponse
     */
    public function reorder(ReorderUnitsRequest $request, Course $course): JsonResponse
    {
        try {
            $this->unitService->reorder($course, $request->input('unit_orders'));

            return $this->successResponse(null, 'Units reordered successfully.');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to reorder units.');
        }
    }

    /**
     * Move unit to a specific position.
     *
     * @param MoveUnitRequest $request
     * @param Unit $unit
     * @return JsonResponse
     */
    public function moveToPosition(MoveUnitRequest $request, Unit $unit): JsonResponse
    {
        try {
            $updatedUnit = $this->unitService->moveToPosition($unit, $request->input('unit_order'));
            $updatedUnit->load(['course']);

            return $this->successResponse(
                new UnitResource($updatedUnit),
                'Unit moved to position successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to move unit to position.');
        }
    }

    /**
     * Get unit duration.
     *
     * @param Unit $unit
     * @return JsonResponse
     */
    public function getDuration(Unit $unit): JsonResponse
    {
        try {
            $duration = $this->unitService->getDuration($unit);

            return $this->successResponse(
                [
                    'unit_id' => $unit->unit_id,
                    'duration_minutes' => $duration,
                    'actual_duration_minutes' => $unit->actual_duration_minutes,
                ],
                'Unit duration retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve unit duration.', null, null, $e);
        }
    }

    /**
     * Check if unit can be deleted.
     *
     * @param Unit $unit
     * @return JsonResponse
     */
    public function canBeDeleted(Unit $unit): JsonResponse
    {
        try {
            $canBeDeleted = $this->unitService->canBeDeleted($unit);
            $lessonsCount = $unit->lessons()->count();

            return $this->successResponse(
                [
                    'unit_id' => $unit->unit_id,
                    'can_be_deleted' => $canBeDeleted,
                    'lessons_count' => $lessonsCount,
                    'reason' => $canBeDeleted ? null : "Unit has {$lessonsCount} lesson(s).",
                ],
                'Unit deletion check completed.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to check if unit can be deleted.');
        }
    }

    /**
     * Get unit count for a course.
     *
     * @param Course $course
     * @return JsonResponse
     */
    public function getUnitCount(Course $course): JsonResponse
    {
        try {
            $count = $this->unitService->getUnitCount($course);

            return $this->successResponse(
                [
                    'course_id' => $course->course_id,
                    'units_count' => $count,
                ],
                'Unit count retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve unit count.');
        }
    }
}
