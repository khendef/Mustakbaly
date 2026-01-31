<?php
/**
 * Modules\AssesmentModule\Observers\QuestionObserver
 *
 * Observes `Question` model events and dispatches domain events
 * such as `QuestionCreated` when appropriate.
 *
 * @package Modules\AssesmentModule\Observers
 */
namespace Modules\AssesmentModule\Observers;

use Modules\AssesmentModule\Events\QuestionCreated;
use Modules\AssesmentModule\Models\Question;

/**
 * Class QuestionObserver
 *
 * @package Modules\AssesmentModule\Observers
 */
class QuestionObserver
{
    /**
     * Handle the `created` event for the `Question` model.
     *
     * Dispatches a `QuestionCreated` event with the created model.
     *
     * @param Question $question The question model instance that was created.
     * @return void
     * @see \Modules\AssesmentModule\Events\QuestionCreated
     */
    public function created(Question $question): void
    {
        event(new QuestionCreated($question));
    }

}
