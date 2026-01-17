<?php

namespace Modules\LearningModule\Services;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\CourseInstructor;

/**
 * Service class for managing course-instructor relationships.
 * Handles instructor assignment, removal, and primary instructor management.
 */
class CourseInstructorService
{
    /**
     * Assign an instructor to a course.
     *
     * @param Course $course
     * @param int $instructorId
     * @param bool $isPrimary
     * @param int|null $assignedBy
     * @return CourseInstructor
     * @throws \Exception
     */
    public function assign(
        Course $course,
        int $instructorId,
        bool $isPrimary = false,
        ?int $assignedBy = null
    ): CourseInstructor {
        try {
            // Check if instructor is already assigned
            $existingAssignment = CourseInstructor::where('course_id', $course->course_id)
                ->where('instructor_id', $instructorId)
                ->first();

            if ($existingAssignment) {
                throw new \Exception("Instructor is already assigned to this course.", 422);
            }

            $assignment = CourseInstructor::create([
                'course_id' => $course->course_id,
                'instructor_id' => $instructorId,
                'is_primary' => $isPrimary,
                'assigned_by' => $assignedBy ?? Auth::id(),
                'assigned_at' => now(),
            ]);

            Log::info("Instructor assigned to course", [
                'course_id' => $course->course_id,
                'instructor_id' => $instructorId,
                'is_primary' => $isPrimary,
                'assigned_by' => $assignedBy ?? Auth::id(),
            ]);

            return $assignment;
        } catch (\Exception $e) {
            Log::error("Failed to assign instructor", [
                'course_id' => $course->course_id,
                'instructor_id' => $instructorId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Remove an instructor from a course.
     *
     * @param Course $course
     * @param int $instructorId
     * @return bool
     * @throws \Exception
     */
    public function remove(Course $course, int $instructorId): bool
    {
        try {
            $instructorCount = $course->instructors()->count();

            if ($instructorCount <= 1) {
                throw new \Exception("Cannot remove the last instructor from the course.", 422);
            }

            // find and delete the instructor from the course not need to load model
            $deleted = CourseInstructor::where('course_id', $course->course_id)
                ->where('instructor_id', $instructorId)
                ->delete();

            if ($deleted > 0) {
                Log::info("Instructor removed from course", [
                    'course_id' => $course->course_id,
                    'instructor_id' => $instructorId,
                ]);
            }

            return $deleted > 0;
        } catch (\Exception $e) {
            Log::error("Failed to remove instructor", [
                'course_id' => $course->course_id,
                'instructor_id' => $instructorId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Set an instructor as the primary instructor.
     *
     * @param Course $course
     * @param int $instructorId
     * @return CourseInstructor
     * @throws \Exception
     */
    public function setPrimary(Course $course, int $instructorId): CourseInstructor
    {
        try {
            $assignment = CourseInstructor::where('course_id', $course->course_id)
                ->where('instructor_id', $instructorId)
                ->first();

            if (!$assignment) {
                throw new \Exception("Instructor is not assigned to this course.", 404);
            }

            // Set this instructor as primary (without unsetting others)
            $assignment->update(['is_primary' => true]);

            Log::info("Instructor set as primary", [
                'course_id' => $course->course_id,
                'instructor_id' => $instructorId,
            ]);

            return $assignment->fresh();
        } catch (\Exception $e) {
            Log::error("Failed to set primary instructor", [
                'course_id' => $course->course_id,
                'instructor_id' => $instructorId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Unset primary flag for instructors of a course.
     * This is an explicit action - call this when you want to unset primary instructors.
     *
     * @param Course $course
     * @param int|null $excludeInstructorId Optional instructor ID to exclude from unsetting
     * @return int Number of instructors unset
     */
    public function unsetPrimaryInstructors(Course $course, ?int $excludeInstructorId = null): int
    {
        $query = CourseInstructor::where('course_id', $course->course_id)
            ->where('is_primary', true);

        if ($excludeInstructorId) {
            $query->where('instructor_id', '!=', $excludeInstructorId);
        }

        return $query->update(['is_primary' => false]);
    }

    /**
     * Unset primary flag for a specific instructor.
     *
     * @param Course $course
     * @param int $instructorId
     * @return bool
     */
    public function unsetPrimary(Course $course, int $instructorId): bool
    {
        $updated = CourseInstructor::where('course_id', $course->course_id)
            ->where('instructor_id', $instructorId)
            ->where('is_primary', true)
            ->update(['is_primary' => false]);

        return $updated > 0;
    }

    /**
     * Check if instructor is assigned to course.
     *
     * @param Course $course
     * @param int $instructorId
     * @return bool
     */
    public function isAssigned(Course $course, int $instructorId): bool
    {
        return CourseInstructor::where('course_id', $course->course_id)
            ->where('instructor_id', $instructorId)
            ->exists();
    }

    /**
     * Get the primary instructor for a course.
     *
     * @param Course $course
     * @return CourseInstructor|null
     */
    public function getPrimaryInstructor(Course $course): ?CourseInstructor
    {
        return CourseInstructor::where('course_id', $course->course_id)
            ->where('is_primary', true)
            ->first();
    }

    /**
     * Get all instructors for a course.
     *
     * @param Course $course
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getInstructors(Course $course)
    {
        return CourseInstructor::where('course_id', $course->course_id)
            ->with(['instructor', 'assignedBy'])
            ->orderBy('is_primary', 'desc')
            ->orderBy('assigned_at', 'asc')
            ->get();
    }

    /**
     * Get count of instructors for a course.
     *
     * @param Course $course
     * @return int
     */
    public function getInstructorCount(Course $course): int
    {
        return $course->instructors()->count();
    }

    /**
     * Check if course has at least one instructor.
     *
     * @param Course $course
     * @return bool
     */
    public function hasInstructors(Course $course): bool
    {
        return $this->getInstructorCount($course) > 0;
    }
}
