<?php

namespace Modules\LearningModule\Services;

use App\Traits\CachesQueries;
use App\Traits\HelperTrait;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\LearningModule\Models\Lesson;
use Modules\LearningModule\Models\Unit;

/**
 * Service class for managing lesson business logic.
 * Handles lesson creation, updates, ordering, deletion, and various lesson operations.
 */
class LessonService
{
    use HelperTrait, CachesQueries;

    /**
     * Enrollment service instance.
     *
     * @var EnrollmentService
     */
    protected EnrollmentService $enrollmentService;

    /**
     * Create a new lesson service instance.
     *
     * @param EnrollmentService $enrollmentService
     */
    public function __construct(EnrollmentService $enrollmentService)
    {
        $this->enrollmentService = $enrollmentService;
    }
    /**
     * Create a new lesson.
     *
     * @param Unit|int $unit
     * @param array $data
     * @return Lesson
     * @throws Exception
     */
    public function create($unit, array $data): Lesson
    {
        try {
            // Resolve unit if ID provided
            if (is_int($unit)) {
                $unit = Unit::find($unit);
                if (!$unit) {
                    throw new Exception("Unit not found.", 404);
                }
            }

            $data['unit_id'] = $unit->unit_id;

            // Set lesson_order if not provided (set to next available order)
            if (!isset($data['lesson_order'])) {
                $data['lesson_order'] = $this->getNextOrder(Lesson::class, 'unit_id', $unit->unit_id, 'lesson_order');
            } else {
                // Validate order uniqueness
                $this->validateOrder(Lesson::class, 'unit_id', $unit->unit_id, $data['lesson_order'], 'lesson_order', 'lesson_id', null, 'Lesson');
            }

            $lesson = Lesson::create($data);

            // Clear lesson and unit cache after creation
            $this->clearLessonCache($lesson, $unit);

            Log::info("Lesson created", [
                'lesson_id' => $lesson->lesson_id,
                'unit_id' => $unit->unit_id,
                'title' => $lesson->title,
                'lesson_order' => $lesson->lesson_order,
            ]);

            return $lesson;
        } catch (Exception $e) {
            Log::error("Failed to create lesson", [
                'unit_id' => is_int($unit) ? $unit : $unit->unit_id,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing lesson.
     *
     * @param Lesson $lesson
     * @param array $data
     * @return Lesson
     * @throws Exception
     */
    public function update(Lesson $lesson, array $data): Lesson
    {
        try {
            // Handle order change
            if (isset($data['lesson_order']) && $data['lesson_order'] != $lesson->lesson_order) {
                $this->validateOrder(Lesson::class, 'unit_id', $lesson->unit_id, $data['lesson_order'], 'lesson_order', 'lesson_id', $lesson->lesson_id, 'Lesson');
            }

            $wasCompleted = $lesson->is_completed;
            $lesson->update($data);

            // Clear lesson and unit cache after update
            $this->clearLessonCache($lesson);

            // If lesson was just marked as completed, trigger cascade logic
            if (isset($data['is_completed']) && $data['is_completed'] && !$wasCompleted) {
                $lesson->refresh(); // Refresh to get updated relationships

                // Check and update enrollments for the course
                $course = $lesson->unit->course;
                if ($course) {
                    $this->enrollmentService->checkAndHandleCourseCompletion($course);
                }
            }

            Log::info("Lesson updated", [
                'lesson_id' => $lesson->lesson_id,
                'updated_fields' => array_keys($data),
            ]);

            return $lesson->fresh();
        } catch (Exception $e) {
            Log::error("Failed to update lesson", [
                'lesson_id' => $lesson->lesson_id,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a lesson (soft delete).
     *
     * @param Lesson $lesson
     * @return bool
     * @throws Exception
     */
    public function delete(Lesson $lesson): bool
    {
        try {
            $lessonId = $lesson->lesson_id;
            $lessonTitle = $lesson->title;
            $unitId = $lesson->unit_id;
            $deleted = $lesson->delete();

            if ($deleted) {
                // Clear lesson and unit cache after deletion
                $this->clearLessonCache($lesson);

                Log::info("Lesson deleted", [
                    'lesson_id' => $lessonId,
                    'title' => $lessonTitle,
                    'unit_id' => $unitId,
                ]);
            }

            return $deleted;
        } catch (Exception $e) {
            Log::error("Failed to delete lesson", [
                'lesson_id' => $lesson->lesson_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Reorder lessons within a unit.
     *
     * @param Unit $unit
     * @param array $lessonOrders Array of ['lesson_id' => order] pairs
     * @return void
     * @throws Exception
     */
    public function reorder(Unit $unit, array $lessonOrders): void
    {
        try {
            // Validate all orders are unique
            $orders = array_values($lessonOrders);
            if (count($orders) !== count(array_unique($orders))) {
                throw new Exception("Duplicate orders found.", 422);
            }

            DB::transaction(function () use ($unit, $lessonOrders) {
                foreach ($lessonOrders as $lessonId => $order) {
                    Lesson::where('unit_id', $unit->unit_id)
                        ->where('lesson_id', $lessonId)
                        ->update(['lesson_order' => $order]);
                }
            });

            Log::info("Lessons reordered", [
                'unit_id' => $unit->unit_id,
                'lessons_count' => count($lessonOrders),
            ]);
        } catch (Exception $e) {
            Log::error("Failed to reorder lessons", [
                'unit_id' => $unit->unit_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Move lesson to a specific position.
     *
     * @param Lesson $lesson
     * @param int $newOrder
     * @return Lesson
     * @throws Exception
     */
    public function moveToPosition(Lesson $lesson, int $newOrder): Lesson
    {
        try {
            return DB::transaction(function () use ($lesson, $newOrder) {
                $this->validateOrder(Lesson::class, 'unit_id', $lesson->unit_id, $newOrder, 'lesson_order', 'lesson_id', $lesson->lesson_id, 'Lesson');

                // Shift other lessons if needed
                $this->shiftOrders(Lesson::class, 'unit_id', $lesson->unit_id, $lesson->lesson_order, $newOrder, 'lesson_order', 'lesson_id', $lesson->lesson_id);

                $lesson->update(['lesson_order' => $newOrder]);

                Log::info("Lesson moved to position", [
                    'lesson_id' => $lesson->lesson_id,
                    'old_order' => $lesson->lesson_order,
                    'new_order' => $newOrder,
                ]);

                return $lesson->fresh();
            });
        } catch (Exception $e) {
            Log::error("Failed to move lesson", [
                'lesson_id' => $lesson->lesson_id,
                'new_order' => $newOrder,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get lessons for a unit.
     *
     * @param Unit|int $unit
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLessonsByUnit($unit, array $filters = [])
    {
        $unitId = is_int($unit) ? $unit : $unit->unit_id;

        $query = Lesson::where('unit_id', $unitId);

        // Apply filters
        if (isset($filters['include_deleted']) && $filters['include_deleted']) {
            $query->withTrashed();
        }

        // Order by lesson_order
        $query->orderBy('lesson_order', 'asc');

        return $query->get();
    }

    /**
     * Get lesson by ID.
     *
     * @param int $lessonId
     * @return Lesson
     * @throws Exception
     */
    public function getById(int $lessonId): Lesson
    {
        $lesson = Lesson::find($lessonId);

        if (!$lesson) {
            throw new Exception("Lesson not found.", 404);
        }

        return $lesson;
    }

    /**
     * Get lesson duration.
     *
     * @param Lesson $lesson
     * @return int Duration in minutes
     */
    public function getDuration(Lesson $lesson): int
    {
        return $lesson->actual_duration_minutes ?? 0;
    }


    /**
     * Get lesson count for a unit.
     *
     * @param Unit $unit
     * @return int
     */
    public function getLessonCount(Unit $unit): int
    {
        return Lesson::where('unit_id', $unit->unit_id)->count();
    }

    /**
     * Mark a lesson as completed.
     *
     * @param Lesson $lesson
     * @return Lesson
     * @throws Exception
     */
    public function markAsCompleted(Lesson $lesson): Lesson
    {
        try {
            if ($lesson->is_completed) {
                return $lesson; // Already completed
            }

            $lesson->update(['is_completed' => true]);

            Log::info("Lesson marked as completed", [
                'lesson_id' => $lesson->lesson_id,
                'unit_id' => $lesson->unit_id,
            ]);

            // Check and update enrollments for the course
            $course = $lesson->unit->course;
            if ($course) {
                $this->enrollmentService->checkAndHandleCourseCompletion($course);
            }

            return $lesson->fresh();
        } catch (Exception $e) {
            Log::error("Failed to mark lesson as completed", [
                'lesson_id' => $lesson->lesson_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Clear lesson related cache.
     *
     * @param Lesson $lesson
     * @param Unit|null $unit Optional unit to clear unit cache
     * @return void
     */
    protected function clearLessonCache(Lesson $lesson, ?Unit $unit = null): void
    {
        if ($this->supportsCacheTags()) {
            // Use tags for efficient bulk invalidation
            $this->flushTags(['lessons', "lesson.{$lesson->lesson_id}"]);
            $unit = $unit ?? $lesson->unit;
            if ($unit) {
                $this->flushTags(["unit.{$unit->unit_id}"]);
            }
        } else {
            // Fallback to individual key deletion
            $keys = [
                "lesson.{$lesson->lesson_id}",
            ];
            $unit = $unit ?? $lesson->unit;
            if ($unit) {
                $keys[] = "unit.{$unit->unit_id}";
                $keys[] = "lessons.unit.{$unit->unit_id}";
            }
            $this->forgetMany($keys);
        }
    }
}
