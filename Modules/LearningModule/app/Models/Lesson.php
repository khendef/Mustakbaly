<?php

namespace Modules\LearningModule\Models;

use App\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\LearningModule\Builders\LessonBuilder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lesson extends Model implements HasMedia
{
    /**
     * Represents a lesson within a unit in the e-learning platform.
     * Contains lesson content, metadata, and relationships, supporting various lesson types and soft deletion.
     */
    use SoftDeletes, LogsActivity , InteractsWithMedia;

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
     * Get the enrollments that have completed this lesson.
     *
     * @return BelongsToMany
     */
    public function completedByEnrollments(): BelongsToMany
    {
        return $this->belongsToMany(Enrollment::class, 'enrollment_lesson', 'lesson_id', 'enrollment_id')
            ->withPivot(['completed_at'])
            ->withTimestamps();
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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');

        $this->addMediaCollection('video')
        ->singleFile()
        ->acceptsMimeTypes(['video/mp4', 'video/x-m4v', 'video/quicktime']);
    }
}
