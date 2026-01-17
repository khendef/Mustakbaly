<?php

namespace Modules\LearningModule\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\CachesQueries;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\LearningModule\Http\Requests\Lesson\MoveLessonRequest;
use Modules\LearningModule\Http\Requests\Lesson\ReorderLessonsRequest;
use Modules\LearningModule\Http\Requests\Lesson\StoreLessonRequest;
use Modules\LearningModule\Http\Requests\Lesson\UpdateLessonRequest;
use Modules\LearningModule\Http\Resources\LessonResource;
use Modules\LearningModule\Models\Lesson;
use Modules\LearningModule\Models\Unit;
use Modules\LearningModule\Services\LessonService;

/**
 * Controller for managing lessons.
 * Handles HTTP requests and delegates business logic to LessonService.
 * Follows SOLID principles: Single Responsibility, Dependency Inversion.
 */
class LessonController extends Controller
{
    use CachesQueries;
    /**
     * Lesson service instance.
     *
     * @var LessonService
     */
    protected LessonService $lessonService;

    /**
     * Create a new controller instance.
     *
     * @param LessonService $lessonService
     */
    public function __construct(LessonService $lessonService)
    {
        $this->lessonService = $lessonService;
    }

    /**
     * Display a listing of lessons.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $cacheKey = 'lessons.index.' . md5($request->getQueryString());

            $lessons = $this->remember($cacheKey, 1800, function () use ($request) {
                $query = Lesson::query();
                return $query
                    ->filterByRequest($request)
                    ->withRelations()
                    ->ordered()
                    ->paginateFromRequest($request)
                    ->through(fn($lesson) => new LessonResource($lesson));
            }, ['lessons']);

            return $this->successResponse($lessons, 'Lessons retrieved successfully.');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve lessons.');
        }
    }

    /**
     * Store a newly created lesson.
     *
     * @param StoreLessonRequest $request
     * @return JsonResponse
     */
    public function store(StoreLessonRequest $request): JsonResponse
    {
        try {
            $unit = Unit::findOrFail($request->input('unit_id'));
            $data = $request->validated();
            unset($data['unit_id']); // Remove unit_id as service sets it from unit object
            $lesson = $this->lessonService->create($unit, $data);
            $lesson->load(['unit']);

            return $this->createdResponse(
                new LessonResource($lesson),
                'Lesson created successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to create lesson.');
        }
    }

    /**
     * Display the specified lesson.
     *
     * @param Lesson $lesson
     * @return JsonResponse
     */
    public function show(Lesson $lesson): JsonResponse
    {
        try {
            $cacheKey = "lesson.{$lesson->lesson_id}";

            $lessonData = $this->remember($cacheKey, 1800, function () use ($lesson) {
                $lesson->load(['unit']);
                return new LessonResource($lesson);
            }, ['lessons', "lesson.{$lesson->lesson_id}"]);

            return $this->successResponse(
                $lessonData,
                'Lesson retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Lesson not found.');
        }
    }

    /**
     * Update the specified lesson.
     *
     * @param UpdateLessonRequest $request
     * @param Lesson $lesson
     * @return JsonResponse
     */
    public function update(UpdateLessonRequest $request, Lesson $lesson): JsonResponse
    {
        try {
            $updatedLesson = $this->lessonService->update($lesson, $request->validated());
            $updatedLesson->load(['unit']);

            return $this->successResponse(
                new LessonResource($updatedLesson),
                'Lesson updated successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to update lesson.');
        }
    }

    /**
     * Remove the specified lesson.
     *
     * @param Lesson $lesson
     * @return JsonResponse
     */
    public function destroy(Lesson $lesson): JsonResponse
    {
        try {
            $this->lessonService->delete($lesson);

            return $this->successResponse(null, 'Lesson deleted successfully.');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to delete lesson.');
        }
    }

    /**
     * Get lessons by unit.
     *
     * @param Request $request
     * @param Unit $unit
     * @return JsonResponse
     */
    public function byUnit(Request $request, Unit $unit): JsonResponse
    {
        try {
            $cacheKey = "lessons.unit.{$unit->unit_id}." . md5($request->getQueryString());

            $lessons = $this->remember($cacheKey, 1800, function () use ($request, $unit) {
                return Lesson::query()
                    ->byUnit($unit->unit_id)
                    ->filterByRequest($request)
                    ->withRelations()
                    ->ordered()
                    ->paginateFromRequest($request)
                    ->through(fn($lesson) => new LessonResource($lesson));
            }, ['lessons', "unit.{$unit->unit_id}"]);

            return $this->successResponse($lessons, 'Unit lessons retrieved successfully.');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve unit lessons.');
        }
    }

    /**
     * Reorder lessons within a unit.
     *
     * @param ReorderLessonsRequest $request
     * @param Unit $unit
     * @return JsonResponse
     */
    public function reorder(ReorderLessonsRequest $request, Unit $unit): JsonResponse
    {
        try {
            $this->lessonService->reorder($unit, $request->input('lesson_orders'));

            return $this->successResponse(null, 'Lessons reordered successfully.');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to reorder lessons.');
        }
    }

    /**
     * Move lesson to a specific position.
     *
     * @param MoveLessonRequest $request
     * @param Lesson $lesson
     * @return JsonResponse
     */
    public function moveToPosition(MoveLessonRequest $request, Lesson $lesson): JsonResponse
    {
        try {
            $updatedLesson = $this->lessonService->moveToPosition($lesson, $request->input('lesson_order'));
            $updatedLesson->load(['unit']);

            return $this->successResponse(
                new LessonResource($updatedLesson),
                'Lesson moved to position successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to move lesson to position.');
        }
    }

    /**
     * Get lesson duration.
     *
     * @param Lesson $lesson
     * @return JsonResponse
     */
    public function getDuration(Lesson $lesson): JsonResponse
    {
        try {
            $duration = $this->lessonService->getDuration($lesson);

            return $this->successResponse(
                [
                    'lesson_id' => $lesson->lesson_id,
                    'duration_minutes' => $duration,
                    'actual_duration_minutes' => $lesson->actual_duration_minutes,
                ],
                'Lesson duration retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve lesson duration.', null, null, $e);
        }
    }

    /**
     * Get lesson count for a unit.
     *
     * @param Unit $unit
     * @return JsonResponse
     */
    public function getLessonCount(Unit $unit): JsonResponse
    {
        try {
            $count = $this->lessonService->getLessonCount($unit);

            return $this->successResponse(
                [
                    'unit_id' => $unit->unit_id,
                    'lessons_count' => $count,
                ],
                'Lesson count retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve lesson count.');
        }
    }
}
