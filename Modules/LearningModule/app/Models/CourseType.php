<?php

namespace Modules\LearningModule\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\LearningModule\Builders\CourseTypeBuilder;
use Spatie\Activitylog\LogOptions;

class CourseType extends Model
{
    use LogsActivity;

    /**
     * Represents a course type or category in the e-learning platform.
     * Defines the classification and characteristics of courses, such as certification programs, workshops, or online courses.
     */

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'course_type_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'target_audience',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Relationships

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return CourseTypeBuilder
     */
    public function newEloquentBuilder($query): CourseTypeBuilder
    {
        return new CourseTypeBuilder($query);
    }

    /**
     * Get the courses for this course type.
     *
     * @return HasMany
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'course_type_id', 'course_type_id');
    }

    /**
     * Configure activity logging for CourseType model.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'slug',
                'description',
                'is_active',
                'target_audience',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                return "Course type '{$this->name}' was {$eventName}";
            });
    }
}
