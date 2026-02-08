<?php

namespace Modules\LearningModule\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\AssesmentModule\Events\AttemptGraded;
use Modules\LearningModule\Services\EnrollmentService;

/**
 * Listener that calculates and updates final grade when a quiz attempt is graded.
 * 
 * This listener:
 * - Gets the enrollment for the student and course
 * - Calculates final grade based on all graded quiz attempts
 * - Only updates if course is completed (progress_percentage = 100%)
 */
class CalculateFinalGrade implements ShouldQueue
{
    use InteractsWithQueue;

    protected EnrollmentService $enrollmentService;

    /**
     * Create the event listener instance.
     *
     * @param EnrollmentService $enrollmentService
     */
    public function __construct(EnrollmentService $enrollmentService)
    {
        $this->enrollmentService = $enrollmentService;
    }

    /**
     * Handle the event.
     *
     * @param AttemptGraded $event
     * @return void
     */
    public function handle(AttemptGraded $event): void
    {
        try {
            $attempt = $event->attempt;

            if (!$attempt || !$attempt->quiz) {
                Log::warning("Attempt or quiz not found in CalculateFinalGrade listener", [
                    'attempt_id' => $attempt->id ?? null,
                ]);
                return;
            }

            $quiz = $attempt->quiz;
            $course = $quiz->course;

            if (!$course) {
                Log::warning("Course not found for quiz in CalculateFinalGrade listener", [
                    'quiz_id' => $quiz->id ?? null,
                    'attempt_id' => $attempt->id ?? null,
                ]);
                return;
            }

            // Get enrollment for this student and course
            $enrollment = $this->enrollmentService->getEnrollment($course, $attempt->student_id);

            if (!$enrollment) {
                Log::warning("Enrollment not found when calculating final grade", [
                    'student_id' => $attempt->student_id,
                    'course_id' => $course->course_id,
                    'attempt_id' => $attempt->id,
                ]);
                return;
            }

            // Calculate and update final grade
            $this->enrollmentService->calculateFinalGrade($enrollment);
        } catch (\Exception $e) {
            Log::error("Error in CalculateFinalGrade listener", [
                'attempt_id' => $event->attempt->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
