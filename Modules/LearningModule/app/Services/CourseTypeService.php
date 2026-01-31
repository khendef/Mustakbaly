<?php

namespace Modules\LearningModule\Services;

use App\Traits\CachesQueries;
use App\Traits\HelperTrait;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\LearningModule\Models\CourseType;

/**
 * Service class for managing course type business logic.
 * Handles course type creation, updates, activation/deactivation, and various course type operations.
 */
class CourseTypeService
{
    use HelperTrait, CachesQueries;
    /**
     * Create a new course type.
     *
     * @param array $data
     * @return CourseType
     * @throws Exception
     */
    public function create(array $data): ?CourseType
    {
        try {
            // Generate slug if not provided (use English name)
            if (empty($data['slug']) && !empty($data['name'])) {
                $nameForSlug = $this->translatableToSlugSource($data['name'], 'en');
                if ($nameForSlug !== '') {
                    $data['slug'] = $this->generateUniqueSlug($nameForSlug, CourseType::class);
                }
            }

            // Ensure slug is unique
            if (isset($data['slug'])) {
                $data['slug'] = $this->ensureUniqueSlug($data['slug'], CourseType::class);
            }

            $courseType = CourseType::create($data);

            // Clear cache after creation
            $this->clearCourseTypeCache();

            Log::info("Course type created", [
                'course_type_id' => $courseType->course_type_id,
                'name' => $this->translatableToSlugSource($courseType->name ?? [], 'en'),
                'slug' => $courseType->slug,
            ]);

            return $courseType;
        } catch (Exception $e) {
            Log::error("Failed to create course type", [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Update an existing course type.
     *
     * @param CourseType $courseType
     * @param array $data
     * @return CourseType
     * @throws Exception
     */
    public function update(CourseType $courseType, array $data): ?CourseType
    {
        try {
            // Handle slug update (use English name)
            if (isset($data['name']) && empty($data['slug'])) {
                $nameForSlug = $this->translatableToSlugSource($data['name'], 'en');
                if ($nameForSlug !== '') {
                    $data['slug'] = $this->generateUniqueSlug($nameForSlug, CourseType::class, 'slug', 'course_type_id', $courseType->course_type_id);
                }
            } elseif (isset($data['slug'])) {
                $data['slug'] = $this->ensureUniqueSlug($data['slug'], CourseType::class, 'slug', 'course_type_id', $courseType->course_type_id);
            }

            $courseType->update($data);

            // Clear cache after update
            $this->clearCourseTypeCache($courseType);

            Log::info("Course type updated", [
                'course_type_id' => $courseType->course_type_id,
                'updated_fields' => array_keys($data),
            ]);

            return $courseType->fresh();
        } catch (Exception $e) {
            Log::error("Failed to update course type", [
                'course_type_id' => $courseType->course_type_id,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Activate a course type.
     *
     * @param CourseType $courseType
     * @return CourseType
     * @throws Exception
     */
    public function activate(CourseType $courseType): ?CourseType
    {
        if ($courseType->is_active) {
            return $courseType;
        }

        try {
            $courseType->update(['is_active' => true]);

            // Clear cache after activation
            $this->clearCourseTypeCache($courseType);

            Log::info("Course type activated", [
                'course_type_id' => $courseType->course_type_id,
                'name' => $courseType->name,
            ]);

            return $courseType->fresh();
        } catch (Exception $e) {
            Log::error("Failed to activate course type", [
                'course_type_id' => $courseType->course_type_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Deactivate a course type.
     *
     * @param CourseType $courseType
     * @return CourseType
     * @throws Exception
     */
    public function deactivate(CourseType $courseType): ?CourseType
    {
        if (!$courseType->is_active) {
            return $courseType;
        }

        // Check if course type has active courses
        $activeCoursesCount = $courseType->courses()
            ->where('status', 'published')
            ->count();

        if ($activeCoursesCount > 0) {
            Log::warning("Attempted to deactivate course type with active published courses", [
                'course_type_id' => $courseType->course_type_id,
                'active_courses_count' => $activeCoursesCount,
            ]);
            return null;
        }

        try {
            $courseType->update(['is_active' => false]);

            // Clear cache after deactivation
            $this->clearCourseTypeCache($courseType);

            Log::info("Course type deactivated", [
                'course_type_id' => $courseType->course_type_id,
                'name' => $courseType->name,
            ]);

            return $courseType->fresh();
        } catch (Exception $e) {
            Log::error("Failed to deactivate course type", [
                'course_type_id' => $courseType->course_type_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Delete a course type (soft delete).
     *
     * @param CourseType $courseType
     * @return bool
     * @throws Exception
     */
    public function delete(CourseType $courseType): bool
    {
        // Check if course type has courses
        $coursesCount = $courseType->courses()->count();

        if ($coursesCount > 0) {
            Log::warning("Attempted to delete course type with courses", [
                'course_type_id' => $courseType->course_type_id,
                'courses_count' => $coursesCount,
            ]);
            return false;
        }

        try {
            return DB::transaction(function () use ($courseType) {
                $courseTypeId = $courseType->course_type_id;
                $courseTypeName = $courseType->name;
                $deleted = $courseType->delete();

                if ($deleted) {
                    // Clear cache after deletion
                    $this->clearCourseTypeCache();

                    Log::info("Course type deleted", [
                        'course_type_id' => $courseTypeId,
                        'name' => $courseTypeName,
                    ]);
                }

                return $deleted;
            });
        } catch (Exception $e) {
            Log::error("Failed to delete course type", [
                'course_type_id' => $courseType->course_type_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Check if course type can be deleted.
     *
     * @param CourseType $courseType
     * @return bool
     */
    public function canBeDeleted(CourseType $courseType): bool
    {
        return $courseType->courses()->count() === 0;
    }

    /**
     * Check if course type can be deactivated.
     *
     * @param CourseType $courseType
     * @return bool
     */
    public function canBeDeactivated(CourseType $courseType): bool
    {
        $activeCoursesCount = $courseType->courses()
            ->where('status', 'published')
            ->count();

        return $activeCoursesCount === 0;
    }

    /**
     * Get all active course types.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveCourseTypes()
    {
        return $this->remember('course_types.active', 3600, function () {
            return CourseType::where('is_active', true)
                ->orderBy('name', 'asc')
                ->get();
        }, ['course_types']);
    }

    /**
     * Get all course types (active and inactive).
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllCourseTypes()
    {
        return $this->remember('course_types.all', 3600, function () {
            return CourseType::orderBy('name', 'asc')->get();
        }, ['course_types']);
    }

    /**
     * Get course type by slug.
     *
     * @param string $slug
     * @return CourseType|null
     */
    public function getBySlug(string $slug): ?CourseType
    {
        return $this->remember("course_type.slug.{$slug}", 3600, function () use ($slug) {
            return CourseType::where('slug', $slug)->first();
        }, ['course_types']);
    }

    /**
     * Get course type by ID.
     *
     * @param int $courseTypeId
     * @return CourseType|null
     */
    public function getById(int $courseTypeId): ?CourseType
    {
        return $this->remember("course_type.{$courseTypeId}", 3600, function () use ($courseTypeId) {
            $courseType = CourseType::find($courseTypeId);

            if (!$courseType) {
                Log::warning("Course type not found", [
                    'course_type_id' => $courseTypeId,
                ]);
                return null;
            }

            return $courseType;
        }, ['course_types']);
    }

    /**
     * Get course count for a course type.
     *
     * @param CourseType $courseType
     * @return int
     */
    public function getCourseCount(CourseType $courseType): int
    {
        return $courseType->courses()->count();
    }

    /**
     * Get active course count for a course type.
     *
     * @param CourseType $courseType
     * @return int
     */
    public function getActiveCourseCount(CourseType $courseType): int
    {
        $cacheKey = "course_type.{$courseType->course_type_id}.active_count";

        return $this->remember($cacheKey, 3600, function () use ($courseType) {
            return $courseType->courses()
                ->where('status', 'published')
                ->count();
        }, ['course_types', "course_type.{$courseType->course_type_id}"]);
    }

    /**
     * Clear course type related cache.
     * Uses Redis tags for efficient bulk invalidation.
     *
     * @param CourseType|null $courseType Optional course type to clear specific cache
     * @return void
     */
    protected function clearCourseTypeCache(?CourseType $courseType = null): void
    {
        // Use Redis tags for efficient bulk invalidation
        $this->flushTags(['course_types']);
        if ($courseType) {
            $this->flushTags(["course_type.{$courseType->course_type_id}"]);
        }
    }
}
