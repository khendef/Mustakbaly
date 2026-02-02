<?php

namespace Modules\NotificationModule\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Facades\Broadcast;
use Modules\NotificationModule\DTO\NotificationData;

/**
 * Notification sent when a quiz attempt has been graded.
 *
 * Persists a database entry and broadcasts the result to the user.
 */
class QuizAttemptGraded extends Notification implements ShouldQueue
{
    use Queueable;

    protected NotificationData $data;

    /**
     * Create a new notification instance.
     *
     * @param NotificationData $data The notification data object
     */
    public function __construct(NotificationData $data)
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

    /**
     * Format the notification for database storage.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toDatabase($notifiable): array
    {
        return [
            'attempt_id' => $this->data->attemptId,
            'score' => $this->data->score,
            'is_passed' => $this->data->is_passed,
            'title' => 'Quiz Attempt Graded',
            'body' => "Your score: {$this->data->score}",
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
            'attempt_id' => $this->data->attemptId,
            'score' => $this->data->score,
            'is_passed' => $this->data->is_passed,
            'title' => 'Quiz Attempt Graded',
            'body' => "Your score: {$this->data->score}",
        ]);
    }
}