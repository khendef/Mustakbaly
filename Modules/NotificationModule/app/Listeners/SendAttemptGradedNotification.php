<?php

namespace Modules\NotificationModule\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\AssesmentModule\Events\AttemptGraded;
use Modules\NotificationModule\Notifications\QuizAttemptGraded;


/**
 * Listener that sends the graded attempt notification to the student.
 *
 * This listener implements `ShouldQueue` so sending the notification
 * can be performed asynchronously by the queue worker.
 */
class SendAttemptGradedNotification implements ShouldQueue
{
    /**
     * Create the event listener instance.
     *
     * @return void
     */
    public function __construct()
    {
        // dependencies may be injected here
    }

    /**
     * Handle the AttemptGraded event.
     *
     * @param \Modules\AssesmentModule\Events\AttemptGraded $event The event containing the graded attempt
     * @return void
     */
    public function handle(AttemptGraded $event): void
    {
        $attempt = $event->attempt;

        $attempt->student->notify(new QuizAttemptGraded(
            attemptId: $attempt->id,
            score: (int) $attempt->score,
            is_passed: (bool) $attempt->is_passed,
        ));
    }
}
