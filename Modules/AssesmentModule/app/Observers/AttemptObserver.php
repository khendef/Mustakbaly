<?php
/**
 * Modules\AssesmentModule\Observers\AttemptObserver
 *
 * Observes changes on the `Attempt` model and dispatches domain events
 * such as `AttemptGraded` and `AttemptSubmitted` when the attempt
 * status changes.
 *
 * @package Modules\AssesmentModule\Observers
 */
namespace Modules\AssesmentModule\Observers;

use Illuminate\Support\Facades\Log;
use Modules\AssesmentModule\Events\AttemptGraded;
use Modules\AssesmentModule\Events\AttemptSubmitted;
use Modules\AssesmentModule\Models\Attempt;

/**
 * Class AttemptObserver
 *
 * @package Modules\AssesmentModule\Observers
 */
class AttemptObserver
{
    /**
     * Handle the model "updated" event.
     *
     * When the `status` attribute changes this observer will dispatch a
     * domain event depending on the new status value.
     *
     * - `graded` -> dispatches \Modules\AssesmentModule\Events\AttemptGraded
     * - `submitted` -> dispatches \Modules\AssesmentModule\Events\AttemptSubmitted
     *
     * @param Attempt $attempt The attempt model instance that was updated.
     * @return void
     * @see \Modules\AssesmentModule\Events\AttemptGraded
     * @see \Modules\AssesmentModule\Events\AttemptSubmitted
     */
    public function updated(Attempt $attempt): void
    {
      /*  Log::info('AttemptObserver fired',[
          'attempt_id' => $attempt->id,
          'status'=> $attempt->status,
          'changed' =>$attempt->getChanges(),
        ]);*/
      if ($attempt->wasChanged('status') && $attempt->status === 'graded') {
            event(new AttemptGraded($attempt));
        } 

        if ($attempt->wasChanged('status') && $attempt->status === 'submitted') {
            event(new AttemptSubmitted($attempt));
        }
    }
}
