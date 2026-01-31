<?php

namespace Modules\NotificationModule\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\AssesmentModule\Models\Question;

class QuestionCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Question $question) {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database','broadcast'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'question_id' => $this->question->id,
            'quiz_id'     => $this->question->quiz_id,
            'title'       => 'تم إنشاء سؤال جديد',
            'body'        => $this->getQuestionText(),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => 'تم إنشاء سؤال جديد',
            'body'  => $this->getQuestionText(),
        ]);
    }

    private function getQuestionText(): string
    {
        return $this->question->question_text['ar']
            ?? $this->question->question_text['en']
            ?? 'سؤال جديد';
    }
}