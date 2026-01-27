<?php

namespace Modules\AssesmentModule\Models;

use Modules\AssesmentModule\Models\Builders\QuestionOptionBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;
class QuestionOption extends Model
{
     use HasFactory;     use HasTranslations;
    protected $table = 'question_options';

    protected $fillable = [
        'question_id',
         'option_text',
         'is_correct'
       ];
    public array $translatable=['option_text'];

    protected $casts = ['is_correct' => 'boolean',
    ];

    public function question() {
        return $this->belongsTo(Question::class);
    }
    protected function optionText(): Attribute{
        return  Attribute::make(set: fn ($v) => trim((string)$v) );
    }
    public function  newEloquentBuilder($query):QuestionOptionBuilder
    {
        return new QuestionOptionBuilder($query);
    }

}

