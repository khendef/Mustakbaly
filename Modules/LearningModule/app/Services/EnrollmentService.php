<?php

namespace Modules\LearningModule\Services;

use App\Traits\CachesQueries;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\LearningModule\Enums\EnrollmentStatus;
use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\Enrollment;
use Modules\LearningModule\Models\Lesson;
use Modules\LearningModule\Models\Unit;

/**
 * Service class for managing enrollment business logic.
 * Handles enrollment creation, status management, progress tracking, and completion.
 */
class EnrollmentService
{
    use CachesQueries;
    /**
     * Course service instance.
     *
     * @var CourseService
     */
    protected CourseService $courseService;

    /**
     * Create a new enrollment service instance.
     *
     * @param CourseService $courseService
     */
    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    /**
     * Enroll a learner in a course.
     *
     * @param Course $course
     * @param int $learnerId
     * @param string $enrollmentType
     * @param int|null $enrolledBy
     * @return Enrollment
     * @throws \Exception
     */
    public function enroll(Course $course, int $learnerId, string $enrollmentType = 'self', ?int $enrolledBy = null): ?Enrollment
    {
        // Validate course is available for enrollment
        if (!$this->courseService->isAvailableForEnrollment($course)) {
            Log::warning("Attempted to enroll in course that is not available", [
                'course_id' => $course->course_id,
                'learner_id' => $learnerId,
            ]);
            return null;
        }

        // Check if already enrolled
        $existingEnrollment = Enrollment::where('learner_id', $learnerId)
            ->where('course_id', $course->course_id)
            ->first();

        if ($existingEnrollment) {
            // If enrollment exists but is dropped/suspended, reactivate it by updating status to active
            if (in_array($existingEnrollment->enrollment_status, [EnrollmentStatus::DROPPED->value, EnrollmentStatus::SUSPENDED->value])) {
                return $this->updateStatus($existingEnrollment, EnrollmentStatus::ACTIVE);
            }

            Log::warning("Attempted to enroll learner who is already enrolled", [
                'course_id' => $course->course_id,
                'learner_id' => $learnerId,
            ]);
            return null;
        }

        try {
            return DB::transaction(function () use ($course, $learnerId, $enrollmentType, $enrolledBy) {
                // Determine enrolled_by based on enrollment type
                // For self enrollment: learner enrolled themselves (use learner_id)
                // For assigned enrollment: use provided enrolledBy or Auth::id() (admin/user who assigned)
                $enrolledByValue = $enrollmentType === 'self'
                    ? $learnerId
                    : ($enrolledBy ?? Auth::id());

                $enrollment = Enrollment::create([
                    'learner_id' => $learnerId,
                    'course_id' => $course->course_id,
                    'enrollment_type' => $enrollmentType,
                    'enrollment_status' => EnrollmentStatus::ACTIVE->value,
                    'enrolled_by' => $enrolledByValue,
                    'enrolled_at' => now(),
                    'progress_percentage' => 0.00,
                ]);

                // Clear enrollment cache after creation
                $this->clearEnrollmentCache($learnerId, $course->course_id);

                Log::info("Enrollment created", [
                    'enrollment_id' => $enrollment->enrollment_id,
                    'learner_id' => $learnerId,
                    'course_id' => $course->course_id,
                    'enrollment_type' => $enrollmentType,
                    'enrolled_by' => $enrolledByValue,
                ]);

                return $enrollment;
            });
        } catch (ModelNotFoundException $e) {
            Log::error("Course not found for enrollment", [
                'course_id' => $course->course_id,
                'learner_id' => $learnerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error("Failed to enroll learner", [
                'course_id' => $course->course_id,
                'learner_id' => $learnerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Update enrollment fields.
     *
     * @param Enrollment $enrollment
     * @param array $data
     * @return Enrollment|null
     * @throws \Exception
     */
    public function update(Enrollment $enrollment, array $data): ?Enrollment
    {
        try {
            $enrollment->update($data);

            // Clear enrollment cache after update
            $this->clearEnrollmentCache($enrollment->learner_id, $enrollment->course_id);

            Log::info("Enrollment updated", [
                'enrollment_id' => $enrollment->enrollment_id,
                'updated_fields' => array_keys($data),
            ]);

            return $enrollment->fresh();
        } catch (\Exception $e) {
            Log::error("Failed to update enrollment", [
                'enrollment_id' => $enrollment->enrollment_id,
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Update enrollment status.
     *
     * @param Enrollment $enrollment
     * @param EnrollmentStatus $status
     * @return Enrollment
     * @throws \Exception
     */
    public function updateStatus(Enrollment $enrollment, EnrollmentStatus $status): ?Enrollment
    {
        try {
            $oldStatus = $enrollment->enrollment_status;

            $updateData = ['enrollment_status' => $status->value];

            // Handle completion
            if ($status === EnrollmentStatus::COMPLETED && !$enrollment->completed_at) {
                $updateData['completed_at'] = now();
                // $updateData['progress_percentage'] = 100.00;
            }

            // Clear completion date if moving away from completed
            if ($oldStatus === EnrollmentStatus::COMPLETED && $status !== EnrollmentStatus::COMPLETED) {
                $updateData['completed_at'] = null;
            }

            $enrollment->update($updateData);

            // Clear enrollment cache after status update
            $this->clearEnrollmentCache($enrollment->learner_id, $enrollment->course_id);

            Log::info("Enrollment status updated", [
                'enrollment_id' => $enrollment->enrollment_id,
                'old_status' => $oldStatus->value,
                'new_status' => $status->value,
            ]);

            return $enrollment->fresh();
        } catch (\Exception $e) {
            Log::error("Failed to update enrollment status", [
                'enrollment_id' => $enrollment->enrollment_id,
                'status' => $status->value,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Calculate and update enrollment progress.
     *
     * @param Enrollment $enrollment
     * @return float
     * @throws \Exception
     */
    public function calculateProgress(Enrollment $enrollment): ?float
    {
        $course = $enrollment->course;

        if (!$course) {
            Log::warning("Course not found for enrollment when calculating progress", [
                'enrollment_id' => $enrollment->enrollment_id,
            ]);
            return null;
        }

        try {
            // Get total lessons count
            $totalLessons = $this->getTotalLessonsCount($course);

            if ($totalLessons === 0) {
                // If no lessons, progress is 0
                $progress = 0.00;
            } else {
                // Get completed lessons count
                $completedLessons = $this->getCompletedLessonsCount($course);

                $progress = round(($completedLessons / $totalLessons) * 100, 2);
            }

            // Update enrollment progress
            $enrollment->update(['progress_percentage' => $progress]);

            // Check if all lessons are completed and handle completion
            $this->checkAndHandleCourseCompletion($enrollment);

            Log::info("Enrollment progress calculated", [
                'enrollment_id' => $enrollment->enrollment_id,
                'progress_percentage' => $progress,
                'completed_lessons' => $completedLessons,
                'total_lessons' => $totalLessons,
            ]);

            return $progress;
        } catch (\Exception $e) {
            Log::error("Failed to calculate enrollment progress", [
                'enrollment_id' => $enrollment->enrollment_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Get total lessons count for a course.
     *
     * @param Course $course
     * @return int
     */
    protected function getTotalLessonsCount(Course $course): int
    {
        return Lesson::whereHas('unit', function ($query) use ($course) {
            $query->where('course_id', $course->course_id);
        })->count();
    }

    /**
     * Get completed lessons count for an enrollment.
     * Counts lessons where is_completed is true for the course.
     *
     * @param Enrollment $enrollment
     * @return int
     */
    protected function getCompletedLessonsCount(Course $course): int
    {
        return Lesson::whereHas('unit', function ($query) use ($course) {
            $query->where('course_id', $course->course_id);
        })->where('is_completed', true)->count();
    }

    /**
     * Check if learner is enrolled in course.
     *
     * @param Course|int $course
     * @param int $learnerId
     * @return bool
     */
    public function isEnrolled($course, int $learnerId): bool
    {
        // to handle the case where the course is an integer or a course model
        $courseId = is_int($course) ? $course : $course->course_id;
        $cacheKey = "enrollment.check.{$learnerId}.{$courseId}";

        return $this->remember($cacheKey, 300, function () use ($learnerId, $courseId) {
            return Enrollment::where('learner_id', $learnerId)
                ->where('course_id', $courseId)
                ->where('enrollment_status', EnrollmentStatus::ACTIVE->value)
                ->exists();
        }, ['enrollments', "learner.{$learnerId}", "course.{$courseId}"]);
    }

    /**
     * Get enrollment for learner and course.
     *
     * @param Course|int $course
     * @param int $learnerId
     * @return Enrollment|null
     */
    public function getEnrollment($course, int $learnerId): ?Enrollment
    {
        // to handle the case where the course is an integer or a course model
        $courseId = is_int($course) ? $course : $course->course_id;
        $cacheKey = "enrollment.{$learnerId}.{$courseId}";

        return $this->remember($cacheKey, 300, function () use ($learnerId, $courseId) {
            return Enrollment::where('learner_id', $learnerId)
                ->where('course_id', $courseId)
                ->first();
        }, ['enrollments', "learner.{$learnerId}", "course.{$courseId}"]);
    }

    /**
     * Get active enrollments for a course.
     *
     * @param Course $course
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveEnrollmentsForCourse(Course $course)
    {
        return Enrollment::where('course_id', $course->course_id)
            ->where('enrollment_status', EnrollmentStatus::ACTIVE->value)
            ->with(['learner', 'enrolledBy'])
            ->orderBy('enrolled_at', 'desc')
            ->get();
    }

    /**
     * Get active enrollments for a learner.
     *
     * @param int $learnerId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveEnrollmentsForLearner(int $learnerId)
    {
        $cacheKey = "enrollments.learner.{$learnerId}.active";

        return $this->remember($cacheKey, 600, function () use ($learnerId) {
            return Enrollment::where('learner_id', $learnerId)
                ->where('enrollment_status', EnrollmentStatus::ACTIVE->value)
                ->with(['course', 'enrolledBy'])
                ->orderBy('enrolled_at', 'desc')
                ->get();
        }, ['enrollments', "learner.{$learnerId}"]);
    }

    /**
     * Clear enrollment related cache.
     * Uses Redis tags for efficient bulk invalidation.
     *
     * @param int $learnerId
     * @param int $courseId
     * @return void
     */
    protected function clearEnrollmentCache(int $learnerId, int $courseId): void
    {
        // Use Redis tags for efficient bulk invalidation
        $this->flushTags(["learner.{$learnerId}", "course.{$courseId}"]);
    }

    /**
     * Get detailed progress information for an enrollment.
     *
     * @param Enrollment $enrollment
     * @return array
     * @throws \Exception
     */
    public function getProgressDetails(Enrollment $enrollment): ?array
    {
        $course = $enrollment->course;

        if (!$course) {
            Log::warning("Course not found for enrollment when getting progress details", [
                'enrollment_id' => $enrollment->enrollment_id,
            ]);
            return null;
        }

        try {
            $totalUnits = Unit::where('course_id', $course->course_id)->count();
            $totalLessons = $this->getTotalLessonsCount($course);
            $completedLessons = $this->getCompletedLessonsCount($course);


            return [
                'enrollment_id' => $enrollment->enrollment_id,
                'progress_percentage' => (float)$enrollment->progress_percentage,
                'total_units' => $totalUnits,
                'total_lessons' => $totalLessons,
                'completed_lessons' => $completedLessons,
                'remaining_lessons' => max(0, $totalLessons - $completedLessons),
                'is_completed' => $enrollment->enrollment_status === EnrollmentStatus::COMPLETED,
            ];
        } catch (\Exception $e) {
            Log::error("Failed to get progress details", [
                'enrollment_id' => $enrollment->enrollment_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Check if all lessons in a course are completed and update enrollment accordingly.
     *
     * @param Enrollment $enrollment
     * @return void
     * @throws \Exception
     */
    public function checkAndHandleCourseCompletion(Enrollment $enrollment): void
    {
        $course = $enrollment->course;

        if (!$course) {
            Log::warning("Course not found for enrollment when checking completion", [
                'enrollment_id' => $enrollment->enrollment_id,
            ]);
            return;
        }

        try {
            $totalLessons = $this->getTotalLessonsCount($course);
            $completedLessons = $this->getCompletedLessonsCount($course);

            // If all lessons are completed, update progress to 100% and set completed_at
            if ($totalLessons > 0 && $completedLessons === $totalLessons) {
                // Update progress to 100%
                $enrollment->update(['progress_percentage' => 100.00]);

                // Set completed_at if not already set
                // Note: We don't check exams/assignments here as per user's request
                if (!$enrollment->completed_at) {
                    $enrollment->update(['completed_at' => now()]);
                }

                // Update enrollment status to completed if not already
                if ($enrollment->enrollment_status !== EnrollmentStatus::COMPLETED->value) {
                    $this->updateStatus($enrollment, EnrollmentStatus::COMPLETED);
                }

                Log::info("Course completion handled for enrollment", [
                    'enrollment_id' => $enrollment->enrollment_id,
                    'course_id' => $course->course_id,
                    'total_lessons' => $totalLessons,
                    'completed_lessons' => $completedLessons,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to check course completion", [
                'enrollment_id' => $enrollment->enrollment_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
