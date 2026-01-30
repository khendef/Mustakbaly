<?php
namespace Modules\AssesmentModule\Models\Builders;
use Illuminate\Database\Eloquent\Builder;
class QuizBuilder extends Builder
{
  /*  public function withQuestionsCount(): self
    {
        return $this->withCount('questions');
    }*/
    public function published(): self
    {
        return $this->where('status', 'published');
    }
    public function draft(): self
    {
        return $this->where('status', 'draft');
    }
    public function availableNow():self{
        return $this->where(function($q){
            $q->whereNull('available_from')
              ->orWhere('available_from','<=',now());
        })->where(function($q){
            $q->whereNull('due_date')
              ->orWhere('due_date','>=',now());
        });

    }
    public function forCourse(int $courseId):self{
        return $this->where('course_id',$courseId);
    }
    public function forInstructor(int $instructorId):self{
        return $this->where('instructor_id',$instructorId);
    }

    public function filter(array $filters){
         return $this
         /****filter by Course */
        ->when(
            $filters['course_id'] ?? null,
            fn(Builder $q,$val) => $q->forCourse((int)$val)
        )
        /*****filter by instructor */
        ->when(
            $filters['instructor_id'] ?? null,
            fn(Builder $q,$val) => $q->forInstructor((int)$val)
        )
        ///************filter by type */
        ->when(
            $filters['type'] ?? null,
            fn(Builder $q,$val) => match((string)$val){
                'quiz' => $q->where('type','quiz'),
                'assignment' => $q->where('type','assignment'),
                'practice' => $q->where('type','practice'),
                default => $q
            }
        )
        /*********filter by Status Strategy */
         ->when(
            $filters['status'] ?? null,
            fn(Builder $q,$val) => match ((string) $val){
                'published' => $q->where('status','published'),
                'draft' => $q->where('status','draft'),
                default => $q
            }
         )
        //Ordering Strategy
        ->when(
            $filters['order'] ?? null,
            fn(Builder $q,$val) => match((string)$val){
                'latest' => $q->latest('id'),
                'oldest' => $q->oldest('id'),
                default => $q
            }
        )
        ->when(
        filter_var($filter['available_now'] ?? false, FILTER_VALIDATE_BOOLEAN),
        fn(Builder $q) => $q->availableNow()
        );
    }
}

