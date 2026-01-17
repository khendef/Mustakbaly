<?php

namespace Modules\LearningModule\Services;

use App\Traits\CachesQueries;
use App\Traits\HelperTrait;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\LearningModule\Enums\CourseStatus;
use Modules\LearningModule\Models\Unit;
use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\CourseType;
use Modules\LearningModule\Models\Enrollment;
use Modules\LearningModule\Services\CourseInstructorService;

/**
 * Service class for managing course business logic.
 * Handles course creation, updates, publishing, and various course operations.
 */
class CourseService
{
    use HelperTrait, CachesQueries;

    /**
     * Create a new course.
     *
     * @param array $data
     * @return Course
     * @throws Exception
     */
    public function create(array $data): Course
    {
        try {
            // Validate course type exists
            if (isset($data['course_type_id'])) {
                $courseType = CourseType::find($data['course_type_id']);
                if (!$courseType) {
                    throw new Exception("Course type not found.", 404);
                }
            }

            // Generate slug if not provided
            if (empty($data['slug']) && !empty($data['title'])) {
                $data['slug'] = $this->generateUniqueSlug($data['title'], Course::class);
            }

            // Ensure slug is unique
            if (isset($data['slug'])) {
                $data['slug'] = $this->ensureUniqueSlug($data['slug'], Course::class);
            }

            // Set created_by (use 1 as default for testing when auth is disabled)
            $data['created_by'] = Auth::id();

            // Set default status if not provided
            if (empty($data['status'])) {
                $data['status'] = CourseStatus::DRAFT->value;
            }

            $course = Course::create($data);

            // Clear course cache after creation
            $this->clearCourseCache();

            Log::info("Course created", [
                'course_id' => $course->course_id,
                'title' => $course->title,
                'created_by' => $data['created_by'],
            ]);

            return $course;
        } catch (Exception $e) {
            Log::error("Failed to create course", [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing course.
     *
     * @param Course $course
     * @param array $data
     * @return Course
     * @throws Exception
     */
    public function update(Course $course, array $data): Course
    {
        try {
            // Validate course type if being changed
            if (isset($data['course_type_id']) && $data['course_type_id'] != $course->course_type_id) {
                $courseType = CourseType::find($data['course_type_id']);
                if (!$courseType) {
                    throw new Exception("Course type not found.", 404);
                }
            }

            // Handle slug update
            if (isset($data['title']) && empty($data['slug'])) {
                // If title changed and slug not provided, generate new slug
                $data['slug'] = $this->generateUniqueSlug($data['title'], Course::class);
            } elseif (isset($data['slug'])) {
                // Ensure slug is unique (excluding current course)
                $data['slug'] = $this->ensureUniqueSlug($data['slug'], Course::class, 'slug', 'course_id', $course->course_id);
            }

            $course->update($data);

            // Clear course cache after update
            $this->clearCourseCache($course);

            Log::info("Course updated", [
                'course_id' => $course->course_id,
                'updated_fields' => array_keys($data),
            ]);

            return $course->fresh();
        } catch (Exception $e) {
            Log::error("Failed to update course", [
                'course_id' => $course->course_id,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Publish a course.
     *
     * @param Course $course
     * @return Course
     * @throws Exception
     */
    public function publish(Course $course): Course
    {
        try {
            if (!$this->isPublishable($course)) {
                $reasons = $this->getUnpublishabilityReasons($course);
                $message = 'Course cannot be published. Reasons: ' . implode(', ', $reasons);
                throw new Exception($message, 422);
            }

            $course->update([
                'status' => CourseStatus::PUBLISHED->value,
                'published_at' => now(),
            ]);

            // Clear course cache after publishing
            $this->clearCourseCache($course);

            Log::info("Course published", [
                'course_id' => $course->course_id,
                'title' => $course->title,
            ]);

            return $course->fresh();
        } catch (Exception $e) {
            Log::error("Failed to publish course", [
                'course_id' => $course->course_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Unpublish a course.
     *
     * @param Course $course
     * @return Course
     */
    public function unpublish(Course $course): Course
    {
        $course->update([
            'status' => CourseStatus::DRAFT->value,
            'published_at' => null,
        ]);

        // Clear course cache after unpublishing
        $this->clearCourseCache($course);

        return $course->fresh();
    }

    /**
     * Change course status.
     *
     * @param Course $course
     * @param string $newStatus
     * @return Course
     * @throws Exception
     */
    public function changeStatus(Course $course, string $newStatus): Course
    {
        try {
            $validStatuses = [
                CourseStatus::DRAFT->value,
                CourseStatus::REVIEW->value,
                CourseStatus::PUBLISHED->value,
                CourseStatus::ARCHIVED->value,
            ];
            if (!in_array($newStatus, $validStatuses)) {
                throw new Exception("Invalid status: {$newStatus}", 422);
            }

            $currentStatus = $course->status;

            // Handle special status changes
            if ($newStatus === CourseStatus::PUBLISHED->value && $currentStatus !== CourseStatus::PUBLISHED->value) {
                return $this->publish($course);
            }

            $updateData = ['status' => $newStatus];

            // Clear published_at if unpublishing
            if ($newStatus !== CourseStatus::PUBLISHED->value && $course->published_at) {
                $updateData['published_at'] = null;
            }

            $course->update($updateData);

            // Clear course cache after status change
            $this->clearCourseCache($course);

            Log::info("Course status changed", [
                'course_id' => $course->course_id,
                'old_status' => $currentStatus,
                'new_status' => $newStatus,
            ]);

            return $course->fresh();
        } catch (Exception $e) {
            Log::error("Failed to change course status", [
                'course_id' => $course->course_id,
                'new_status' => $newStatus,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Check if course can be published.
     *
     * @param Course $course
     * @return bool
     */
    public function isPublishable(Course $course): bool
    {
        $reasons = $this->getUnpublishabilityReasons($course);
        return empty($reasons);
    }

    /**
     * Get reasons why course cannot be published.
     *
     * @param Course $course
     * @return array
     */
    public function getUnpublishabilityReasons(Course $course): array
    {
        $reasons = [];

        // Check if course has at least one instructor
        if ($course->instructors()->count() === 0) {
            $reasons[] = 'Course must have at least one instructor';
        }

        // Check if course has at least one unit
        if ($course->units()->count() === 0) {
            $reasons[] = 'Course must have at least one unit';
        }

        // Check if course type is active
        if ($course->courseType && !$course->courseType->is_active) {
            $reasons[] = 'Course type must be active';
        }

        // Check if title and description are provided
        if (empty($course->title)) {
            $reasons[] = 'Course title is required';
        }

        if (empty($course->description)) {
            $reasons[] = 'Course description is required';
        }

        return $reasons;
    }

    /**
     * Check if course can be deleted.
     *
     * @param Course $course
     * @return bool
     */
    public function canBeDeleted(Course $course): bool
    {
        // Check for active enrollments
        $activeEnrollments = $course->enrollments()
            ->where('enrollment_status', 'active')
            ->count();

        return $activeEnrollments === 0;
    }

    /**
     * Delete a course (soft delete).
     *
     * @param Course $course
     * @return bool
     * @throws Exception
     */
    public function delete(Course $course): bool
    {
        try {
            if (!$this->canBeDeleted($course)) {
                throw new Exception("Course cannot be deleted because it has active enrollments.", 422);
            }

            $courseId = $course->course_id;
            $deleted = $course->delete();

            if ($deleted) {
                // Clear course cache after deletion
                $this->clearCourseCache($course);

                Log::info("Course deleted", [
                    'course_id' => $courseId,
                    'title' => $course->title,
                ]);
            }

            return $deleted;
        } catch (Exception $e) {
            Log::error("Failed to delete course", [
                'course_id' => $course->course_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }


    /**
     * Update course rating.
     *
     * @param Course $course
     * @param float $newRating
     * @return Course
     */
    public function updateRating(Course $course, float $newRating): Course
    {
        $totalRatings = $course->total_ratings;
        $currentAverage = $course->average_rating;

        if ($totalRatings === 0) {
            // First rating
            $course->update([
                'average_rating' => $newRating,
                'total_ratings' => 1,
            ]);
        } else {
            // Calculate new average
            $newAverage = (($currentAverage * $totalRatings) + $newRating) / ($totalRatings + 1);

            $course->update([
                'average_rating' => round($newAverage, 2),
                'total_ratings' => $totalRatings + 1,
            ]);
        }

        return $course->fresh();
    }

    /**
     * Check if course is available for enrollment.
     *
     * @param Course $course
     * @return bool
     */
    public function isAvailableForEnrollment(Course $course): bool
    {
        // Course must be published
        if ($course->status !== CourseStatus::PUBLISHED->value || !$course->published_at) {
            return false;
        }

        // Course must not be deleted
        if ($course->trashed()) {
            return false;
        }

        // Course type must be active
        if ($course->courseType && !$course->courseType->is_active) {
            return false;
        }

        return true;
    }

    /**
     * Get course by ID.
     *
     * @param int $courseId
     * @return Course
     * @throws Exception
     */
    public function getById(int $courseId): Course
    {
        $course = Course::query()->find($courseId);

        if (!$course) {
            throw new Exception("Course not found.", 404);
        }

        return $course;
    }

    /**
     * Get course duration.
     *
     * @param Course $course
     * @return int Duration in hours
     */
    public function getDuration(Course $course): int
    {
        return $course->actual_duration_hours ?? 0;
    }

    /**
     * Clear course related cache.
     *
     * @param Course|null $course Optional course to clear specific cache
     * @return void
     */
    protected function clearCourseCache(?Course $course = null): void
    {
        if ($this->supportsCacheTags()) {
            // Use tags for efficient bulk invalidation
            $this->flushTags(['courses']);
            if ($course) {
                $this->flushTags(["course.{$course->course_id}"]);
            }
        } else {
            // Fallback to individual key deletion
            if ($course) {
                $this->forget("course.{$course->course_id}");
            }
            // Note: Without tags, we can't easily clear all courses.index.* keys
            // They will expire naturally based on TTL
        }
    }
}
