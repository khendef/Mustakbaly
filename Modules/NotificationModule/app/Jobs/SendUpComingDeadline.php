<?php

namespace Modules\NotificationModule\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Modules\AssesmentModule\Models\Quiz;
use Modules\NotificationModule\Notifications\DeadlineUpcomingNotification;
use Modules\UserManagementModule\Models\Student;

class SendUpcomingDeadline implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param int[] 
     */
    public function __construct(public array $hoursBefore = [24, 3]) {}

    public function handle(): void
    {
        $now = now();

        foreach ($this->hoursBefore as $h) {
            $from = $now->copy();
            $to   = $now->copy()->addHours($h);

            $quizzes = Quiz::withoutGlobalScopes()
                ->whereNotNull('due_at')
                ->whereBetween('due_at', [$from, $to])
                ->where('is_published', true)
                ->get();

            foreach ($quizzes as $quiz) {
                $students = $this->getStudentsForQuiz($quiz);

                foreach ($students as $student) {
                    $key = "deadline:{$quiz->id}:{$h}";

                    $exists = $student->notifications()
                        ->withoutGlobalScopes()
                        ->where('type', DeadlineUpcomingNotification::class)
                        ->where('data->key', $key)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $student->notify(new DeadlineUpcomingNotification(
                        quizId: (string) $quiz->id,
                        dueAtIso: Carbon::parse($quiz->due_at)->toISOString(),
                        hoursLeft: (int) $h
                    ));
                }
            }
        }
    }

    /**
     * Replace this method body with your real enrollment relation.
     *
     * @return Collection<int, Student>
     */
    private function getStudentsForQuiz($quiz): Collection
    {
        /**
         * Choose the correct one for your schema:
         *
         * If quiz belongs to course and course has students:
         * return $quiz->course->students()->withoutGlobalScopes()->get();
         *
         * If quiz has students directly:
         * return $quiz->students()->withoutGlobalScopes()->get();
         *
         */

        return collect();
    }
}
