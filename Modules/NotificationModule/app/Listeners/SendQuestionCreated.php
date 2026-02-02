<?php
namespace Modules\NotificationModule\Listeners;
/**
 * Listener: SendQuestionCreated
 *
 * Handles sending a notification when a Question is created.
 *
 * @package Modules\NotificationModule\Listeners
 */

use Modules\AssesmentModule\Events\QuestionCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\NotificationModule\DTO\QuestionNotificationData;
use Modules\NotificationModule\Services\NotificationService;

class SendQuestionCreated implements ShouldQueue
{
    use InteractsWithQueue;

    protected NotificationService $notificationService;

    /**
     * Create the listener instance.
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
     * @param QuestionCreated $event
     * @return void
     */
    public function handle(QuestionCreated $event): void
    {
        $question = $event->question;

        // Prepare data for notification
        $notificationData = new QuestionNotificationData(
            questionId: $question->id,
            quizId: $question->quiz_id,
            questionText: $question->question_text['ar'] ?? $question->question_text['en']
        );

        // Send notification
        $this->notificationService->sendQuestionCreatedNotification($notificationData);
    }

}