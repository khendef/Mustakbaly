<?php

namespace Modules\LearningModule\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\LearningModule\Builders\LessonBuilder;
use Spatie\Activitylog\LogOptions;

class Lesson extends Model
{
    /**
     * Represents a lesson within a unit in the e-learning platform.
     * Contains lesson content, metadata, and relationships, supporting various lesson types and soft deletion.
     */
    use SoftDeletes, LogsActivity;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'lesson_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'unit_id',
        'lesson_order',
        'title',
        'description',
        'lesson_type',
        'is_required',
        'is_completed',
        'actual_duration_minutes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_completed' => 'boolean',
            'deleted_at' => 'datetime',
            'lesson_order' => 'integer',
            'actual_duration_minutes' => 'integer',
        ];
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return LessonBuilder
     */
    public function newEloquentBuilder($query): LessonBuilder
    {
        return new LessonBuilder($query);
    }

    // Relationships

    /**
     * Get the unit that owns the lesson.
     *
     * @return BelongsTo
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'unit_id');
    }

    /**
     * Configure activity logging for Lesson model.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                return "Lesson '{$this->title}' was {$eventName}";
            });
    }
}
