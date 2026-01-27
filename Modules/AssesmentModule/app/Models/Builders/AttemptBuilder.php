<?php

namespace Modules\AssesmentModule\Models\Builders;
use Illuminate\Database\Eloquent\Builder;
class AttemptBuilder extends Builder{
    public function inProgress():AttemptBuilder{
        return $this->where('status','in_progress');
    }
    public function submitted():AttemptBuilder{
        return $this->where('status','submitted');
    }
    public function graded():AttemptBuilder{
        return $this->where('status','graded');
    }
   /* 'quiz_id',
        'student_id',
        'attempt_number',
        'status',
        'score',
        'is_passed',
        'start_at',
        'ends_at',
        'submitted_at',
        'graded_at',
        'graded_by',
        'time_spent_seconds'*/
    public function filter(array $filters):self{
        return $this
        ->when(
            $filters['quiz_id'] ?? null,
            fn(Builder $q,$val) => $q->where('quiz_id',(int)$val)
        )
        ->when(
            $filters['student_id'] ?? null,
            fn(Builder $q,$val) => $q->where('student_id',(int)$val)
        )
        ->when(
            $filters['status'] ?? null,
            fn(Builder $q,$val) => match ((string)$val){
                'in_progress' => $q->inProgress(),
                'submitted' => $q->submitted(),
                'graded' => $q->graded(),
                default => $q
            }
        )
        ->when(array_key_exists('is_passed',$filters),
        fn (Builder $q) => $q->where('is_passed',filter_var($filters['is_passed'],FILTER_VALIDATE_BOOLEAN))
        )
        ->when(
            $filters['graded_by'] ?? null,
            fn(Builder $q,$val) => $q->where('graded_by',(int)$val)
        )
        ->when(
            $filters['attempt_number'] ?? null,
            fn(Builder $q,$val) => $q->where('attempt_number',(int)$val )
        )
        ->when(
            $filters['min_score'] ?? null,
            fn(Builder $q,$val) => $q->where('score','>=',(int)$val )
        )
        ->when(
            $filters['max_score'] ?? null,
            fn(Builder $q,$val) => $q->where('score','<=',(int)$val )
        )
        ->when(
            $filters['start_at'] ?? null,
            fn(Builder $q,$val) => $q->whereDate('start_at','>=',$val)
        )
        ->when(
            $filters['ends_at'] ?? null,
            fn(Builder $q,$val) => $q->whereDate('ends_at','<=',$val)
        )
        ->when(
            $filters['submitted_from'] ?? null,
             fn(Builder $q,$val) => $q->whereDate('submitted_at','<=',$val)
        )
        ->when(
            $filters['submitted_to'] ?? null,
             fn(Builder $q,$val) => $q->whereDate('submitted_at','>=',$val)
        )
        ->when(
            $filters['graded_at'] ?? null,
            fn(Builder $q,$val) => $q->whereDate('graded_at','<=',$val)
        )
        ->when(
            $filters['min_time_spent'] ?? null,
            fn(Builder $q,$val) => $q->where('time_spent_seconds','>=',(int)$val )
        )
        ->when(
            $filters['max_time_spent'] ?? null,
            fn(Builder $q,$val) => $q->where('time_spent_seconds','<=',(int)$val )
        )
        ->when(
            $filters['order'] ?? null,
            fn(Builder $q,$val) => match((string)$val){
                'latest' => $q->orderByDesc('id'),
                'oldest' => $q->orderByAsc('id'),
                default => $q
            }
        );

    }
}
