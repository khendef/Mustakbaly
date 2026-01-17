<?php

namespace Modules\LearningModule\Services;

use App\Traits\CachesQueries;
use App\Traits\HelperTrait;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\Lesson;
use Modules\LearningModule\Models\Unit;

/**
 * Service class for managing unit business logic.
 * Handles unit creation, updates, ordering, deletion, and various unit operations.
 */
class UnitService
{
    use HelperTrait, CachesQueries;
    /**
     * Create a new unit.
     *
     * @param Course|int $course
     * @param array $data
     * @return Unit
     * @throws Exception
     */
    public function create($course, array $data): Unit
    {
        try {
            // Resolve course if ID provided
            if (is_int($course)) {
                $course = Course::find($course);
                if (!$course) {
                    throw new Exception("Course not found.", 404);
                }
            }

            $data['course_id'] = $course->course_id;

            // Set unit_order if not provided (set to next available order)
            if (!isset($data['unit_order'])) {
                $data['unit_order'] = $this->getNextOrder(Unit::class, 'course_id', $course->course_id, 'unit_order');
            } else {
                // Validate order uniqueness
                $this->validateOrder(Unit::class, 'course_id', $course->course_id, $data['unit_order'], 'unit_order', 'unit_id', null, 'Unit');
            }

            $unit = Unit::create($data);

            // Clear unit and course cache after creation
            $this->clearUnitCache($unit, $course);

            Log::info("Unit created", [
                'unit_id' => $unit->unit_id,
                'course_id' => $course->course_id,
                'title' => $unit->title,
                'unit_order' => $unit->unit_order,
            ]);

            return $unit;
        } catch (Exception $e) {
            Log::error("Failed to create unit", [
                'course_id' => is_int($course) ? $course : $course->course_id,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing unit.
     *
     * @param Unit $unit
     * @param array $data
     * @return Unit
     * @throws Exception
     */
    public function update(Unit $unit, array $data): Unit
    {
        try {
            // Handle order change
            if (isset($data['unit_order']) && $data['unit_order'] != $unit->unit_order) {
                $this->validateOrder(Unit::class, 'course_id', $unit->course_id, $data['unit_order'], 'unit_order', 'unit_id', $unit->unit_id, 'Unit');
            }

            $unit->update($data);

            // Clear unit and course cache after update
            $this->clearUnitCache($unit);

            Log::info("Unit updated", [
                'unit_id' => $unit->unit_id,
                'updated_fields' => array_keys($data),
            ]);

            return $unit->fresh();
        } catch (Exception $e) {
            Log::error("Failed to update unit", [
                'unit_id' => $unit->unit_id,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a unit (soft delete).
     *
     * @param Unit $unit
     * @return bool
     * @throws Exception
     */
    public function delete(Unit $unit): bool
    {
        try {
            // Check if unit has lessons
            $lessonsCount = $unit->lessons()->count();

            if ($lessonsCount > 0) {
                throw new Exception("Cannot delete unit with {$lessonsCount} lesson(s).", 422);
            }

            $unitId = $unit->unit_id;
            $unitTitle = $unit->title;
            $courseId = $unit->course_id;
            $deleted = $unit->delete();

            if ($deleted) {
                // Clear unit and course cache after deletion
                $this->clearUnitCache($unit);
                Log::info("Unit deleted", [
                    'unit_id' => $unitId,
                    'title' => $unitTitle,
                    'course_id' => $courseId,
                ]);
            }

            return $deleted;
        } catch (Exception $e) {
            Log::error("Failed to delete unit", [
                'unit_id' => $unit->unit_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Reorder units within a course.
     *
     * @param Course $course
     * @param array $unitOrders Array of ['unit_id' => order] pairs
     * @return void
     * @throws Exception
     */
    public function reorder(Course $course, array $unitOrders): void
    {
        try {
            // Validate all orders are unique
            $orders = array_values($unitOrders);
            if (count($orders) !== count(array_unique($orders))) {
                throw new Exception("Duplicate orders found.", 422);
            }

            DB::transaction(function () use ($course, $unitOrders) {
                foreach ($unitOrders as $unitId => $order) {
                    Unit::where('course_id', $course->course_id)
                        ->where('unit_id', $unitId)
                        ->update(['unit_order' => $order]);
                }
            });

            Log::info("Units reordered", [
                'course_id' => $course->course_id,
                'units_count' => count($unitOrders),
            ]);
        } catch (Exception $e) {
            Log::error("Failed to reorder units", [
                'course_id' => $course->course_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Move unit to a specific position.
     *
     * @param Unit $unit
     * @param int $newOrder
     * @return Unit
     * @throws Exception
     */
    public function moveToPosition(Unit $unit, int $newOrder): Unit
    {
        try {
            return DB::transaction(function () use ($unit, $newOrder) {
                $this->validateOrder(Unit::class, 'course_id', $unit->course_id, $newOrder, 'unit_order', 'unit_id', $unit->unit_id, 'Unit');

                // Shift other units if needed
                $this->shiftOrders(Unit::class, 'course_id', $unit->course_id, $unit->unit_order, $newOrder, 'unit_order', 'unit_id', $unit->unit_id);

                $unit->update(['unit_order' => $newOrder]);

                Log::info("Unit moved to position", [
                    'unit_id' => $unit->unit_id,
                    'old_order' => $unit->unit_order,
                    'new_order' => $newOrder,
                ]);

                return $unit->fresh();
            });
        } catch (Exception $e) {
            Log::error("Failed to move unit", [
                'unit_id' => $unit->unit_id,
                'new_order' => $newOrder,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get units for a course.
     *
     * @param Course|int $course
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUnitsByCourse($course, array $filters = [])
    {
        $courseId = is_int($course) ? $course : $course->course_id;

        $query = Unit::where('course_id', $courseId)
            ->with(['lessons']);

        // Apply filters
        if (isset($filters['include_deleted']) && $filters['include_deleted']) {
            $query->withTrashed();
        }

        // Order by unit_order
        $query->orderBy('unit_order', 'asc');

        return $query->get();
    }

    /**
     * Get unit by ID.
     *
     * @param int $unitId
     * @return Unit
     * @throws Exception
     */
    public function getById(int $unitId): Unit
    {
        $unit = Unit::find($unitId);

        if (!$unit) {
            throw new Exception("Unit not found.", 404);
        }

        return $unit;
    }


    /**
     * Get unit duration.
     *
     * @param Unit $unit
     * @return int Duration in minutes
     */
    public function getDuration(Unit $unit): int
    {
        return $unit->actual_duration_minutes ?? 0;
    }


    /**
     * Check if unit can be deleted.
     *
     * @param Unit $unit
     * @return bool
     */
    public function canBeDeleted(Unit $unit): bool
    {
        return $unit->lessons()->count() === 0;
    }

    /**
     * Get unit count for a course.
     *
     * @param Course $course
     * @return int
     */
    public function getUnitCount(Course $course): int
    {
        return Unit::where('course_id', $course->course_id)->count();
    }

    /**
     * Clear unit related cache.
     *
     * @param Unit $unit
     * @param Course|null $course Optional course to clear course cache
     * @return void
     */
    protected function clearUnitCache(Unit $unit, ?Course $course = null): void
    {
        if ($this->supportsCacheTags()) {
            // Use tags for efficient bulk invalidation
            $this->flushTags(['units', "unit.{$unit->unit_id}"]);
            if ($course) {
                $this->flushTags(["course.{$course->course_id}"]);
            }
        } else {
            // Fallback to individual key deletion
            $keys = [
                "unit.{$unit->unit_id}",
            ];
            if ($course) {
                $keys[] = "course.{$course->course_id}";
                $keys[] = "units.course.{$course->course_id}";
            }
            $this->forgetMany($keys);
        }
    }
}
