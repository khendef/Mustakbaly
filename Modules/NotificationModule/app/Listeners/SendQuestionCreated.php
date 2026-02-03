<?php
namespace Modules\NotificationModule\Listeners;

use Modules\AssesmentModule\Events\QuestionCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
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
        try {
            $question = $event->question;

            // Ensure question text exists and is an array
            $questionText = $question->question_text['ar'] ?? $question->question_text['en'] ?? 'New question created';

            // Create the notification data
            $notificationData = new QuestionNotificationData(
                questionId: $question->id,
                quizId: $question->quiz_id,
                questionText: $questionText
            );

            // Send the notification
            $this->notificationService->sendQuestionCreatedNotification($notificationData);
        } catch (\Exception $e) {
            Log::error('Error while sending Question Created Notification', [
                'question_id' => $event->question->id,
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
