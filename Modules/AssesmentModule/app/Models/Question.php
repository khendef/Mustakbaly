<?php

namespace Modules\AssesmentModule\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;
use Modules\AssesmentModule\Models\Builders\QuestionBuilder;
class Question extends Model
{
     use HasFactory;
     use HasTranslations;

    protected $fillable=[
        'quiz_id',
        'type',
        'question_text',
        'point',
        'order_index',
        'is_required'
    ];
    protected $casts=[
        'point' => 'integer',
        'order_index' => 'integer',
        'is_required' => 'boolean',
        'question_text' => 'array',

    ];

    public array $translatable =['question_text'];


    public function quiz(){
        return $this->belongsTo(Quiz::class,'quiz_id');
    }
    public function options()
    {
        return $this->hasMany(QuestionOption::class,'question_id');
    }
    public function answers(){
        return $this->hasMany(Answer::class);
    }
    public function media(){
        return $this->morphMany(Media::class,'mediable');
    }
     protected function isMultiChoiceQuestion(): Attribute
    {
        return Attribute::make(get: fn () => $this->type === 'mcq');
    }
    protected function type(): Attribute
    {
        return Attribute::make(set: fn ($v) => is_string($v) ? strtolower(trim($v)) : $v);
    }
   /***********Builder */
    public function newEloquentBuilder($query):QuestionBuilder
    {
     return new QuestionBuilder($query);
    }


}

