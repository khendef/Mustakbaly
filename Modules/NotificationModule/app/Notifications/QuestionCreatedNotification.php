<?php
namespace Modules\NotificationModule\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\NotificationModule\DTO\QuestionNotificationData;

class QuestionCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private QuestionNotificationData $data;

    /**
     * Create a new notification instance.
     *
     * @param QuestionNotificationData $data The data for the notification
     */
    public function __construct(QuestionNotificationData $data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'question_id' => $this->data->questionId,
            'quiz_id'     => $this->data->quizId,
            'title'       => 'New Question Created',
            'body'        => $this->getQuestionText(),
        ];
    }

    /**
     * Prepare the broadcast message payload.
     *
     * @param mixed $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'New Question Created',
            'body'  => $this->getQuestionText(),
        ]);
    }

    /**
     * Get the question text in the appropriate language.
     *
     * @return string
     */
    private function getQuestionText(): string
    {
        return $this->data->questionText['ar'] ?? $this->data->questionText['en'] ?? 'New question created';
    }
}
