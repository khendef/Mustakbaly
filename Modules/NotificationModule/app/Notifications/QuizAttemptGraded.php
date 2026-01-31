<?php

namespace Modules\NotificationModule\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Broadcast;

/**
 * Notification sent when a quiz attempt has been graded.
 *
 * Persists a database entry and broadcasts the result to the user.
 */
class QuizAttemptGraded extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param int $attemptId The ID of the quiz attempt
     * @param mixed $score The score achieved
     * @param bool $is_passed Whether the attempt passed
     */
    public function __construct(public int $attemptId, public int $score, public bool $is_passed) {}

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return string[]
     */
    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Format the notification for database storage.
     *
     * @param mixed $notifiable
     * @return array<string,mixed>
     */
   public function toDatabase($notifiable) :array{
        return [
            'attempt_id'=>$this->attemptId,
            'score'=>$this->score,
            'is_passed'=>$this->is_passed,
            'title' => 'Quiz Attempt Graded',
            'body' => "Your score:{$this->score},",
        ];
   }

    /**
     * Prepare the broadcast message payload.
     *
     * @param mixed $notifiable
     * @return BroadcastMessage
     */
   public function toBroadcast($notifiable):BroadcastMessage{
        return new BroadcastMessage(    [
            'attempt_id'=>$this->attemptId,
            'score'=>$this->score,
            'is_passed'=>$this->is_passed,
            'title' => 'Quiz Attempt Graded',
            'body' => "Your score:{$this->score},",
        ]);
   }
}
