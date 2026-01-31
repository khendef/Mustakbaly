<?php

namespace Modules\AssesmentModule\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\AssesmentModule\Models\Builders\QuizBuilder;
use Modules\UserManagementModule\Models\Scopes\OrganizationScope;
use Modules\UserManagementModule\Models\User;
use Spatie\Translatable\HasTranslations;
class Quiz extends Model
{
     use HasFactory;
     use SoftDeletes;
     use HasTranslations;
     protected $table='quizzes';
     protected $fillable = [
        'course_id',
        'instructor_id',
        'quizable_id',
        'quizable_type',
        'type',
        'status',
        'title',
        'description',
        'max_score',
        'passing_score',
        'auto_grade_enabled',
        'available_from',
        'due_date',
        'duration_minutes',
    ];

    protected $casts = [
        'max_score' => 'integer',
        'passing_score' => 'integer',
        'available_from' => 'datetime',
        'due_date' => 'datetime',
        'auto_grade_enabled' => 'boolean',
        'duration_minutes' => 'integer',
    ];

    public array $translatable =['title','description'];

    public function course(){
        return $this->belongsTo(Course::class,'course_id');
    }

    public function instructor(){
        return $this->belongsTo(User::class,'instructor_id')->withoutGlobalScope(OrganizationScope::class);
    }
    public function quizable()
    {
        return $this->morphTo();
    }
    public function questions(){
        return $this->hasMany(Question::class)->orderBy('order_index');
    }
    public function attempts(){
        return $this->hasMany(Attempt::class,'quiz_id');
    }
    public function media(){
        return $this->morphMany(Media::class,'mediable');
    }
    protected function durationSecond():Attribute
    {
        return Attribute::make(
            get: fn () => $this->duration_minutes ? $this->duration_minutes * 60 : null
        );
    }
    protected function isPublished(): Attribute
    {
        return Attribute::make(get: fn () => $this->status === 'published');
    }
    public function newEloquentBuilder($query): QuizBuilder
    {
        return new QuizBuilder($query);
    }
    protected static function booted()
    {
        static::deleting(function (Quiz$quiz) {
          if($quiz->isForceDeleting()){
            return;
          }
          $quiz->questions()->delete();
        });
        static::restoring(function ($quiz) {
            $quiz->questions()->withTrashed()->restore();
        });
    }



}


