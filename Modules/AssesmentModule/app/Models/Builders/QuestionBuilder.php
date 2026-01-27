<?php
namespace Modules\AssesmentModule\Models\Builders;

use Illuminate\Database\Eloquent\Builder;

class QuestionBuilder extends Builder{
    public function forQuiz(int $quizId):self{
        return $this->where('quiz_id',$quizId);
    }
    public function required():self{
        return $this->where('is_required',true);
    }
    public function ordered():self{
        return $this->orderBy('order_index');
    }
    public function filter(array $filters):self{
        return $this
        // Direct search
        ->when($filters['search'] ?? null,
        fn (Builder $q,$val) =>
            $q->where('question_text->en','like','%',(string)$val .'%')
        )
        //Relationship filters
        ->when(
            $filters['quiz_id'] ?? null,
            fn(Builder $q,$val) => $q->forQuiz((int)$val)
        )
        //Status Strategy (Dropdown Approach)
        ->when(
            $filters['type'] ?? null,
            fn(Builder $q,$val) => match((string)$val){
                'mcq' => $q->where('type','mcq'),
                'true_false' => $q->where('type','true_false'),
                'text' => $q->where('type','text'),
                default => $q
            }
        )
        //required strategy (Checkbox)
        ->when(
        filter_var($filters['is_required'] ?? null,
            FILTER_VALIDATE_BOOLEAN),
            fn (Builder $q) => $q->required()
        )

        //Ordering Strategy
        ->when(
            $filters['order'] ?? null,
            fn(Builder $q,$val) => match((string)$val){
                'asc' => $q->orderBy('order_index','asc'),
                'desc' => $q->orderBy('order_index','desc'),
                default => $q
            }
        );
    }
}
