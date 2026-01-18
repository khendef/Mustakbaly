<?php

namespace Modules\AssesmentModule\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Quiz extends Model
{
     use HasFactory;
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

    public function course(){
        return $this->belongsTo(Course::class,'course_id');
    }

    public function instructor(){
        return $this->belongsTo(User::class,'instructor_id');
    }
    public function quizable()
    {
        return $this->morphto();
    }
    public function questions(){
        return $this->hasMany(Question::class)->orderBy('order_index');
    }
    public function attempts(){
        return $this->hasMany(Attempt::class);
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

    protected function title(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => trim((string)$value)
        );
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'published');
    }

    public function scopeAvailableNow(Builder $q): Builder
    {
        return $q->where(function ($x) {
            $x->whereNull('available_from')->orWhere('available_from', '<=', now());
        })->where(function ($x) {
            $x->whereNull('due_date')->orWhere('due_date', '>=', now());
        });
    }
}


