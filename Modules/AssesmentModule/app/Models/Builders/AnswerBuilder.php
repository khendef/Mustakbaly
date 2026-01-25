<?php
namespace Modules\AssesmentModule\Models\Builders;
use Illuminate\Database\Eloquent\Builder;
class AnswerBuilder extends Builder{
public function correct():self{
    return $this->where('is_correct',true);
   }
   /**'attempt_id',
        'question_id',
        'selected_option',
        'answer_text',
        'boolean_answer',
        'is_correct',
        'question_score',
        'graded_by',
        'graded_at',
    ]; */
public function filter(array $filters):self{
        return $this
        ->when(
            $filters['attempt_id'] ?? null,
            fn(Builder $q,$val) => $q->where('attempt_id',(int)$val)
        )
        ->when(
            $filters['question_id'] ?? null,
            fn(Builder $q,$val) => $q->where('question_id',(int)$val)
        )
         ->when(
            $filters['selected_option_id'] ?? null,
            fn(Builder $q,$val) => $q->where('selected_option_id',(int)$val)
        )
        ->when(
            $filters['answer_text'] ?? null,
            fn (Builder $q,$val) =>
                $q->where('answer_text->en','like','%',(string)$val .'%')
        )
        ->when(array_key_exists('is_correct',$filters),
        fn (Builder $q) => $q->where('is_correct',filter_var($filters['is_correct'],FILTER_VALIDATE_BOOLEAN))
        )
        ->when(
            $filters['min_score'] ?? null,
            fn(Builder $q,$val) => $q->where('question_score','>=',(int)$val )
        )
        ->when(
            $filters['max_score'] ?? null,
            fn(Builder $q,$val) => $q->where('question_score','<=',(int)$val )
        )
        ->when(array_key_exists('boolean_answer',$filters),
        fn (Builder $q) => $q->where('boolean_answer',filter_var($filters['boolean_answer'],FILTER_VALIDATE_BOOLEAN))
        )
        ->when(
            $filters['graded_at'] ?? null,
            fn(Builder $q,$val) => $q->whereDate('graded_at',(string)$val)
        )
        ->when(
            $filters['graded_by'] ?? null,
            fn(Builder $q,$val) => $q->where('graded_by',(int)$val)
        );
    }
}
