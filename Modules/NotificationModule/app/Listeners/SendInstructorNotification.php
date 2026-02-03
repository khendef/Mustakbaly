<?php

namespace Modules\NotificationModule\Listeners;

use Modules\AssesmentModule\Events\AttemptSubmitted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\NotificationModule\DTO\AssignmentNotificationData;
use Modules\UserManagementModule\Models\User;
use Modules\NotificationModule\Notifications\InstructorAssignmentSubmitted;
use Modules\NotificationModule\Services\NotificationService;
use Modules\UserManagementModule\Models\Scopes\OrganizationScope;

/**
 * Class SendInstructorNotification
 *
 * Listens for the AttemptSubmitted event and notifies
 * the instructor when a student submits an assignment.
 *
 * This listener is responsible only for reacting to the event
 * and sending notifications, keeping business logic decoupled.
 *
 * @package Modules\NotificationModule\Listeners
 */
 class SendInstructorNotification implements ShouldQueue
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(AttemptSubmitted $event): void
    {
        $attempt = $event->attempt;

        if (!$attempt) {
            return;
        }

        $quiz = $attempt->quiz;
        if (!$quiz || ($quiz->type ?? null) !== 'assignment') {
            return;
        }

        $instructor = $quiz->instructor;
        if (!$instructor) {
            return;
        }

        $studentName = $attempt->student->name ?? 'Unknown Student';
        $notificationData = new AssignmentNotificationData(
            $attempt->id,
            $quiz->id,
            $studentName
        );
        $instructor->notify(new InstructorAssignmentSubmitted($notificationData));
    }
}