<?php

namespace Modules\AssesmentModule\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class QuestionOption extends Model
{
     use HasFactory;
    protected $table = 'question_options';

    protected $fillable = [
        'question_id',
         'option_text',
         'is_correct'
       ];

    protected $casts = ['is_correct' => 'boolean'];

    public function question() {
        return $this->belongsTo(Question::class);
    }
    protected function optinText(): Attribute{
        return  Attribute::make(set: fn ($v) => trim((string)$v) );
    }

    public function scopeCorrect(Builder $q): Builder
    {
        return $q->where('is_correct', true);
    }
}

