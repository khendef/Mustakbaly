<?php
namespace Modules\NotificationModule\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\NotificationModule\DTO\NotificationData;
use Modules\AssesmentModule\Events\AttemptGraded;
use Modules\NotificationModule\Services\NotificationService;

/**
 * Listener that sends the graded attempt notification to the student.
 */
class SendAttemptGradedNotification implements ShouldQueue
{
    protected $notificationService;

    /**
     * Create the event listener instance.
     *
     * @param NotificationService $notificationService
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     *
     * @param AttemptGraded $event
     * @return void
     */
    public function handle(AttemptGraded $event)
    {
        //create DTO
        $notificationData = new NotificationData(
            attemptId: $event->attempt->id,
            score: $event->attempt->score,
            is_passed: $event->attempt->is_passed,
            studentId: $event->attempt->student_id
        );
        //send notification with service
        $this->notificationService->sendQuizAttemptGradedNotification($notificationData);
    }
}
