<?php
namespace Modules\AssesmentModule\Models\Builders;
use Illuminate\Database\Eloquent\Builder;
class QuestionOptionBuilder extends Builder
{
   public function correct():self{
    return $this->where('is_correct',true);
   }
   public function filter(array $filters):self{
    return $this
    ->when(
        $filters['question_id'] ?? null,
        fn(Builder $q,$val) => $q->where('question_id',(int)$val)
    )
    ->when(
        filter_var($filters['is_correct'] ?? false,FILTER_VALIDATE_BOOLEAN),
        fn(Builder $q) => $q->correct()
        )
        ->when(
            $filters['option_text'] ?? null,
            fn (Builder $q,$val) =>
                $q->where('option_text->en','like','%',(string)$val .'%')
        );
   }
}
