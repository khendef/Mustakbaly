<?php

namespace Modules\NotificationModule\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\NotificationModule\DTO\AssignmentNotificationData;

/**
 * Notification sent to instructors when a student submits an assignment.
 *
 * Stores the attempt id and the student's name and is delivered via
 * the `database` and `broadcast` channels.
 */
class InstructorAssignmentSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public int $attemptId;
    public string $studentName;
    public int $quizId;

    /**
     * Create a new notification instance.
     *
     * @param AssignmentNotificationData $data
     */
    public function __construct(AssignmentNotificationData $data)
    {
        $this->attemptId = $data->attemptId;
        $this->quizId = $data->quizId;
        $this->studentName = $data->studentName;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'attempt_id' => $this->attemptId,
            'student_name' => $this->studentName,
            'title' => [
                'ar' => 'تم تسليم الواجب',
                'en' => 'New assignment submitted',
            ],
            'body' => [
                'ar' => "الطالب {$this->studentName} قد قام بتسليم الواجب.",
                'en' => "The student {$this->studentName} has submitted the assignment.",
            ],
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}