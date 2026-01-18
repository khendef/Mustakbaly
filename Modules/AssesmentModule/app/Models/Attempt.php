<?php

namespace Modules\AssesmentModule\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Attempt extends Model
{
     use HasFactory;
    protected $fillable = [
        'quiz_id',
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
        'time_spent_seconds'
    ];

    protected $casts = [
        'score' => 'integer',
        'attempt_number' => 'integer',
        'start_at' => 'datetime',
        'ends_at' => 'datetime',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'is_passed' => 'boolean',
        'time_spent_seconds' => 'integer'
    ];

    public function quiz(){
        return $this->belongsTo(Quiz::class);
    }
    public function student(){
        return $this->belongsTo(User::class,'student_id');
    }
    public function answer(){
        return $this->hasMany(Answer::class);
    }
    public function grader(){
        return $this->belongsTo(User::class, 'graded_by');
    }
    public function media() {
        return $this->morphMany(Media::class,'mediable');
    }

    protected function remainingSeconds():Attribute{
        return Attribute::make(
       get: function () {
        if( !$this->ends_at) return null;
        return max(0,now()->diffInSeconds($this->ends_at,false));
       });
    }
    public function isTimeUp():Attribute{
        return Attribute::make(get: fn() => $this->ends_at ? now()->gte($this->ends_at): false);
    }
    protected function timeSpentSecondsComputed():Attribute{
        return Attribute::make(get: function(){
            $end = $this->submitted_at ?? now();
            return $this->start_at ? max(0 , $this->start_at->diffInSeconds($end)) :  0;
        });
    }

    public function scopeActive(Builder $q)
    {
    return $q->where('status','in_progress');
    }
    public function scopeInProgress(Builder $q): Builder {
        return $q->where('status','in_progress');
    }
    public function scopeSubmitted(Builder $q): Builder{
        return $q->where('status','submitted');
    }
    public function scopeGraded(Builder $q) : Builder{
       return $q->where('status','graded');
    }

}
