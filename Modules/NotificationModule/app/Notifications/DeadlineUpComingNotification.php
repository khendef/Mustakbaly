<?php

namespace Modules\NotificationModule\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent to students when an assessment/quiz deadline is approaching.
 * Stores payload in database and broadcasts it in realtime.
 */
class DeadlineUpcomingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param string $quizId The related quiz/assessment ID
     * @param string $dueAtIso Due date in ISO format
     * @param int $hoursLeft Hours remaining until due date
     */
    public function __construct(
        public string $quizId,
        public string $dueAtIso,
        public int $hoursLeft
    ) {}

    /**
     * Delivery channels.
     *
     * @param mixed $notifiable
     * @return array<int,string>
     */
    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Database payload.
     *
     * @param mixed $notifiable
     * @return array<string,mixed>
     */
    public function toDatabase($notifiable): array
    {
        return [
            'quiz_id'    => $this->quizId,
            'due_at'     => $this->dueAtIso,
            'hours_left' => $this->hoursLeft,

            'title' => [
                'ar' => 'موعد نهائي قادم',
                'en' => 'Upcoming deadline',
            ],
            'body' => [
                'ar' => "باقي {$this->hoursLeft} ساعة على تسليم التقييم.",
                'en' => "{$this->hoursLeft} hours left to submit the assessment.",
            ],

            'key' => "deadline:{$this->quizId}:{$this->hoursLeft}",
        ];
    }

    /**
     * Broadcast payload.
     *
     * @param mixed $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}