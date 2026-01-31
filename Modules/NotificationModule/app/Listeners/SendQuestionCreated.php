<?php
/**
 * Listener: SendQuestionCreated
 *
 * Handles sending a notification when a Question is created.
 *
 * @package Modules\NotificationModule\Listeners
 */

namespace Modules\NotificationModule\Listeners;

use Modules\AssesmentModule\Events\QuestionCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\NotificationModule\Notifications\QuestionCreatedNotification;
use Modules\UserManagementModule\Models\Scopes\OrganizationScope;

class SendQuestionCreated implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param  QuestionCreated  $event
     * @return void
     */
    public function handle(QuestionCreated $event): void
    {
        $question = $event->question;

        // Guard: ensure we actually have a question and quiz
        if (! $question) {
            return;
        }

        $quiz = $question->quiz;
        if (! $quiz) {
            return;
        }

        // Use the relation method to get a query builder so we can call withoutGlobalScopes()
        $instructor = $quiz->instructor()->withoutGlobalScope(OrganizationScope::class)->first();

        if ($instructor) {
            $instructor->notify(new QuestionCreatedNotification($question));
        }
    }
}