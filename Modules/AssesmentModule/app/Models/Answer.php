<?php

namespace Modules\AssesmentModule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\AssesmentModule\Database\Factories\AnswerFactory;

class Answer extends Model
{
    use HasFactory;

     protected $fillable = [
        'attempt_id',
        'question_id',
        'selected_option',
        'answer_text',
        'boolean_answer',
        'is_correct',
        'question_score',
        'graded_by',
        'graded_at',
    ];

    protected $casts=[
    'boolean_answer' => 'boolean',
    'is_correct' => 'boolean',
    'graded_at' => 'datetime',
    'question_score' => 'integer'
    ];

    public function attempt(){
        return $this->belongsTo(Attempt::class);
    }
    public function question(){
        return $this->belongsTo(Question::class);
    }
    public function grader(){
        return $this->belongsTo(User::class,'graded_by');
    }

}
