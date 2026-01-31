<?php

namespace Modules\NotificationModule\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notification sent to instructors when a student submits an assignment.
 *
 * Stores the attempt id and the student's name and is delivered via
 * the `database` and `broadcast` channels.
 */
class InstructorAssignmentSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The related attempt ID.
     *
     * @var int
     */
    public int $attemptId;

    /**
     * The submitting student's display name.
     *
     * @var string
     */
    public string $studentName;

    /**
     * Create a new notification instance.
     *
     * @param int $attemptId The ID of the attempt/submission
     * @param string $studentName The student's name
     */
    public function __construct(int $attemptId, string $studentName)
    {
        $this->attemptId = $attemptId;
        $this->studentName = $studentName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array<int,string>
     */
    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @param mixed $notifiable
     * @return array<string,mixed>
     */
    public function toDatabase($notifiable): array
    {
        return [
            'attempt_id' => $this->attemptId,
            'student_name' => $this->studentName,
            'title' => [
                'ar'=> 'تم تسليم الواجب',
                'en' => 'New assignment submitted',
            ],
            'body' => [
                'ar' => "قام الطالب {$this->studentName} بتسليم الواجب المطلوب منه.",
                'en' => "Student {$this->studentName} submitted the assignment required.",
            ]
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param mixed $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}

