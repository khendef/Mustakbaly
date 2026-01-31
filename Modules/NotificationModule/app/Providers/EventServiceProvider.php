<?php

namespace Modules\NotificationModule\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\AssesmentModule\Events\AssignmentPublished;
use Modules\AssesmentModule\Events\QuestionCreated;
use Modules\AssesmentModule\Events\AttemptSubmitted;
use Modules\NotificationModule\Listeners\SendInstructorNotification;
use Modules\NotificationModule\Listeners\SendQuestionCreated;
use Modules\AssesmentModule\Events\AttemptGraded;
use Modules\NotificationModule\Listeners\SendAssignmentPublishedToStudents;
use Modules\NotificationModule\Listeners\SendAttemptGradedNotification;


class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        QuestionCreated::class => [
            SendQuestionCreated::class,
        ],
        AttemptSubmitted::class => [
            SendInstructorNotification::class,
            ],
        AttemptGraded::class => [
            SendAttemptGradedNotification::class
        ],

       
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
