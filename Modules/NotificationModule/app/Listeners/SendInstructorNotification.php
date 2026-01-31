<?php

namespace Modules\NotificationModule\Listeners;

use Modules\AssesmentModule\Events\AttemptSubmitted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\UserManagementModule\Models\User;
use Modules\NotificationModule\Notifications\InstructorAssignmentSubmitted;
use Modules\UserManagementModule\Models\Scopes\OrganizationScope;

/**
 * Class SendInstructorNotification
 *
 * Listens for the AttemptSubmitted event and notifies
 * the instructor when a student submits an assignment.
 *
 * This listener is responsible only for reacting to the event
 * and sending notifications, keeping business logic decoupled.
 *
 * @package Modules\NotificationModule\Listeners
 */
class SendInstructorNotification implements ShouldQueue
{
    /**
     * Create a new listener instance.
     *
     * Dependencies may be injected here if required
     * (e.g., logging services or repositories).
     */
    public function __construct()
    {
    }

    /**
     * Handle the AttemptSubmitted event.
     *
     * When an attempt is submitted and the related quiz
     * is of type "assignment", the instructor associated
     * with the quiz is notified.
     *
     * @param AttemptSubmitted $event The event containing the submitted attempt.
     * @return void
     */
   public function handle(AttemptSubmitted $event): void
{
    $attempt = $event->attempt;

    // Guard: ensure attempt exists
    if (! $attempt) {
        return;
    }

    // Guard: ensure quiz exists and is assignment-type
    $quiz = $attempt->quiz;
    if (! $quiz || ($quiz->type ?? null) !== 'assignment') {
        return;
    }

    /*1.Get instructor via relation query so we can remove the org scope
    * 2.Make sure to import the real OrganizationScope class at top of file:
      use Modules\UserMangementModule\Scopes\OrganizationScope; (replace with your actual namespace)*/
    $instructor = $quiz->instructor()
        ->withoutGlobalScope(OrganizationScope::class)
        ->first();

    if (! $instructor) {
        return;
    }

    // Ensure student exists and has a name
    $studentName = $attempt->student->name ?? null;

    // If student name is required, guard for it; otherwise pass null or a fallback
    if (! $studentName) {
        // optional: log or return
        return;
    }

    // Create notification — use positional args if the notification expects them,
    // or named args if your project runs on PHP 8+ and the notification constructor supports named args.
    $instructor->notify(new InstructorAssignmentSubmitted(
        $attempt->id,
        $studentName
    ));
}
}